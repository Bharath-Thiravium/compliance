@extends('compliance.layouts.statutory_reference_layout')

{{-- REFERENCE PDF: FORM_XIII_CLRA.pdf --}}
{{-- EXTRACT EXACT STRUCTURE FROM REFERENCE PDF AND POPULATE BELOW --}}

@section('form_title')
{{-- TODO: Extract exact title from reference PDF --}}
FORM XIII - REGISTER OF CONTRACT LABOUR
@endsection

@section('act_reference')
{{-- TODO: Extract exact act reference from reference PDF --}}
[Under Section 29 of the Contract Labour (Regulation and Abolition) Act, 1970]
@endsection

@section('rule_reference')
{{-- TODO: Extract exact rule reference from reference PDF --}}
[See Rule 75(1) of the Contract Labour (Regulation and Abolition) Central Rules, 1971]
@endsection

@section('establishment_info')
<table>
    <tr>
        <td class="establishment-label">Name of Establishment:</td>
        <td>{{ $header['tenant']['name'] }}</td>
    </tr>
    <tr>
        <td class="establishment-label">Address:</td>
        <td>{{ $header['branch']['address'] ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td class="establishment-label">Registration No:</td>
        <td>{{ $header['branch']['license'] ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td class="establishment-label">Period:</td>
        <td>{{ $header['period'] }}</td>
    </tr>
</table>
@endsection

@section('content')
@if($is_nil)
    <div class="nil-block">
        {{-- TODO: Extract exact NIL text from reference PDF --}}
        NIL - No contract labour employed during this period
    </div>
@else
    <table class="data-table">
        <thead>
            <tr>
                {{-- TODO: Extract exact column headings from reference PDF --}}
                {{-- TODO: Verify column order matches reference PDF exactly --}}
                <th style="width: 4%;">S.No.</th>
                <th style="width: 18%;">Name and Address of Workman</th>
                <th style="width: 18%;">Name of Contractor</th>
                <th style="width: 12%;">Date of Commencement of Employment</th>
                <th style="width: 12%;">Date of Termination</th>
                <th style="width: 12%;">Rate of Wages</th>
                <th style="width: 12%;">Nature of Work</th>
                <th style="width: 12%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row['worker_name'] ?? '-' }}</td>
                <td>{{ $row['contractor_name'] ?? '-' }}</td>
                <td class="text-center">{{ $row['deployment_start'] ?? '-' }}</td>
                <td class="text-center">{{ $row['deployment_end'] ?? '-' }}</td>
                <td class="text-right">{{ $row['wage_rate'] ?? '-' }}</td>
                <td>{{ $row['nature_of_work'] ?? '-' }}</td>
                <td>{{ $row['remarks'] ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection

@section('declaration')
{{-- TODO: Extract exact declaration text from reference PDF --}}
Certified that the particulars given above are true to the best of my knowledge and belief.
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
                <strong>Signature of Principal Employer</strong><br>
                Name: _______________<br>
                Designation: _______________
            </div>
            <div class="seal-placeholder">(Seal of Establishment)</div>
        </td>
    </tr>
</table>
@endsection

@section('custom_styles')
<style>
    {{-- TODO: Add any form-specific styling from reference PDF --}}
</style>
@endsection
