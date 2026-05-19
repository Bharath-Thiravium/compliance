@extends('compliance.layouts.preview')

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM XIX - Wage Slip</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            padding: 20px;
        }
        .form-container {
            border: 2px solid black;
            padding: 15px;
            margin: 0 auto;
            width: 95%;
            max-width: 900px;
        }
        .form-header {
            text-align: center;
            margin-bottom: 15px;
            font-size: 12px;
        }
        .form-header div {
            margin: 3px 0;
        }
        .header-title {
            font-weight: bold;
        }
        .header-fields {
            margin-bottom: 15px;
            font-size: 11px;
        }
        .header-row {
            display: flex;
            margin-bottom: 8px;
            align-items: center;
        }
        .header-label {
            font-weight: bold;
            width: 35%;
            margin-right: 10px;
        }
        .header-value {
            flex: 1;
            border-bottom: 1px dotted black;
            padding: 2px 4px;
            min-height: 18px;
        }
        .column-numbers {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            margin-bottom: 0;
        }
        .column-numbers td {
            border: 1px solid black;
            padding: 4px 2px;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            height: 20px;
        }
        .wage-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            font-size: 11px;
            margin-bottom: 15px;
        }
        .wage-table th,
        .wage-table td {
            border: 1px solid black;
            padding: 6px 4px;
            text-align: left;
            vertical-align: middle;
            font-size: 10px;
        }
        .wage-table th {
            font-weight: bold;
            background-color: #fff;
            word-wrap: break-word;
            line-height: 1.2;
        }
        .wage-table td {
            height: 30px;
        }
        .col-1 { width: 14%; }
        .col-2 { width: 14%; text-align: center; }
        .col-3 { width: 14%; text-align: right; }
        .col-4 { width: 14%; text-align: right; }
        .col-5 { width: 14%; text-align: right; }
        .col-6 { width: 14%; text-align: right; }
        .col-7 { width: 16%; text-align: right; }
        .signature-section {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
            font-size: 11px;
        }
        .signature-block {
            text-align: center;
            width: 200px;
        }
        .signature-label {
            margin-top: 50px;
            font-size: 10px;
            line-height: 1.4;
        }
        .signature-space {
            margin-top: 20px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    @foreach($rows as $slip)
    <div class="form-container">
        <div class="form-header">
            <div class="header-title">FORM XIX</div>
            <div>[See Rule 78 (2) (b)]</div>
            <div class="header-title">Wage Slip</div>
        </div>

        <div class="header-fields">
            <div class="header-row">
                <div class="header-label">Name and Address of Contractor</div>
                <div class="header-value">{{ $slip['contractor_name'] ?? '' }}</div>
            </div>
            <div class="header-row">
                <div class="header-label">Name and Father's/Husband's name</div>
                <div class="header-value">{{ $slip['workman_name'] ?? '' }}{{ !empty($slip['father_name']) ? ' / ' . $slip['father_name'] : '' }}</div>
            </div>
            <div class="header-row">
                <div class="header-label">Name of the workman</div>
                <div class="header-value">{{ $slip['workman_name'] ?? '' }}</div>
            </div>
            <div class="header-row">
                <div class="header-label">Nature and location of work</div>
                <div class="header-value">{{ $slip['work_nature'] ?? '' }}{{ !empty($slip['work_location']) ? ' - ' . $slip['work_location'] : '' }}</div>
            </div>
            <div class="header-row">
                <div class="header-label">For the Week/Fortnight/Month ending</div>
                <div class="header-value">{{ $slip['period_ending'] ?? '' }}</div>
            </div>
        </div>

        <table class="column-numbers">
            <tr>
                <td style="width: 14%;">1</td>
                <td style="width: 14%;">2</td>
                <td style="width: 14%;">3</td>
                <td style="width: 14%;">4</td>
                <td style="width: 14%;">5</td>
                <td style="width: 14%;">6</td>
                <td style="width: 16%;">7</td>
            </tr>
        </table>

        <table class="wage-table">
            <thead>
                <tr>
                    <th class="col-1">No. of days worked</th>
                    <th class="col-2">No. of units worked in case of piece rate workers</th>
                    <th class="col-3">Rate of daily wages/piece rate</th>
                    <th class="col-4">Amount of overtime wages</th>
                    <th class="col-5">Gross wages payable</th>
                    <th class="col-6">Deductions, if any</th>
                    <th class="col-7">Net amount of wages paid</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="col-1">{{ $slip['days_worked'] ?? '' }}</td>
                    <td class="col-2">{{ $slip['piece_units'] ?? '' }}</td>
                    <td class="col-3">{{ isset($slip['daily_rate']) ? number_format($slip['daily_rate'], 2) : '' }}</td>
                    <td class="col-4">{{ isset($slip['overtime_wages']) ? number_format($slip['overtime_wages'], 2) : '' }}</td>
                    <td class="col-5">{{ isset($slip['gross_salary']) ? number_format($slip['gross_salary'], 2) : '' }}</td>
                    <td class="col-6">{{ isset($slip['total_deductions']) ? number_format($slip['total_deductions'], 2) : '' }}</td>
                    <td class="col-7">{{ isset($slip['net_salary']) ? number_format($slip['net_salary'], 2) : '' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="signature-section">
            <div class="signature-block">
                <div class="signature-label">
                    Initials of the Contractor<br>or his representative
                </div>
            </div>
        </div>

        <div class="signature-space"></div>
    </div>
    <div class="page-break"></div>
    @endforeach
</body>
</html>
