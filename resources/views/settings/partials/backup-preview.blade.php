<div class="alert alert-info">
    <h6 class="fw-semibold mb-2">Ön İzleme Sonucu</h6>
    <p class="mb-2 text-muted small">
        Yedek oluşturulma zamanı: {{ $preview['meta']['generated_at'] ?? 'bilinmiyor' }} ·
        Checksum: <code>{{ $preview['meta']['checksum'] ?? 'yok' }}</code>
    </p>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead class="table-light">
            <tr>
                <th>Tablo</th>
                <th class="text-end">Kayıt Sayısı</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($preview['counts'] as $table => $count)
                <tr>
                    <td class="text-capitalize">{{ $table }}</td>
                    <td class="text-end fw-semibold">{{ $count }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <p class="text-muted small mt-2 mb-0">
        Geri yükleme yapmadan önce bu değerleri doğruladığınızdan emin olun.
        Şifreli yedeklerde şifreyi tekrar girmeniz gerekir.
    </p>
</div>
