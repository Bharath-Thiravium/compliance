@extends('compliance.layouts.tn_statutory_precision')

@section('form_title', 'FORM B - REGISTER OF WAGES')
@section('act_reference', '[Under Section 13 of the Factories Act, 1948]')
@section('rule_reference', '[See Rule 26 of the Tamil Nadu Factories Rules, 1950]')
@section('signatory_title', 'Manager or Occupier')

@section('additional_styles')
<style>
    /* Government-prescribed column widths with mm precision */
    .col-sno { width: 3%; min-width: 8mm; text-align: center; }
    .col-name { width: 12%; min-width: 30mm; text-align: left; padding-left: 2mm; }
    .col-desig { width: 10%; min-width: 25mm; text-align: left; padding-left: 2mm; }
    .col-days { width: 5%; min-width: 12mm; text-align: center; }
    .col-rate { width: 7%; min-width: 18mm; text-align: right; padding-right: 2mm; }
    .col-basic { width: 8%; min-width: 20mm; text-align: right; padding-right: 2mm; }
    .col-da { width: 7%; min-width: 18mm; text-align: right; padding-right: 2mm; }
    .col-ot { width: 7%; min-width: 18mm; text-align: right; padding-right: 2mm; }
    .col-others { width: 7%; min-width: 18mm; text-align: right; padding-right: 2mm; }
    .col-cash { width: 7%; min-width: 18mm; text-align: right; padding-right: 2mm; }
    .col-total { width: 8%; min-width: 20mm; text-align: right; padding-right: 2mm; }
    .col-deduct { width: 10%; min-width: 25mm; text-align: left; padding: 2mm; font-size: 8pt; }
    .col-net { width: 8%; min-width: 20mm; text-align: right; padding-right: 2mm; }
    .col-sign { width: 8%; min-width: 20mm; text-align: center; }
    .col-initial { width: 5%; min-width: 12mm; text-align: center; }
</style>
@endsection

@section('content')
@if($is_nil)
    <div class="nil-declaration">
        NIL RETURN<br>
        No entries for the period {{ $header['period'] ?? '' }}
    </div>
@else
    <table>
        <thead>
            <tr>
                <th class="col-sno" rowspan="2">Sl.<br>No.</th>
                <th class="col-name" rowspan="2">Name of Worker</th>
                <th class="col-desig" rowspan="2">Designation</th>
                <th class="col-days" rowspan="2">No. of Days<br>Worked</th>
                <th class="col-rate" rowspan="2">Daily<br>Rate</th>
                <th colspan="6">Wages Earned</th>
                <th class="col-deduct" rowspan="2">Deductions<br>(Nature)</th>
                <th class="col-net" rowspan="2">Net Amount<br>Paid</th>
                <th class="col-sign" rowspan="2">Signature/<br>Thumb<br>Impression</th>
                <th class="col-initial" rowspan="2">Initial of<br>Employer</th>
            </tr>
            <tr>
                <th class="col-basic">Basic<br>Wages</th>
                <th class="col-da">Dearness<br>Allowance</th>
                <th class="col-ot">Overtime</th>
                <th class="col-others">Others</th>
                <th class="col-cash">Other Cash<br>Payments</th>
                <th class="col-total">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
            @php
                $daysWorked = $row['total_days_worked'] ?? 0;
                $dailyRate = $row['daily_rate'] ?? 0;
                $others = ($row['hra_earned'] ?? 0);
                $otherCash = 0;
                $total = ($row['basic_earned'] ?? 0) + ($row['da_earned'] ?? 0) + ($row['overtime_wages'] ?? 0) + $others + $otherCash;
                $deductions = 'PF: ' . number_format($row['pf_employee'] ?? 0, 2) . ', ESI: ' . number_format($row['esi_employee'] ?? 0, 2);
                if (($row['advances'] ?? 0) > 0) $deductions .= ', Adv: ' . number_format($row['advances'] ?? '', 2);
                if (($row['fines'] ?? 0) > 0) $deductions .= ', Fine: ' . number_format($row['fines'] ?? '', 2);
            @endphp
            <tr>
                <td class="col-sno">{{ $index + 1 }}</td>
                <td class="col-name">{{ $row['employee_name'] ?? '-' }}</td>
                <td class="col-desig">{{ $row['designation'] ?? '-' }}</td>
                <td class="col-days">{{ $daysWorked ?? '' }}</td>
                <td class="col-rate">{{ number_format($dailyRate, 2) }}</td>
                <td class="col-basic">{{ number_format($row['basic_earned'] ?? 0, 2) }}</td>
                <td class="col-da">{{ number_format($row['da_earned'] ?? 0, 2) }}</td>
                <td class="col-ot">{{ number_format($row['overtime_wages'] ?? 0, 2) }}</td>
                <td class="col-others">{{ number_format($others, 2) }}</td>
                <td class="col-cash">{{ number_format($otherCash, 2) }}</td>
                <td class="col-total">{{ number_format($total, 2) }}</td>
                <td class="col-deduct">{{ $deductions ?? '' }}</td>
                <td class="col-net">{{ number_format($row['net_salary'] ?? 0, 2) }}</td>
                <td class="col-sign"></td>
                <td class="col-initial"></td>
            </tr>
            @endforeach
            <tr class="totals-row">
                <td colspan="5" style="text-align: right; padding-right: 5mm;"><strong>GRAND TOTAL</strong></td>
                <td class="col-basic"><strong>{{ number_format($totals['basic_earned'] ?? 0, 2) }}</strong></td>
                <td class="col-da"><strong>{{ number_format($totals['da_earned'] ?? 0, 2) }}</strong></td>
                <td class="col-ot"><strong>{{ number_format($totals['overtime_wages'] ?? 0, 2) }}</strong></td>
                <td class="col-others"><strong>{{ number_format($totals['hra_earned'] ?? 0, 2) }}</strong></td>
                <td class="col-cash"><strong>0.00</strong></td>
                <td class="col-total"><strong>{{ number_format($totals['gross_salary'] ?? 0, 2) }}</strong></td>
                <td class="col-deduct" style="text-align: right;"><strong>{{ number_format($totals['total_deductions'] ?? 0, 2) }}</strong></td>
                <td class="col-net"><strong>{{ number_format($totals['net_salary'] ?? 0, 2) }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>
@endif
@endsection

@section('declaration')
Certified that the above register is maintained in accordance with the provisions of the Factories Act, 1948 and the Tamil Nadu Factories Rules, 1950, and that the particulars entered therein are true to the best of my knowledge and belief.
@endsection
