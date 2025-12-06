@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Yeni Fatura</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('invoices.store') }}" method="POST">
                    @csrf
                    @include('invoices._form')
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('invoices.index') }}" class="btn btn-light">İptal</a>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
