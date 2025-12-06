{{-- Aylık Gelir Grafiği --}}
<div class="col-xl-6 col-12">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="bi bi-graph-up me-2 text-primary"></i>Aylık Gelir Trendi
            </h6>
            <small class="text-muted">Son 6 Ay</small>
        </div>
        <div class="card-body">
            <canvas id="revenueChart" height="200"></canvas>
        </div>
    </div>
</div>

{{-- Fatura/Tahsilat Karşılaştırması --}}
<div class="col-xl-6 col-12">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="bi bi-bar-chart me-2 text-success"></i>Fatura vs Tahsilat
            </h6>
            <small class="text-muted">Son 6 Ay</small>
        </div>
        <div class="card-body">
            <canvas id="comparisonChart" height="200"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart verileri (PHP'den)
    const chartData = @json($chartData ?? []);
    
    if (!chartData.labels || chartData.labels.length === 0) {
        console.log('Chart verisi bulunamadı');
        return;
    }

    // Renk paleti
    const colors = {
        primary: 'rgba(37, 99, 235, 1)',
        primaryLight: 'rgba(37, 99, 235, 0.1)',
        success: 'rgba(34, 197, 94, 1)',
        successLight: 'rgba(34, 197, 94, 0.1)',
        warning: 'rgba(234, 179, 8, 1)',
        warningLight: 'rgba(234, 179, 8, 0.1)',
    };

    // Para formatı
    function formatMoney(value) {
        return new Intl.NumberFormat('tr-TR', {
            style: 'currency',
            currency: 'TRY',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value);
    }

    // 1. Gelir Trendi Grafiği (Line Chart)
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Tahsilat',
                    data: chartData.paymentAmounts,
                    borderColor: colors.success,
                    backgroundColor: colors.successLight,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: colors.success,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return 'Tahsilat: ' + formatMoney(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            callback: function(value) {
                                return formatMoney(value);
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }

    // 2. Fatura vs Tahsilat Karşılaştırma Grafiği (Bar Chart)
    const comparisonCtx = document.getElementById('comparisonChart');
    if (comparisonCtx) {
        new Chart(comparisonCtx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Fatura',
                        data: chartData.invoiceAmounts,
                        backgroundColor: colors.primary,
                        borderColor: colors.primary,
                        borderWidth: 0,
                        borderRadius: 4,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Tahsilat',
                        data: chartData.paymentAmounts,
                        backgroundColor: colors.success,
                        borderColor: colors.success,
                        borderWidth: 0,
                        borderRadius: 4,
                        barPercentage: 0.7,
                        categoryPercentage: 0.8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            boxWidth: 12,
                            boxHeight: 12,
                            borderRadius: 3,
                            useBorderRadius: true,
                            padding: 15,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatMoney(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            callback: function(value) {
                                return formatMoney(value);
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
