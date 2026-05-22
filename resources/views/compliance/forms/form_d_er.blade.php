<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>FORM 'D' - Equal Remuneration Register</title>
    <style>
        @page { size: A4 landscape; margin: 8mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #000; background: #fff; }

        .page-wrapper { padding: 3px; }
        .page-container { background: #fff; border: 2px solid #000; padding: 6px 6px 30px 6px; }

        .title-block { text-align: center; margin-bottom: 6px; border-bottom: 1px solid #000; padding-bottom: 4px; }
        .title-block .title    { font-weight: bold; font-size: 14px; }
        .title-block .rule     { font-size: 10px; }
        .title-block .subtitle { font-size: 11px; font-weight: bold; }

        /* Meta info table - full borders on all sides */
        .meta-tbl { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .meta-tbl td {
            border: 1px solid #000;
            padding: 3px 6px;
            font-size: 9px;
            text-align: left;
        }
        .meta-tbl td strong { font-weight: bold; }

        /* Month row */
        .month-row {
            text-align: center;
            font-size: 12px;
            font-weight: normal;
            padding: 4px 0;
            border: 1px solid #000;
            border-top: none;
        }

        /* Main data table */
        .form-d {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .form-d td, .form-d th {
            border: 1px solid #000;
            padding: 3px 2px;
            font-size: 9px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .form-d tr.header th {
            font-size: 8px;
            font-weight: bold;
            white-space: normal;
            word-wrap: break-word;
            line-height: 1.2;
            padding: 3px 2px;
            text-align: center;
        }
        .form-d tr.header th:nth-child(1),
        .form-d tr.header th:nth-child(2) { text-align: left; padding-left: 4px; }

        .form-d tr.numbers td {
            font-size: 8px;
            font-weight: bold;
            color: #1f4e79;
            text-align: center;
            padding: 2px;
        }
        .form-d tr.data td       { font-size: 9px; min-height: 24px; text-align: center; padding: 3px 2px; }
        .form-d tr.data td.left  { text-align: left; padding-left: 4px; }
        .form-d tr.data td.right { text-align: right; padding-right: 4px; }

        .form-d tr.no-data td {
            height: 40px;
            text-align: center;
            vertical-align: middle;
            font-size: 11px;
        }

        .signature-block {
            text-align: right;
            margin-top: 20px;
            padding: 0 20px 0 0;
        }
        .signature-block .for-text   { font-size: 11px; margin-bottom: 8px; }
        .signature-block .sig-image img { width: 100px; height: auto; margin: 4px 0; }
        .signature-block .designation { font-size: 10px; margin-top: 40px; }
    </style>
</head>
<body>
<div class="page-wrapper">
<div class="page-container">

    {{-- Title --}}
    <div class="title-block">
        <div class="title">FORM 'D'</div>
        <div class="rule">(See rule 6)</div>
        <div class="subtitle">Register to be maintained by the employer under Rule 6 of the Equal Remuneration Rules, 1976.</div>
    </div>

    {{-- Meta info rows - all with full borders --}}
    <table class="meta-tbl">
        <tr><td><strong>Name of the Company:</strong>  {{ $company_name ?? '' }}</td></tr>
        <tr><td><strong>Name of the Contractor:</strong>  {{ $contractor_name ?? '' }}</td></tr>
        <tr><td><strong>Total number of workers employed:</strong> {{ $total_workers ?? 0 }}</td></tr>
        <tr><td><strong>Total number of men workers employed:</strong> {{ $total_men ?? 0 }}</td></tr>
        <tr><td><strong>Total number of women workers employed:</strong> {{ $total_women ?? 0 }}</td></tr>
        <tr><td><strong>Work location:</strong> {{ $work_location ?? '' }}</td></tr>
        <tr><td><strong>Name of the Principal Employer:</strong> {{ $principal_employer ?? '' }}</td></tr>
    </table>

    {{-- Month row --}}
    <div class="month-row">{{ $month ?? '' }}-{{ isset($year) ? substr($year, -2) : '' }}</div>

    {{-- Main table --}}
    <table class="form-d">
        <colgroup>
            <col style="width:12%">
            <col style="width:15%">
            <col style="width:8%">
            <col style="width:8%">
            <col style="width:10%">
            <col style="width:10%">
            <col style="width:9%">
            <col style="width:8%">
            <col style="width:9%">
            <col style="width:11%">
        </colgroup>
        <thead>
            <tr class="header">
                <th>Category of workers</th>
                <th>Brief description of work</th>
                <th>No. of men employed</th>
                <th>No. of women employed</th>
                <th>Rate of remuneration paid</th>
                <th>Basic wage or salary</th>
                <th>Dearness rent allowance</th>
                <th>House</th>
                <th>Other allowance</th>
                <th>Cash value of concessional supply of essential commodities</th>
            </tr>
            <tr class="numbers">
                <td>1.</td><td>2.</td><td>3.</td><td>4.</td><td>5.</td>
                <td>6.</td><td>7.</td><td>8.</td><td>9.</td><td>10.</td>
            </tr>
        </thead>
        <tbody>
            @if(($total_women ?? 0) == 0)
                <tr class="no-data">
                    <td colspan="10">No Women Workers Employed on &nbsp; {{ $month ?? '' }} {{ $year ?? '' }}</td>
                </tr>
                {{-- Extra empty rows like reference image --}}
                <tr><td colspan="10" style="height:20px;"></td></tr>
            @else
                @forelse($rows ?? [] as $row)
                <tr class="data">
                    <td class="left">{{ $row['category'] ?? '' }}</td>
                    <td class="left">{{ $row['description'] ?? '' }}</td>
                    <td>{{ $row['men_count'] ?? 0 }}</td>
                    <td>{{ $row['women_count'] ?? 0 }}</td>
                    <td class="right">{{ number_format($row['rate_remuneration'] ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($row['basic_wage'] ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($row['da'] ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($row['hra'] ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($row['other_allowance'] ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($row['cash_value'] ?? 0, 2) }}</td>
                </tr>
                @empty
                <tr class="no-data"><td colspan="10">No records found.</td></tr>
                @endforelse
            @endif
        </tbody>
    </table>

    {{-- Signature --}}
    <div class="signature-block">
        <div class="for-text">For {{ $company_name ?? '' }}</div>
        @if(!empty($signature ?? ''))
            <div class="sig-image"><img src="{{ $signature }}" alt="signature"></div>
        @else
            <div style="height:50px;"></div>
        @endif
        <div class="designation">Authorized Signatory</div>
    </div>

</div>
</div>
</body>
</html>
