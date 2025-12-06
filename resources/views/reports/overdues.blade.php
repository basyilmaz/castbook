@extends('layouts.app')

@php
    use App\Support\Format;
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Geciken Ödemeler</h4>
        <small class="text-muted">Vadesi geçmiş ve henüz kapanmamış faturalar.</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.overdues.export') }}" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i>CSV
        </a>
        <a href="{{ route('reports.overdues.pdf') }}" class="btn btn-outline-danger">
            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
        </a>
    </div>
</div>

@include('reports._tabs')

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fatura</th>
                        <th>Firma</th>
                        <th>Fatura Tarihi</th>
                        <th>Vade Tarihi</th>
                        <th class="text-center">Gecikme (Gün)</th>
                        <th class="text-end">Tutar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td>#{{ $invoice->id }}</td>
                            <td>
                                <a href="{{ route('firms.show', $invoice->firm) }}" class="text-decoration-none">
                                    {{ $invoice->firm->name }}
                                </a>
                            </td>
                            <td>{{ $invoice->date?->format('d.m.Y') }}</td>
                            <td>{{ $invoice->due_date?->format('d.m.Y') ?? '-' }}</td>
                            <td class="text-center text-danger fw-semibold">{{ $invoice->days_overdue }}</td>
                            <td class="text-end text-danger">{{ Format::money($invoice->amount) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Geciken fatura bulunmadı.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
