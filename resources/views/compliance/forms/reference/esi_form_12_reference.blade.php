@extends('compliance.layouts.statutory_reference_layout')

{{-- REFERENCE PDF: ESI_FORM_12.pdf --}}
{{-- EXTRACT EXACT STRUCTURE FROM REFERENCE PDF AND POPULATE BELOW --}}

@section('form_title')
{{-- TODO: Extract exact title from reference PDF --}}
FORM 12 - ACCIDENT BOOK
@endsection

@section('act_reference')
{{-- TODO: Extract exact act reference from reference PDF --}}
[Under Section 55 of the Employees State Insurance Act, 1948]
@endsection

@section('rule_reference')
{{-- TODO: Extract exact rule reference from reference PDF --}}
[See Rule 67 of the Employees State Insurance (General) Regulations, 1950]
@endsection

@section('establishment_info')
<table>
    <tr>
        <td class="establishment-label">Name of Factory/Establishment:</td>
        <td>{{ $header['tenant']['name'] }}</td>
    </tr>
    <tr>
        <td class="establishment-label">Address:</td>
        <td>{{ $header['branch']['address'] ?? 'N/A' }}</td>
    </tr>
    <tr>
        <td class="establishment-label">Code No:</td>
        <td>{{ $header['branch']['esi_code'] ?? 'N/A' }}</td>
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
        NIL - No accidents reported during this period
    </div>
@else
    <table class="data-table">
        <thead>
            <tr>
                {{-- TODO: Extract exact column headings from reference PDF --}}
                {{-- TODO: Verify column order matches reference PDF exactly --}}
                <th style="width: 4%;">S.No.</th>
                <th style="width: 15%;">Name of Injured Person</th>
                <th style="width: 10%;">Token/I.C. No.</th>
                <th style="width: 12%;">Date and Time of Accident</th>
                <th style="width: 15%;">Nature and Cause of Injury</th>
                <th style="width: 12%;">Place of Accident</th>
                <th style="width: 15%;">Action Taken</th>
                <th style="width: 12%;">Signature of Employer</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row['employee_name'] ?? '-' }}</td>
                <td class="text-center">{{ $row['esi_number'] ?? '-' }}</td>
                <td class="text-center">{{ $row['incident_date'] ?? '-' }}</td>
                <td>{{ $row['incident_type'] ?? '-' }}</td>
                <td>{{ $row['location'] ?? '-' }}</td>
                <td>{{ $row['action_taken'] ?? '-' }}</td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection

@section('declaration')
{{-- TODO: Extract exact declaration text from reference PDF --}}
I hereby certify that the above entries are correct and complete as per the records maintained.
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
