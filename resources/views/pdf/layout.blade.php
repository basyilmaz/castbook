<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title ?? 'Rapor' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #2563eb;
        }
        
        .header h1 {
            font-size: 18pt;
            color: #1e40af;
            margin-bottom: 5px;
        }
        
        .header .subtitle {
            font-size: 10pt;
            color: #666;
        }
        
        .header .date {
            font-size: 9pt;
            color: #888;
            margin-top: 5px;
        }
        
        .summary-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .summary-box h3 {
            font-size: 11pt;
            color: #1e40af;
            margin-bottom: 10px;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
        }
        
        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 10px;
        }
        
        .summary-item .label {
            font-size: 8pt;
            color: #666;
            text-transform: uppercase;
        }
        
        .summary-item .value {
            font-size: 14pt;
            font-weight: bold;
            color: #1e40af;
        }
        
        .summary-item .value.success { color: #22c55e; }
        .summary-item .value.danger { color: #ef4444; }
        .summary-item .value.warning { color: #f59e0b; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table th {
            background: #1e40af;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-size: 9pt;
            font-weight: 600;
        }
        
        table th.text-right,
        table td.text-right {
            text-align: right;
        }
        
        table th.text-center,
        table td.text-center {
            text-align: center;
        }
        
        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9pt;
        }
        
        table tr:nth-child(even) {
            background: #f8fafc;
        }
        
        table tr:hover {
            background: #f1f5f9;
        }
        
        table tfoot td {
            font-weight: bold;
            background: #f1f5f9;
            border-top: 2px solid #1e40af;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 8pt;
            font-weight: 600;
        }
        
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-secondary { background: #f1f5f9; color: #475569; }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #888;
            padding: 10px;
            border-top: 1px solid #e2e8f0;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .text-muted { color: #666; }
        .text-success { color: #22c55e; }
        .text-danger { color: #ef4444; }
        .text-warning { color: #f59e0b; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName ?? 'CastBook' }}</h1>
        <div class="subtitle">{{ $title ?? 'Rapor' }}</div>
        <div class="date">Oluşturulma: {{ now()->format('d.m.Y H:i') }}</div>
    </div>
    
    @yield('content')
    
    <div class="footer">
        {{ $companyName ?? 'CastBook' }} - Muhasebe Takip Sistemi | Sayfa {PAGE_NUM} / {PAGE_COUNT}
    </div>
</body>
</html>
