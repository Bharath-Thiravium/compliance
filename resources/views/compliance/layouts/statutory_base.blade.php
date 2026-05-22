<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('form_title')</title>
    <style>
        @page {
            margin: 15mm 10mm;
            @bottom-right {
                content: "Page " counter(page) " of " counter(pages);
            }
        }
        
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        
        .statutory-header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .form-title {
            font-size: 12pt;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
        }
        
        .act-reference {
            font-size: 8pt;
            margin: 3px 0;
            font-style: italic;
        }
        
        .rule-reference {
            font-size: 8pt;
            margin: 3px 0;
        }
        
        .establishment-info {
            margin: 10px 0 15px 0;
            font-size: 8pt;
            line-height: 1.5;
        }
        
        .establishment-info p {
            margin: 2px 0;
        }
        
        .establishment-info strong {
            display: inline-block;
            width: 150px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 8pt;
        }
        
        thead {
            display: table-header-group;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: left;
            vertical-align: top;
        }
        
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        
        .totals-row {
            font-weight: bold;
            background-color: #e8e8e8;
        }
        
        .nil-declaration {
            text-align: center;
            padding: 30px;
            border: 1px solid #000;
            margin: 20px 0;
            font-weight: bold;
            font-size: 10pt;
        }
        
        .signature-block {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        
        .signature-grid {
            display: table;
            width: 100%;
            margin-top: 20px;
        }
        
        .signature-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .signature-right {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin-top: 50px;
            display: inline-block;
        }
        
        .signature-label {
            font-size: 8pt;
            margin-top: 5px;
        }
        
        .declaration-text {
            margin: 20px 0;
            font-size: 8pt;
            line-height: 1.6;
            text-align: justify;
        }
    </style>
    @yield('additional_styles')
</head>
<body>
    <div class="statutory-header">
        <div class="form-title">@yield('form_title')</div>
        <div class="act-reference">@yield('act_reference')</div>
        <div class="rule-reference">@yield('rule_reference')</div>
    </div>
    
    <div class="establishment-info">
        <p><strong>Name of Establishment:</strong> {{ is_array($header['tenant'] ?? null) ? $header['tenant']['name'] : $header['tenant'] }}</p>
        @if(isset($header['branch']))
        <p><strong>Branch/Unit:</strong> {{ $header['branch']['name'] ?? 'N/A' }}</p>
        <p><strong>Address:</strong> {{ $header['branch']['address'] ?? 'N/A' }}</p>
        <p><strong>Factory License No:</strong> {{ $header['branch']['license'] ?? 'N/A' }}</p>
        @endif
        <p><strong>Period:</strong> {{ $header['period'] ?? 'N/A' }}</p>
    </div>
    
    @yield('content')
    
    @if(!$is_nil)
    <div class="signature-block">
        @yield('declaration')
        
        <div class="signature-grid">
            <div class="signature-left">
                <div class="signature-label">Date: _______________</div>
            </div>
            <div class="signature-right">
                @if(isset($batch_signature) && $batch_signature)
                    <img src="{{ storage_path('app/' . $batch_signature['signature_path']) }}" style="height: 60px; margin-bottom: 10px;">
                    <div class="signature-label">
                        <strong>{{ $batch_signature['signatory_name'] }}</strong><br>
                        {{ $batch_signature['signatory_designation'] }}
                    </div>
                @elseif(isset($company_signature) && $company_signature)
                    <img src="{{ storage_path('app/' . $company_signature) }}" style="height: 60px; margin-bottom: 10px;">
                    <div class="signature-label">
                        <strong>@yield('signatory_title', 'Manager / Occupier')</strong><br>
                        Name: _______________<br>
                        Signature & Seal
                    </div>
                @else
                    <div class="signature-line"></div>
                    <div class="signature-label">
                        <strong>@yield('signatory_title', 'Manager / Occupier')</strong><br>
                        Name: _______________<br>
                        Signature & Seal
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if(isset($batch_id) && isset($form_code))
    <script>
        (function() {
            const batchId = {{ $batch_id }};
            const formCode = '{{ $form_code }}';
            
            setInterval(() => {
                fetch(`/compliance/batch/${batchId}/form/${formCode}/refresh`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.rows) updateTableRows(data.rows);
                        if (data.totals) updateTotals(data.totals);
                    })
                    .catch(err => console.error('Refresh failed:', err));
            }, 5000);

            function updateTableRows(rows) {
                const tbody = document.querySelector('table tbody');
                if (!tbody) return;
                
                const existingRows = tbody.querySelectorAll('tr:not(.totals-row)');
                rows.forEach((row, index) => {
                    if (existingRows[index]) {
                        updateRow(existingRows[index], row, index);
                    }
                });
            }

            function updateRow(tr, data, index) {
                const cells = tr.querySelectorAll('td');
                if (cells.length === 0) return;

                const daysWorked = data.total_days_worked || 0;
                const dailyRate = daysWorked > 0 ? (data.basic_earned || 0) / daysWorked : 0;
                const others = data.hra_earned || 0;
                const total = (data.basic_earned || 0) + (data.da_earned || 0) + (data.overtime_wages || 0) + others;
                
                cells[0].textContent = index + 1;
                cells[1].textContent = data.employee_name || '-';
                cells[2].textContent = data.designation || '-';
                cells[3].textContent = daysWorked;
                cells[4].textContent = dailyRate.toFixed(2);
                cells[5].textContent = (data.basic_earned || 0).toFixed(2);
                cells[6].textContent = (data.da_earned || 0).toFixed(2);
                cells[7].textContent = (data.overtime_wages || 0).toFixed(2);
                cells[8].textContent = others.toFixed(2);
                cells[9].textContent = '0.00';
                cells[10].textContent = total.toFixed(2);
                cells[12].textContent = (data.net_salary || 0).toFixed(2);
            }

            function updateTotals(totals) {
                const totalsRow = document.querySelector('.totals-row');
                if (!totalsRow) return;
                
                const cells = totalsRow.querySelectorAll('td');
                if (cells.length < 9) return;
                
                cells[1].textContent = (totals.basic_earned || 0).toFixed(2);
                cells[2].textContent = (totals.da_earned || 0).toFixed(2);
                cells[3].textContent = (totals.overtime_wages || 0).toFixed(2);
                cells[4].textContent = (totals.hra_earned || 0).toFixed(2);
                cells[5].textContent = '0.00';
                cells[6].textContent = (totals.gross_salary || 0).toFixed(2);
                cells[7].textContent = (totals.total_deductions || 0).toFixed(2);
                cells[8].textContent = (totals.net_salary || 0).toFixed(2);
            }
        })();
    </script>
    @endif
</body>
</html>
