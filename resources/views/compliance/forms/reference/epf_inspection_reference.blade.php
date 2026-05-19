@extends('compliance.layouts.statutory_reference_layout')

{{-- REFERENCE PDF: EPF_INSPECTION.pdf --}}
{{-- EXTRACT EXACT STRUCTURE FROM REFERENCE PDF AND POPULATE BELOW --}}

@section('form_title')
{{-- TODO: Extract exact title from reference PDF --}}
INSPECTION REGISTER
@endsection

@section('act_reference')
{{-- TODO: Extract exact act reference from reference PDF --}}
[Under Section 17 of the Employees Provident Funds and Miscellaneous Provisions Act, 1952]
@endsection

@section('rule_reference')
{{-- TODO: Extract exact rule reference from reference PDF --}}
[See Rule 44 of the Employees Provident Funds Scheme, 1952]
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
        <td class="establishment-label">Code No:</td>
        <td>{{ $header['branch']['pf_code'] ?? 'N/A' }}</td>
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
        NIL - No inspections conducted during this period
    </div>
@else
    <table class="data-table">
        <thead>
            <tr>
                {{-- TODO: Extract exact column headings from reference PDF --}}
                {{-- TODO: Verify column order matches reference PDF exactly --}}
                <th style="width: 5%;">S.No.</th>
                <th style="width: 12%;">Date of Inspection</th>
                <th style="width: 20%;">Name and Designation of Inspecting Officer</th>
                <th style="width: 15%;">Reference No.</th>
                <th style="width: 30%;">Remarks/Observations</th>
                <th style="width: 18%;">Signature of Inspecting Officer</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $row['inspection_date'] ?? '-' }}</td>
                <td>{{ $row['authority'] ?? '-' }}</td>
                <td>{{ $row['reference'] ?? '-' }}</td>
                <td>{{ $row['remarks'] ?? '-' }}</td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection

@section('declaration')
{{-- TODO: Extract exact declaration text from reference PDF --}}
Certified that the above inspection records are maintained as per statutory requirements.
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
                <strong>Signature of Employer/Manager</strong><br>
                Name: _______________<br>
                Designation: _______________
            </div>
            <div class="seal-placeholder">(Seal)</div>
        </td>
    </tr>
</table>
@endsection

@section('custom_styles')
<style>
    {{-- TODO: Add any form-specific styling from reference PDF --}}
</style>
@endsection
