@extends('compliance.layouts.statutory_reference_layout')

@section('form_title')
CONTRACTOR MASTER REGISTER
@endsection

@section('act_reference')
[Under Contract Labour (Regulation and Abolition) Act, 1970]
@endsection

@section('rule_reference')
[See Rule XX]
@endsection

@section('establishment_info')
<table>
    <tr>
        <td class="establishment-label">Name of Establishment:</td>
        <td>{{ $header['tenant'] ?? ''['name'] }}</td>
    </tr>
    <tr>
        <td class="establishment-label">Period:</td>
        <td>{{ $header['period'] ?? '' }}</td>
    </tr>
</table>
@endsection

@section('content')
@php
$totals = $totals ?? [];
@endphp
@if($is_nil)
    <div class="nil-block">
        NIL - No contractors registered during this period
    </div>
@else
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">S.No.</th>
                <th style="width: 25%;">Company Name</th>
                <th style="width: 15%;">License Number</th>
                <th style="width: 12%;">Valid From</th>
                <th style="width: 12%;">Valid To</th>
                <th style="width: 31%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row['company_name'] ?? '' }}</td>
                <td>{{ $row['license_number'] ?? '' }}</td>
                <td class="text-center">{{ $row['valid_from'] ?? '' }}</td>
                <td class="text-center">{{ $row['valid_to'] ?? '' }}</td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection

@section('declaration')
I hereby certify that the above particulars are correct to the best of my knowledge and belief.
@endsection

@section('signature_block')
<table class="signature-table">
    <tr>
        <td class="signature-left">
            <div>Date: _______________</div>
        </td>
        <td class="signature-right">
            <div class="signature-line"></div>
            <div class="signature-label">
                <strong>Signature of Manager/Authorized Person</strong>
            </div>
        </td>
    </tr>
</table>
@endsection
