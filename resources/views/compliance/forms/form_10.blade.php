<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>FORM 10 - Overtime Muster Roll</title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            color: #000;
            line-height: 1.1;
            -webkit-print-color-adjust: exact;
        }

        table { border-spacing: 0; }

        .form-container {
            width: 277mm;
            margin: 12mm auto 0 auto;
            border: 1.2px solid #000;
            padding: 0;
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        /* Header */
        .header-block { padding: 3px 6px 2px; }
        .form-header { text-align: center; line-height: 1.2; }
        .form-header .main-title { font-size: 9.5px; font-weight: bold; }
        .form-header .form-no    { font-size: 10px;  font-weight: bold; }
        .form-header .rule       { font-size: 8px; }
        .form-header .subtitle   { font-size: 8.5px; font-weight: bold; }

        /* Info table */
        .info-table { width: 100%; border-collapse: collapse; margin: 0; }
        .info-table td {
            border-top: 0.5px solid #000;
            border-bottom: 0.5px solid #000;
            border-left: none;
            border-right: none;
            padding: 3px 6px;
            font-size: 8px;
            line-height: 1.1;
            vertical-align: middle;
        }
        .info-table td:first-child { width: 40%; font-weight: bold; white-space: nowrap; padding-left: 6px; }
        .info-table td:last-child  { width: 60%; padding-left: 2px; }
        .info-table tr + tr td    { border-top: none; }

        /* Data table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin: 0;
            page-break-inside: avoid;
        }
        .data-table th,
        .data-table td {
            border: 0.5px solid #000;
            font-size: 7.5px;
            vertical-align: middle;
            line-height: 1.1;
            word-wrap: break-word;
        }
        .data-table th {
            font-weight: bold;
            text-align: center;
            padding: 3px 2px;
            line-height: 1.15;
        }
        .data-table td { padding: 2px 3px; }
        .data-table tbody tr { page-break-inside: avoid; }

        .tc { text-align: center; }
        .tr { text-align: right; }

        .totals-row td { font-weight: bold; background-color: #f5f5f5; }

        .data-table tbody tr:first-child td { height: 14px; }

        .nil-row { text-align: center; font-weight: bold; font-size: 8px; padding: 4px 0; letter-spacing: 0.2px; }

        .signature-cell {
            height: 85px;
            padding: 6px 10px;
            position: relative;
            font-size: 8px;
            border-top: 0.5px solid #000;
            border-left: none !important;
            border-right: none !important;
            border-bottom: none !important;
        }
        .sign-wrapper {
            position: absolute;
            right: 10px;
            bottom: 6px;
            text-align: right;
        }
        .company-text { font-size: 8px; margin-bottom: 4px; }
        .seal-sign-block { display: block; }
        .seal  { height: 32px; display: block; margin-left: auto; }
        .sign-img { height: 18px; display: block; margin-left: auto; margin-top: 2px; }
        .sign-label { font-size: 8px; margin-top: 2px; }

    </style>
</head>
<body>
<div class="form-container">

    <div class="header-block">
        <div class="form-header">
            <div class="main-title">The Tamil Nadu Factories Rules</div>
            <div class="form-no">FORM 10</div>
            <div class="rule">(Prescribed under Rule 78)</div>
            <div class="subtitle">Overtime muster roll for exempted workers</div>
        </div>
    </div>
    <div style="border-top:0.5px solid #000;"></div>
    <table class="info-table">
        <tr>
            <td>Name of the Company</td>
            <td>: {{ ($header['tenant'] ?? [])['name'] ?? '' }}</td>
        </tr>
        <tr>
            <td>Name of the Contractor</td>
            <td>: {{ $header['contractor_name'] ?? '' }}</td>
        </tr>
        <tr>
            <td>Total number of workers employed</td>
            <td>: {{ $header['total_workers'] ?? count($rows ?? []) }}</td>
        </tr>
        <tr>
            <td>Work location</td>
            <td>: {{ ($header['branch'] ?? [])['address'] ?? '' }}</td>
        </tr>
        <tr>
            <td>Name of the Principal Employer</td>
            <td>: {{ $header['principal_employer'] ?? '' }}</td>
        </tr>
        <tr>
            <td>Month</td>
            <td>: {{ $header['period'] ?? '' }}</td>
        </tr>
    </table>

    @php
        $hasOvertime = $hasOvertime ?? (isset($rows) && count($rows) > 0);
        \Illuminate\Support\Facades\Log::info('FORM_10 render', [
            'company'     => ($header['tenant'] ?? [])['name'] ?? '',
            'period'      => $header['period'] ?? '',
            'rows'        => count($rows ?? []),
            'hasOvertime' => $hasOvertime,
        ]);
    @endphp

    <table class="data-table">
        <colgroup>
            <col style="width:3.3%">
            <col style="width:13.1%">
            <col style="width:9.5%">
            <col style="width:8.8%">
            <col style="width:6.6%">
            <col style="width:6.6%">
            <col style="width:4.7%">
            <col style="width:5.5%">
            <col style="width:5.5%">
            <col style="width:5.8%">
            <col style="width:5.8%">
            <col style="width:10.2%">
            <col style="width:5.8%">
            <col style="width:8.8%">
        </colgroup>
        <thead>
            <tr>
                <th>No. in register</th>
                <th>Name</th>
                <th>Department</th>
                <th>Dates on which overtime has been worked</th>
                <th>Extent of overtime on each occasion</th>
                <th>Total overtime worked or production in case of piece-workers</th>
                <th>Normal hours</th>
                <th>Normal rate of pay</th>
                <th>Overtime rate of pay</th>
                <th>Normal earnings</th>
                <th>Overtime earnings</th>
                <th>Cash equivalent of advantages accruing through the concessional sale of food-grains and other articles</th>
                <th>Total earnings</th>
                <th>Dates on which overtime payments made</th>
            </tr>
        </thead>
        <tbody>
            @if(!$hasOvertime)
            <tr>
                <td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td>
                <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
            </tr>
            <tr>
                <td colspan="14" class="nil-row">NO BODY IN THE ORGANIZATION HAS WORKED OVER TIME FOR THE MONTH OF {{ strtoupper($header['period'] ?? '') }}</td>
            </tr>
            @else
            @foreach($rows as $index => $row)
            <tr>
                <td class="tc">{{ $index + 1 }}</td>
                <td>{{ $row['employee_name'] ?? '' }}</td>
                <td>{{ $row['designation'] ?? '' }}</td>
                <td class="tc"></td>
                <td class="tr">{{ number_format($row['overtime_hours'] ?? 0, 2) }}</td>
                <td class="tr">{{ ($row['is_piece_worker'] ?? false) ? number_format($row['overtime_hours'] ?? 0, 2) : '' }}</td>
                <td class="tc">8</td>
                <td class="tr">{{ number_format($row['normal_rate'] ?? 0, 2) }}</td>
                <td class="tr">{{ number_format($row['overtime_rate'] ?? 0, 2) }}</td>
                <td class="tr">{{ number_format($row['normal_earnings'] ?? 0, 2) }}</td>
                <td class="tr">{{ number_format($row['overtime_wages'] ?? 0, 2) }}</td>
                <td class="tr">{{ number_format($row['food_grain_benefit'] ?? 0, 2) }}</td>
                <td class="tr">{{ number_format(($row['normal_earnings'] ?? 0) + ($row['overtime_wages'] ?? 0) + ($row['food_grain_benefit'] ?? 0), 2) }}</td>
                <td class="tc"></td>
            </tr>
            @endforeach
            @if($hasOvertime && !empty($totals))
            <tr class="totals-row">
                <td colspan="4" class="tr">Total</td>
                <td class="tr">{{ number_format($totals['overtime_hours'] ?? 0, 2) }}</td>
                <td class="tr">{{ number_format($totals['piece_worker_overtime'] ?? 0, 2) }}</td>
                <td class="tc">&mdash;</td>
                <td class="tr">{{ number_format($totals['normal_rate'] ?? 0, 2) }}</td>
                <td class="tr">{{ number_format($totals['overtime_rate'] ?? 0, 2) }}</td>
                <td class="tr">{{ number_format($totals['normal_earnings'] ?? 0, 2) }}</td>
                <td class="tr">{{ number_format($totals['overtime_wages'] ?? 0, 2) }}</td>
                <td class="tr">{{ number_format($totals['food_grain_benefit'] ?? 0, 2) }}</td>
                <td class="tr">{{ number_format(($totals['normal_earnings'] ?? 0) + ($totals['overtime_wages'] ?? 0) + ($totals['food_grain_benefit'] ?? 0), 2) }}</td>
                <td></td>
            </tr>
            @endif
            @endif
            <tr class="signature-row">
                <td colspan="14" class="signature-cell">
                    <div class="sign-wrapper">
                        <div class="company-text">For {{ ($header['tenant'] ?? [])['name'] ?? 'Company Name' }}</div>
                        <div class="seal-sign-block">
                            @if(!empty($batch_signature))
                                @if(!empty($batch_signature['seal_path']))
                                    <img src="{{ storage_path('app/' . $batch_signature['seal_path']) }}" class="seal">
                                @endif
                                <img src="{{ storage_path('app/' . $batch_signature['signature_path']) }}" class="sign-img">
                                <div class="sign-label">{{ $batch_signature['signatory_name'] ?? 'Authorized Signatory' }}<br>{{ $batch_signature['signatory_designation'] ?? '' }}</div>
                            @elseif(!empty($company_signature))
                                <img src="{{ storage_path('app/' . $company_signature) }}" class="sign-img">
                                <div class="sign-label">Authorized Signatory</div>
                            @else
                                <div style="height:50px;"></div>
                                <div class="sign-label">Authorized Signatory</div>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

</div>
</body>
</html>
