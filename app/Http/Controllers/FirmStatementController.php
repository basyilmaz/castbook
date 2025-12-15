<?php

namespace App\Http\Controllers;

use App\Mail\FirmStatementMail;
use App\Models\Firm;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class FirmStatementController extends Controller
{
    public function generate(Request $request, Firm $firm)
    {
        $data = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'send_to' => ['nullable', 'email'],
            'action' => ['required', 'in:download,email,print'],
        ]);

        $start = Carbon::parse($data['start_date'])->startOfDay();
        $end = Carbon::parse($data['end_date'])->endOfDay();

        $initialBalance = $firm->transactions()
            ->where('date', '<', $start)
            ->selectRaw("SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as debit_sum, SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as credit_sum")
            ->first();

        $initial = ($initialBalance->debit_sum ?? 0) - ($initialBalance->credit_sum ?? 0);

        $transactions = $firm->transactions()
            ->whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();

        $runningBalance = $initial;
        $totalDebit = 0;
        $totalCredit = 0;

        $transactions->transform(function ($transaction) use (&$runningBalance, &$totalDebit, &$totalCredit) {
            if ($transaction->type === 'debit') {
                $runningBalance += $transaction->amount;
                $totalDebit += $transaction->amount;
            } else {
                $runningBalance -= $transaction->amount;
                $totalCredit += $transaction->amount;
            }
            $transaction->running_balance = $runningBalance;

            return $transaction;
        });

        $summary = [
            'start' => $start,
            'end' => $end,
            'initial_balance' => $initial,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'closing_balance' => $runningBalance,
        ];

        $settings = Setting::query()->whereIn('key', [
            'company_name',
            'company_address',
            'company_email',
            'company_phone',
            'company_logo_path',
        ])->pluck('value', 'key');

        $viewData = [
            'firm' => $firm,
            'transactions' => $transactions,
            'summary' => $summary,
            'settings' => $settings,
        ];

        $fileName = 'hesap-ekstresi-' . $firm->id . '-' . $start->format('Ymd') . '-' . $end->format('Ymd') . '.pdf';

        // PDF İndir
        if ($data['action'] === 'download') {
            try {
                $pdf = Pdf::loadView('firms.statement_pdf', $viewData)->setPaper('a4');
                $pdfContent = $pdf->output();
                
                return response($pdfContent)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Length', strlen($pdfContent));
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        // Yazdır - HTML olarak tarayıcıda aç + otomatik yazdır
        if ($data['action'] === 'print') {
            return view('firms.statement_print', $viewData);
        }

        // E-posta gönder
        $recipient = $data['send_to'] ?? $firm->contact_email;

        if (! $recipient) {
            return back()->withErrors(['send_to' => 'Gönderim için e-posta adresi gerekli.'])->withInput();
        }

        $pdf = Pdf::loadView('firms.statement_pdf', $viewData)->setPaper('a4');
        Mail::to($recipient)->send(new FirmStatementMail($firm, $summary, $transactions, $pdf->output(), $fileName, $settings->toArray()));

        return back()->with('status', 'Hesap ekstresi e-posta ile gönderildi.');
    }
}