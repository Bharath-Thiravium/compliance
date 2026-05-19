@extends('layouts.app')

@section('content')
@php
$header = $header ?? [];
$rows = $rows ?? [];
$totals = $totals ?? [];
$is_nil = $is_nil ?? false;
@endphp



@section('form_title')
FORM 8 - REGISTER OF ACCIDENTS
@endsection

@section('act_reference')
[Under Factories Act, 1948]
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
@if($is_nil)
    <div class="nil-block">
        NIL - No accidents reported during this period
    </div>
@else
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">S.No.</th>
                @foreach(array_keys($rows[0] ?? []) as $column)
                <th>{{ ucwords(str_replace('_', ' ', $column)) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                @foreach($row as $value)
                <td>{{ is_numeric($value) ? number_format($value, 2) : ($value ?? 'N/A') }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
        @if(!empty($totals))
        <tfoot>
            <tr class="totals-row">
                <td colspan="{{ count($rows[0] ?? []) }}" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>{{ number_format(array_sum($totals), 2) }}</strong></td>
            </tr>
        </tfoot>
        @endif
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
