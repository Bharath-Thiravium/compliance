<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM C - Register of Fines and Unpaid Accumulations</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { size: A4 portrait; margin: 8mm; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9px;
            margin: 0;
            padding: 10px;
            background: #fff;
            color: #000;
        }

        .form-container {
            border: 1px solid #000;
            width: 100%;
            box-sizing: border-box;
        }

        .form-header {
            text-align: center;
            padding: 6px 4px 4px;
            border-bottom: 1px solid #000;
            font-size: 10px;
            line-height: 1.6;
        }
        .form-header .bold { font-weight: bold; }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1px solid #000;
        }
        .info-table td {
            padding: 3px 6px;
            font-size: 9px;
            vertical-align: middle;
            border-bottom: 1px solid #000;
        }
        .info-table td.lbl {
            font-weight: bold;
            white-space: nowrap;
            width: 30%;
            border-right: none;
        }
        .info-table td.val { width: 70%; }

        .register-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            table-layout: fixed;
        }
        .register-table th,
        .register-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
            word-break: break-word;
        }
        .register-table th { font-weight: bold; }
        .register-table td { text-align: left; }
        .col-details { width: 30%; }
        .col-quarter { width: 17.5%; }
        .text-center { text-align: center !important; }
        .sub-row { padding-left: 20px; }

        .footnote-section {
            padding: 6px 8px;
            font-size: 8px;
            font-style: italic;
            border-top: 1px solid #000;
        }
    </style>
</head>
<body>
<div class="form-container">

    <div class="form-header">
        <div class="bold">FORM – C</div>
        <div>(See Rule 29 of the Tamil Nadu Labour Welfare Fund Rules, 1973)</div>
        <div class="bold">Register of Fines and Unpaid Accumulations for the Year {{ $header['period'] ?? '______' }}</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="lbl">Name of the Establishment :</td>
            <td class="val">{{ $header['establishment_name'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="lbl">Address :</td>
            <td class="val">{{ $header['address'] ?? '' }}</td>
        </tr>
    </table>

    <table class="register-table">
        <thead>
            <tr>
                <th class="col-details">Details of Fines and Unpaid Accumulations</th>
                <th class="col-quarter">Quarter ending 31st March</th>
                <th class="col-quarter">Quarter ending 30th June</th>
                <th class="col-quarter">Quarter ending 30th September</th>
                <th class="col-quarter">Quarter ending 31st December</th>
            </tr>
            <tr>
                <th class="col-details text-center">(1)</th>
                <th class="col-quarter text-center">(2)</th>
                <th class="col-quarter text-center">(3)</th>
                <th class="col-quarter text-center">(4)</th>
                <th class="col-quarter text-center">(5)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="col-details">1.Total Realisation under Fines</td>
                <td class="col-quarter">{{ $data['fines_realisation']['march'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['fines_realisation']['june'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['fines_realisation']['september'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['fines_realisation']['december'] ?? '' }}</td>
            </tr>
            <tr>
                <td class="col-details">2.Total amount being unpaid accumulations* of</td>
                <td class="col-quarter"></td><td class="col-quarter"></td>
                <td class="col-quarter"></td><td class="col-quarter"></td>
            </tr>
            <tr>
                <td class="col-details sub-row">(i) Basic wages</td>
                <td class="col-quarter">{{ $data['unpaid_basic']['march'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['unpaid_basic']['june'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['unpaid_basic']['september'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['unpaid_basic']['december'] ?? '' }}</td>
            </tr>
            <tr>
                <td class="col-details sub-row">(ii) Overtime</td>
                <td class="col-quarter">{{ $data['unpaid_overtime']['march'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['unpaid_overtime']['june'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['unpaid_overtime']['september'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['unpaid_overtime']['december'] ?? '' }}</td>
            </tr>
            <tr>
                <td class="col-details sub-row">(iii) Dearness allowances and other allowances</td>
                <td class="col-quarter">{{ $data['unpaid_allowance']['march'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['unpaid_allowance']['june'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['unpaid_allowance']['september'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['unpaid_allowance']['december'] ?? '' }}</td>
            </tr>
            <tr>
                <td class="col-details sub-row">(iv) Bonus</td>
                <td class="col-quarter">{{ $data['unpaid_bonus']['march'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['unpaid_bonus']['june'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['unpaid_bonus']['september'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['unpaid_bonus']['december'] ?? '' }}</td>
            </tr>
            <tr>
                <td class="col-details sub-row">(v) Gratuity</td>
                <td class="col-quarter">{{ $data['unpaid_gratuity']['march'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['unpaid_gratuity']['june'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['unpaid_gratuity']['september'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['unpaid_gratuity']['december'] ?? '' }}</td>
            </tr>
            <tr>
                <td class="col-details sub-row">(vi) Any other item of unpaid accumulation</td>
                <td class="col-quarter">{{ $data['unpaid_other']['march'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['unpaid_other']['june'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['unpaid_other']['september'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['unpaid_other']['december'] ?? '' }}</td>
            </tr>
            <tr>
                <td class="col-details">3.Deductions under Standing Orders</td>
                <td class="col-quarter">{{ $data['standing_order_deduction']['march'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['standing_order_deduction']['june'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['standing_order_deduction']['september'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['standing_order_deduction']['december'] ?? '' }}</td>
            </tr>
            <tr>
                <td class="col-details">4.Deductions under Payment of Wages Act</td>
                <td class="col-quarter">{{ $data['pwa_deduction']['march'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['pwa_deduction']['june'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['pwa_deduction']['september'] ?? '' }}</td>
                <td class="col-quarter">{{ $data['pwa_deduction']['december'] ?? '' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footnote-section">
        * See definition of "Unpaid Accumulations" under Section 2(1) of the Tamil Nadu Labour Welfare Fund Act 1972.
    </div>

</div>
</body>
</html>
