<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>FORM B - Register of Wages</title>
    <style>
        @page { size: A4 landscape; margin: 8mm 5mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 4.5px; color: #000; background: #fff; padding: 6mm 0; }

        .form-title { text-align: center; font-weight: bold; font-size: 6px; line-height: 1.3; margin-bottom: 1px; }

        .rates-table { width: 25%; border-collapse: collapse; margin-bottom: 1px; }
        .rates-table th, .rates-table td { border: 1px solid #000; font-size: 4.5px; padding: 1px; text-align: center; }
        .rates-table td:first-child { text-align: left; }

        .info-section { font-size: 4.5px; margin: 1px 0; line-height: 1.3; }
        .info-section p { margin: 0; padding: 0; text-align: left; }
        .info-section p .lbl { font-weight: bold; }

        table.data-table { width: 100%; border-collapse: collapse; table-layout: auto; margin: 0; font-size: 4.5px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 1px; text-align: center; vertical-align: middle; line-height: 1.2; white-space: nowrap; }
        table.data-table th { font-weight: bold; white-space: normal; word-break: break-word; }
        table.data-table td:nth-child(1) { text-align: center; }
        table.data-table td:nth-child(3) { text-align: left; white-space: nowrap; }
        table.data-table td.num { text-align: right; }
        table.data-table tr.col-nums td { font-weight: bold; background: #f0f0f0; text-align: center; }
        table.data-table tr.total-row td { font-weight: bold; }

        col.c1  { width: 2%; }
        col.c2  { width: 3%; }
        col.c3  { width: 26%; }
        col.c4  { width: 3%; }  col.c5  { width: 4%; }  col.c6  { width: 3%; }
        col.c7  { width: 3%; }  col.c8  { width: 3%; }  col.c9  { width: 3%; }
        col.c10 { width: 3%; }  col.c11 { width: 3%; }  col.c12 { width: 3%; }
        col.c13 { width: 3%; }  col.c14 { width: 2%; }  col.c15 { width: 2%; }
        col.c16 { width: 2%; }  col.c17 { width: 2%; }  col.c18 { width: 4%; }
        col.c19 { width: 3%; }  col.c20 { width: 3%; }
        col.c21 { width: 5%; }  col.c22 { width: 5%; }  col.c23 { width: 5%; }  col.c24 { width: 5%; }

        .signature-box { margin-top: 20px; margin-bottom: 4px; text-align: right; font-size: 4.5px; padding-right: 4px; }
        .signature-space { height: 0; }

        @media print {
            @page { size: A4 landscape; margin: 8mm 5mm; }
            body { margin: 0; padding: 6mm 0; }
            table.data-table { page-break-inside: avoid; break-inside: avoid; }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; break-inside: avoid; }
        }
    </style>
</head>
<body>

<table style="width:88%; margin-left:auto; margin-right:auto; border:1.5px solid #000; border-collapse:collapse;">
<tr><td style="padding:2px 2px 6px 2px; vertical-align:top; border:none;">

    <div class="form-title">
        FORM B<br>
        REGISTER OF WAGE [See rule 2(1)]
    </div>

    <table class="rates-table">
        <tr><th colspan="4">Rate of Minimum Wages</th></tr>
        <tr><td></td><td>Skilled</td><td>Semi-Skilled</td><td>Un Skilled</td></tr>
        <tr><td>Minimum Basic</td><td>{{ $minWages['basic']['skilled'] ?? '' }}</td><td>{{ $minWages['basic']['semi_skilled'] ?? '' }}</td><td>{{ $minWages['basic']['unskilled'] ?? '' }}</td></tr>
        <tr><td>DA</td><td>{{ $minWages['da']['skilled'] ?? '' }}</td><td>{{ $minWages['da']['semi_skilled'] ?? '' }}</td><td>{{ $minWages['da']['unskilled'] ?? '' }}</td></tr>
        <tr><td>Overtime</td><td>{{ $minWages['overtime']['skilled'] ?? '' }}</td><td>{{ $minWages['overtime']['semi_skilled'] ?? '' }}</td><td>{{ $minWages['overtime']['unskilled'] ?? '' }}</td></tr>
    </table>

    <div class="info-section">
        <p><span class="lbl">Name of the Establishment :</span>&nbsp;{{ $header['establishment_name'] ?? '' }}</p>
        <p><span class="lbl">Name of Owner :</span>&nbsp;{{ $header['owner_name'] ?? '' }}</p>
        <p><span class="lbl">Wage period :</span>&nbsp;{{ $header['wage_period'] ?? 'Monthly' }}</p>
    </div>

    <table class="data-table">
        <colgroup>
            <col class="c1"><col class="c2"><col class="c3"><col class="c4">
            <col class="c5"><col class="c6"><col class="c7"><col class="c8">
            <col class="c9"><col class="c10"><col class="c11"><col class="c12">
            <col class="c13"><col class="c14"><col class="c15"><col class="c16">
            <col class="c17"><col class="c18"><col class="c19"><col class="c20">
            <col class="c21"><col class="c22"><col class="c23"><col class="c24">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">Sr. No.</th>
                <th rowspan="2">UAN</th>
                <th rowspan="2">Name</th>
                <th rowspan="2">Rate of Wage</th>
                <th rowspan="2" style="white-space:nowrap;">No. of<br>Days<br>Worked</th>
                <th rowspan="2">OT Hours</th>
                <th colspan="7">EARNINGS</th>
                <th colspan="6">DEDUCTIONS</th>
                <th rowspan="2">Net Payment</th>
                <th rowspan="2" style="min-width:30px; width:5%;">Employer PF/ Welfare</th>
                <th rowspan="2" style="min-width:30px; width:5%;">Bank Transaction ID</th>
                <th rowspan="2" style="min-width:30px; width:5%;">Date of Payment</th>
                <th rowspan="2" style="min-width:30px; width:5%;">Remarks</th>
            </tr>
            <tr>
                <th>Basic</th><th>Spl. Basic</th><th>DA</th><th>OT Pay</th>
                <th>HRA</th><th>Others</th><th>Total</th>
                <th>PF</th><th>ESIC</th><th>Others</th><th>PT</th><th style="white-space:nowrap;">Recovery</th><th>Total</th>
            </tr>
            <tr class="col-nums">
                <td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td>
                <td>7</td><td>8</td><td>9</td><td>10</td><td>11</td><td>12</td><td>13</td>
                <td>14</td><td>15</td><td>16</td><td>17</td><td>18</td><td>19</td>
                <td>20</td><td>21</td><td>22</td><td>23</td><td>24</td>
            </tr>
        </thead>
        <tbody>
            @forelse($rows ?? [] as $i => $row)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $row['uan'] ?? $row['employee_code'] ?? '' }}</td>
                <td>{{ $row['employee_name'] ?? '' }}</td>
                <td class="num">{{ number_format($row['rate_of_wage'] ?? 0, 2) }}</td>
                <td>{{ $row['total_days_worked'] ?? '' }}</td>
                <td class="num">{{ number_format($row['overtime_hours'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($row['basic_earned'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($row['special_allowance'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($row['da_earned'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($row['overtime_wages'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($row['hra_earned'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($row['other_earnings'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($row['gross_salary'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($row['pf_employee'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($row['esi_employee'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($row['other_deductions'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($row['pt_deduction'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($row['recovery'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($row['total_deductions'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($row['net_salary'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($row['pf_employer'] ?? 0, 2) }}</td>
                <td>{{ $row['bank_transaction_id'] ?? '' }}</td>
                <td>{{ $row['payment_date'] ?? '' }}</td>
                <td>{{ $row['remarks'] ?? '' }}</td>
            </tr>
            @empty
            @endforelse
        </tbody>
        @if(!empty($totals ?? []))
        <tfoot>
            <tr class="total-row">
                <td colspan="6" style="text-align:center;">Grand Total</td>
                <td class="num">{{ number_format($totals['basic_earned'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($totals['special_allowance'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($totals['da_earned'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($totals['overtime_wages'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($totals['hra_earned'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($totals['other_earnings'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($totals['gross_salary'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($totals['pf_employee'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($totals['esi_employee'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($totals['other_deductions'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($totals['pt_deduction'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($totals['recovery'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($totals['total_deductions'] ?? 0, 2) }}</td>
                <td class="num">{{ number_format($totals['net_salary'] ?? 0, 2) }}</td>
                <td colspan="4"></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div style="height:40px;"></div>
    <div style="text-align:right; font-size:4.5px; padding-right:4px; padding-bottom:4px;">Seal Signature of The Contractor</div>

</td></tr>
</table>

</body>
</html>
