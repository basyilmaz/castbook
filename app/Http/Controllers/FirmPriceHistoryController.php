<?php

namespace App\Http\Controllers;

use App\Models\Firm;
use App\Models\FirmPriceHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FirmPriceHistoryController extends Controller
{
    public function store(Request $request, Firm $firm): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
            'valid_from' => ['required', 'date'],
        ]);

        $date = Carbon::parse($data['valid_from'])->startOfDay();

        try {
            DB::transaction(function () use ($firm, $data, $date) {
                $next = $firm->priceHistories()
                    ->where('valid_from', '>=', $date)
                    ->orderBy('valid_from')
                    ->first();

                if ($next && $next->valid_from->equalTo($date)) {
                    throw new \RuntimeException('Bu tarihte zaten bir fiyat kaydı mevcut.');
                }

                $previous = $firm->priceHistories()
                    ->where('valid_from', '<', $date)
                    ->orderByDesc('valid_from')
                    ->first();

                if ($previous) {
                    $previous->update([
                        'valid_to' => $date->copy()->subDay(),
                    ]);
                }

                $validTo = null;
                if ($next) {
                    $validTo = $next->valid_from->copy()->subDay();
                    if ($validTo->lessThan($date)) {
                        $validTo = $date->copy();
                    }
                }

                $firm->priceHistories()->create([
                    'amount' => $data['amount'],
                    'valid_from' => $date,
                    'valid_to' => $validTo,
                    'created_by' => Auth::id(),
                ]);
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['price_history' => $e->getMessage()])->withInput();
        }

        return back()->with('status', 'Yeni fiyat kaydı eklendi.');
    }

    public function destroy(Firm $firm, FirmPriceHistory $priceHistory): RedirectResponse
    {
        abort_if($priceHistory->firm_id !== $firm->id, 404);

        DB::transaction(function () use ($firm, $priceHistory) {
            $previous = $firm->priceHistories()
                ->where('valid_from', '<', $priceHistory->valid_from)
                ->orderByDesc('valid_from')
                ->first();

            $next = $firm->priceHistories()
                ->where('valid_from', '>', $priceHistory->valid_from)
                ->orderBy('valid_from')
                ->first();

            $priceHistory->delete();

            if ($previous) {
                $previous->update([
                    'valid_to' => $next ? $next->valid_from->copy()->subDay() : null,
                ]);
            }
        });

        return back()->with('status', 'Fiyat kaydı silindi.');
    }
}
