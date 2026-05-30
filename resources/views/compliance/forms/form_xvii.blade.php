<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM XVII - Register of Wages</title>
    <style>
        @page { margin: 3mm; size: A4 landscape; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 6px;
        }
        .form-container {
            border: 1px solid black;
            padding: 2px;
            width: 100%;
        }
        .form-header {
            text-align: center;
            margin-bottom: 2px;
        }
        .header-title {
            font-weight: bold;
            font-size: 8px;
        }
        .header-subtitle {
            font-size: 6px;
        }
        .header-section {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
            font-size: 6px;
        }
        .header-section td {
            border: 1px solid black;
            padding: 1px;
            vertical-align: top;
            width: 50%;
        }
        .header-row {
            margin-bottom: 1px;
            line-height: 1.1;
        }
        .header-label { font-weight: bold; }
        .register-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            font-size: 5px;
            margin-bottom: 2px;
            table-layout: fixed;
        }
        .register-table th,
        .register-table td {
            border: 1px solid black;
            padding: 0px 1px;
            text-align: center;
            vertical-align: middle;
            overflow: hidden;
            word-wrap: break-word;
        }
        .register-table th {
            font-weight: bold;
            line-height: 1.0;
            height: 11px;
        }
        .register-table td { height: 9px; }
        .text-right { text-align: right; }
        .text-left  { text-align: left; }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            font-size: 6px;
        }
        .signature-table td { padding: 0; }
        .sign-left  { text-align: left;  width: 50%; }
        .sign-right { text-align: right; width: 50%; }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <div class="header-title">FORM XVII</div>
            <div class="header-title">REGISTER OF WAGES</div>
            <div class="header-subtitle">[Rule 78(1)(a)(i) of Contract Labour (Regulation &amp; Abolition) Central Rules]</div>
        </div>

        <table class="header-section">
            <tr>
                <td>
                    <div class="header-row">
                        <span class="header-label">Name and address of Contractor:</span><br>
                        {{ $header['contractor_name'] ?? '' }}
                    </div>
                    <div class="header-row">
                        <span class="header-label">Nature and location of work:</span><br>
                        {{ $header['work_nature'] ?? '' }}{{ !empty($header['work_location']) ? ' - ' . $header['work_location'] : '' }}
                    </div>
                    <div class="header-row">
                        <span class="header-label">Wage period:</span>
                        {{ $header['wage_period'] ?? '' }}
                    </div>
                </td>
                <td>
                    <div class="header-row">
                        <span class="header-label">Name and address of Establishment in/under which contract is carried on:</span><br>
                        {{ $header['establishment_name'] ?? '' }}
                    </div>
                    <div class="header-row">
                        <span class="header-label">Name and address of Principal Employer:</span><br>
                        {{ $header['principal_employer'] ?? '' }}
                    </div>
                </td>
            </tr>
        </table>

        <table class="register-table">
            <colgroup>
                <col style="width:2.5%">  {{-- S.No --}}
                <col style="width:7%">    {{-- Name --}}
                <col style="width:5%">    {{-- Serial No --}}
                <col style="width:6%">    {{-- Designation --}}
                <col style="width:4%">    {{-- Days --}}
                <col style="width:4%">    {{-- Unit --}}
                <col style="width:5%">    {{-- Rate --}}
                <col style="width:5%">    {{-- Basic --}}
                <col style="width:5%">    {{-- DA --}}
                <col style="width:5%">    {{-- OT --}}
                <col style="width:5%">    {{-- Other --}}
                <col style="width:5%">    {{-- Total --}}
                <col style="width:4%">    {{-- ESI --}}
                <col style="width:4%">    {{-- PF --}}
                <col style="width:4%">    {{-- PT --}}
                <col style="width:4%">    {{-- Deduct Total --}}
                <col style="width:5%">    {{-- Net --}}
                <col style="width:5%">    {{-- Signature --}}
                <col style="width:5%">    {{-- Initial --}}
            </colgroup>
            <thead>
                <tr>
                    <th rowspan="2">S.No</th>
                    <th rowspan="2">Name of Workman</th>
                    <th rowspan="2">Serial No in Register of Workmen</th>
                    <th rowspan="2">Designation / Nature of Work</th>
                    <th rowspan="2">No. of Days Worked</th>
                    <th rowspan="2">Unit of Work Done</th>
                    <th rowspan="2">Daily Rate of Wages / Piece Rate</th>
                    <th rowspan="2">Basic Wages</th>
                    <th rowspan="2">Dearness Allowance</th>
                    <th rowspan="2">Overtime</th>
                    <th rowspan="2">Other Cash Payments</th>
                    <th rowspan="2">Total</th>
                    <th colspan="4">Deductions (if any)</th>
                    <th rowspan="2">Net Amount Paid</th>
                    <th rowspan="2">Signature / Thumb Impression of Workman</th>
                    <th rowspan="2">Initial of Contractor or his representative</th>
                </tr>
                <tr>
                    <th>ESI</th>
                    <th>PF</th>
                    <th>PT</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows ?? [] as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $row['name'] ?? '' }}</td>
                    <td>{{ $row['employee_code'] ?? '' }}</td>
                    <td class="text-left">{{ $row['designation'] ?? '' }}</td>
                    <td>{{ $row['days_worked'] ?? '' }}</td>
                    <td>{{ $row['unit_work'] ?? '' }}</td>
                    <td class="text-right">{{ ($row['daily_rate'] ?? 0) ? number_format($row['daily_rate'], 2) : '' }}</td>
                    <td class="text-right">{{ ($row['basic_wages'] ?? 0) ? number_format($row['basic_wages'], 2) : '' }}</td>
                    <td class="text-right">{{ ($row['da'] ?? 0) ? number_format($row['da'], 2) : '' }}</td>
                    <td class="text-right">{{ ($row['overtime'] ?? 0) ? number_format($row['overtime'], 2) : '' }}</td>
                    <td class="text-right">{{ ($row['other_cash'] ?? 0) ? number_format($row['other_cash'], 2) : '' }}</td>
                    <td class="text-right">{{ ($row['gross_salary'] ?? 0) ? number_format($row['gross_salary'], 2) : '' }}</td>
                    <td class="text-right">{{ ($row['esi'] ?? 0) ? number_format($row['esi'], 2) : '' }}</td>
                    <td class="text-right">{{ ($row['pf'] ?? 0) ? number_format($row['pf'], 2) : '' }}</td>
                    <td class="text-right">{{ ($row['pt'] ?? 0) ? number_format($row['pt'], 2) : '' }}</td>
                    <td class="text-right">{{ ($row['total_deductions'] ?? 0) ? number_format($row['total_deductions'], 2) : '' }}</td>
                    <td class="text-right">{{ ($row['net_amount'] ?? 0) ? number_format($row['net_amount'], 2) : '' }}</td>
                    <td></td>
                    <td></td>
                </tr>
                @empty
                    <tr>
                        <td colspan="19" style="text-align:center;">No records found</td>
                    </tr>
                @endforelse
                @if(!empty($rows ?? []))
                <tr style="font-weight:bold;">
                    <td colspan="4" style="text-align:right;">Total</td>
                    <td class="text-right">{{ $totals['days_worked'] ?? '' }}</td>
                    <td></td>
                    <td></td>
                    <td class="text-right">{{ isset($totals['basic_wages'])      ? number_format($totals['basic_wages'], 2)      : '' }}</td>
                    <td class="text-right">{{ isset($totals['da'])               ? number_format($totals['da'], 2)               : '' }}</td>
                    <td class="text-right">{{ isset($totals['overtime'])         ? number_format($totals['overtime'], 2)         : '' }}</td>
                    <td class="text-right">{{ isset($totals['other_cash'])       ? number_format($totals['other_cash'], 2)       : '' }}</td>
                    <td class="text-right">{{ isset($totals['gross_salary'])     ? number_format($totals['gross_salary'], 2)     : '' }}</td>
                    <td class="text-right">{{ isset($totals['esi'])              ? number_format($totals['esi'], 2)              : '' }}</td>
                    <td class="text-right">{{ isset($totals['pf'])               ? number_format($totals['pf'], 2)               : '' }}</td>
                    <td class="text-right">{{ isset($totals['pt'])               ? number_format($totals['pt'], 2)               : '' }}</td>
                    <td class="text-right">{{ isset($totals['total_deductions']) ? number_format($totals['total_deductions'], 2) : '' }}</td>
                    <td class="text-right">{{ isset($totals['net_amount'])       ? number_format($totals['net_amount'], 2)       : '' }}</td>
                    <td></td>
                    <td></td>
                </tr>
                @endif
            </tbody>
        </table>

        <table class="signature-table">
            <tr>
                <td class="sign-left">Signature of the Site Engineer</td>
                <td class="sign-right">Signature of the Contractor with Seal</td>
            </tr>
        </table>
    </div>
</body>
</html>
