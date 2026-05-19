<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM C - Bonus Register</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { size: A3 landscape; margin: 8mm; }

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

        .details-section {
            padding: 6px 8px;
            border-bottom: 1px solid #000;
            font-size: 9px;
        }
        .detail-row {
            display: flex;
            align-items: center;
            margin-bottom: 4px;
        }
        .detail-row:last-child { margin-bottom: 0; }
        .detail-label {
            font-weight: bold;
            width: 180px;
            flex-shrink: 0;
        }
        .detail-line {
            flex: 1;
            border-bottom: 1px solid #000;
            height: 10px;
        }

        .register-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6px;
            table-layout: fixed;
            overflow: hidden;
        }
        .register-table th,
        .register-table td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
            vertical-align: middle;
            word-break: break-all;
            overflow: hidden;
            line-height: 1.2;
        }
        .register-table th { font-weight: bold; }
        .register-table td { text-align: left; }
        .text-center { text-align: center !important; }
        .td-name {
            white-space: nowrap;
            overflow: hidden;
            font-size: 5px;
        }
    </style>
</head>
<body>
<div class="form-container">

    <div class="form-header">
        <div class="bold">FORM C</div>
        <div>See Rule 4(c)</div>
        <div class="bold">BONUS PAID TO EMPLOYEES FOR THE ACCOUNTING YEAR ENDING ON __________</div>
    </div>

    <div class="details-section">
        <div class="detail-row">
            <div class="detail-label">Name of the establishment</div>
            <div class="detail-line"></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">No. of working days in the year</div>
            <div class="detail-line"></div>
        </div>
    </div>

    <table class="register-table">
        <colgroup>
            <col style="width:3%">
            <col style="width:20%">
            <col style="width:5%">
            <col style="width:6%">
            <col style="width:5%">
            <col style="width:3%">
            <col style="width:6%">
            <col style="width:6%">
            <col style="width:5%">
            <col style="width:5%">
            <col style="width:4%">
            <col style="width:6%">
            <col style="width:5%">
            <col style="width:6%">
            <col style="width:4%">
            <col style="width:4%">
            <col style="width:4%">
        </colgroup>
        <thead>
            <tr>
                <th>Sr. No.</th>
                <th>Name of the employee</th>
                <th>Father's Name</th>
                <th>Whether he has completed 15 years of age at the beginning of the accounting year</th>
                <th>Designation</th>
                <th>No. of days worked in the year</th>
                <th>Total salary or wage in respect of the accounting year</th>
                <th>Amount of bonus payable under section 10 or section 11 as the case may be</th>
                <th>Puja bonus or other customary bonus paid during the accounting year</th>
                <th>Interim bonus or bonus paid in advance</th>
                <th>Amount of Income-tax deducted</th>
                <th>Deduction on account of financial loss, if any, caused by misconduct of the employees</th>
                <th>Total sum deducted under Columns 9, 10, 10A and 11</th>
                <th>Net amount payable (Column 8 minus Column 12)</th>
                <th>Amount actually paid</th>
                <th>Date on which paid</th>
                <th>Signature / Thumb impression of the employees</th>
            </tr>
            <tr>
                <th>(1)</th><th>(2)</th><th>(3)</th><th>(4)</th><th>(5)</th><th>(6)</th>
                <th>(7)</th><th>(8)</th><th>(9)</th><th>(10)</th><th>(10A)</th><th>(11)</th>
                <th>(12)</th><th>(13)</th><th>(14)</th><th>(15)</th><th>(16)</th>
            </tr>
        </thead>
        <tbody>
            @if($is_nil)
            <tr>
                <td colspan="17" class="text-center">Nil</td>
            </tr>
            @else
            @foreach($rows as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td style="white-space:nowrap; font-size:5px;">{{ $row['employee_name'] }}</td>
                <td>{{ $row['father_name'] }}</td>
                <td class="text-center">{{ $row['age_eligible'] }}</td>
                <td>{{ $row['designation'] }}</td>
                <td class="text-center">{{ $row['days_worked'] }}</td>
                <td class="text-center">{{ number_format($row['total_wages'], 2) }}</td>
                <td class="text-center">{{ number_format($row['bonus_payable'], 2) }}</td>
                <td class="text-center">{{ number_format($row['puja_bonus'], 2) }}</td>
                <td class="text-center">{{ number_format($row['interim_bonus'], 2) }}</td>
                <td class="text-center">{{ number_format($row['tax_deducted'], 2) }}</td>
                <td class="text-center">{{ number_format($row['loss_deduction'], 2) }}</td>
                <td class="text-center">{{ number_format($row['total_deduction'], 2) }}</td>
                <td class="text-center">{{ number_format($row['net_payable'], 2) }}</td>
                <td class="text-center">{{ number_format($row['amount_paid'], 2) }}</td>
                <td class="text-center">{{ $row['payment_date'] }}</td>
                <td></td>
            </tr>
            @endforeach
            @endif
        </tbody>
    </table>

</div>
</body>
</html>
