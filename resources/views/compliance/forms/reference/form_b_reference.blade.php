@extends('compliance.layouts.statutory_reference_layout')

{{-- REFERENCE PDF: FORM_B_FACTORIES_ACT.pdf --}}
{{-- EXTRACT EXACT STRUCTURE FROM REFERENCE PDF AND POPULATE BELOW --}}

@section('form_title')
{{-- TODO: Extract exact title from reference PDF --}}
FORM B - REGISTER OF WAGES
@endsection

@section('act_reference')
{{-- TODO: Extract exact act reference from reference PDF --}}
[Under Section 13 of the Factories Act, 1948]
@endsection

@section('rule_reference')
{{-- TODO: Extract exact rule reference from reference PDF --}}
[See Rule 26 of the Factories Rules]
@endsection

@section('establishment_info')
<table>
    <tr>
        <td class="establishment-label">Name of Factory:</td>
        <td>{{ $header['tenant']['name'] }}</td>
    </tr>
    <tr>
        <td class="establishment-label">Address:</td>
        <td>{{ $header['branch']['address'] ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td class="establishment-label">Registration/License No:</td>
        <td>{{ $header['branch']['license'] ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td class="establishment-label">Wage Period:</td>
        <td>{{ $header['period'] }}</td>
    </tr>
</table>
@endsection

@section('content')
@if($is_nil)
    <div class="nil-block">
        {{-- TODO: Extract exact NIL text from reference PDF --}}
        NIL - No wages paid during this period
    </div>
@else
    <table class="data-table">
        <thead>
            <tr>
                {{-- TODO: Extract exact column headings from reference PDF --}}
                {{-- TODO: Verify column order matches reference PDF exactly --}}
                <th style="width: 3%;">S.No.</th>
                <th style="width: 8%;">Employee Code</th>
                <th style="width: 15%;">Name of Worker</th>
                <th style="width: 10%;">Designation</th>
                <th style="width: 7%;">Basic Wages</th>
                <th style="width: 7%;">Dearness Allowance</th>
                <th style="width: 7%;">House Rent Allowance</th>
                <th style="width: 7%;">Overtime Wages</th>
                <th style="width: 7%;">Gross Wages</th>
                <th style="width: 6%;">P.F.</th>
                <th style="width: 6%;">E.S.I.</th>
                <th style="width: 7%;">Total Deductions</th>
                <th style="width: 7%;">Net Wages Paid</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row['employee_code'] }}</td>
                <td>{{ $row['employee_name'] }}</td>
                <td>{{ $row['designation'] }}</td>
                <td class="text-right">{{ number_format($row['basic_earned'], 2) }}</td>
                <td class="text-right">{{ number_format($row['da_earned'], 2) }}</td>
                <td class="text-right">{{ number_format($row['hra_earned'], 2) }}</td>
                <td class="text-right">{{ number_format($row['overtime_wages'], 2) }}</td>
                <td class="text-right">{{ number_format($row['gross_salary'], 2) }}</td>
                <td class="text-right">{{ number_format($row['pf_employee'], 2) }}</td>
                <td class="text-right">{{ number_format($row['esi_employee'], 2) }}</td>
                <td class="text-right">{{ number_format($row['total_deductions'], 2) }}</td>
                <td class="text-right">{{ number_format($row['net_salary'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals-row">
                {{-- TODO: Extract exact totals row label from reference PDF --}}
                <td colspan="4" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['basic_earned'], 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['da_earned'], 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['hra_earned'], 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['overtime_wages'], 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['gross_salary'], 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['pf_employee'], 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['esi_employee'], 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['total_deductions'], 2) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['net_salary'], 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
@endif
@endsection

@section('declaration')
{{-- TODO: Extract exact declaration text from reference PDF --}}
I hereby certify that the above particulars are correct to the best of my knowledge and belief.
@endsection

@section('signature_block')
<table class="signature-table">
    <tr>
        <td class="signature-left">
            {{-- TODO: Verify left side content from reference PDF --}}
            <div>Date: _______________</div>
            <div style="margin-top: 10px;">Place: _______________</div>
        </td>
        <td class="signature-right">
            {{-- TODO: Verify right side content and layout from reference PDF --}}
            <div class="signature-line"></div>
            <div class="signature-label">
                <strong>Signature of Manager/Occupier</strong><br>
                Name: _______________<br>
                Designation: _______________
            </div>
            <div class="seal-placeholder">(Seal of Factory)</div>
        </td>
    </tr>
</table>
@endsection

@section('custom_styles')
<style>
    {{-- TODO: Add any form-specific styling from reference PDF --}}
    {{-- Example: specific column widths, borders, fonts --}}
</style>
@endsection
