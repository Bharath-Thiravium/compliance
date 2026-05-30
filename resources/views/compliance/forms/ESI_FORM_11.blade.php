<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ESI FORM 11 - Accident Book</title>
    <style>
        @page { size: A4 landscape; margin: 8mm; }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 8px;
            color: #000;
            line-height: 1.2;
            -webkit-print-color-adjust: exact;
        }

        .form-container {
            width: 100%;
            border: 1px solid #000;
        }

        /* ── HEADER ── */
        .header-block {
            padding: 4px 6px 3px;
            border-bottom: 0.5px solid #000;
        }

        .header-flex {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            min-height: 44px;
        }

        .logo {
            position: absolute;
            left: 0;
            height: 40px;
        }

        .header-text {
            text-align: center;
            line-height: 1.4;
        }

        .header-text .corp   { font-size: 9px;  font-weight: bold; }
        .header-text .title  { font-size: 10px; font-weight: bold; }
        .header-text .reg    { font-size: 8px; }
        .header-text .formno { font-size: 9px;  font-weight: bold; }

        /* ── INFO TABLE ── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            border-bottom: 0.5px solid #000;
            padding: 2px 6px;
            font-size: 8px;
            vertical-align: middle;
        }

        .info-table td:first-child {
            width: 35%;
            font-weight: bold;
            white-space: nowrap;
        }

        .info-table td:last-child {
            width: 65%;
        }

        /* ── DATA TABLE ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .data-table th,
        .data-table td {
            border: 0.5px solid #000;
            font-size: 7px;
            text-align: center;
            vertical-align: middle;
            padding: 2px 1px;
            word-wrap: break-word;
            line-height: 1.15;
        }

        .data-table th {
            font-weight: bold;
            background-color: #fff;
        }

        .data-table td {
            height: 18px;
        }

        /* ── COLUMN NUMBERS ROW ── */
        .col-numbers td {
            font-size: 7px;
            text-align: center;
            padding: 1px;
            font-weight: bold;
        }

        /* ── NIL ROW ── */
        .nil-row {
            text-align: center;
            font-weight: bold;
            font-size: 8px;
            padding: 4px 0;
        }

        /* ── SIGNATURE ── */
        .signature-area {
            height: 60px;
            position: relative;
            border-top: 0.5px solid #000;
            padding: 4px 6px;
        }

        .signature-block {
            position: absolute;
            right: 20px;
            bottom: 6px;
            text-align: center;
        }

        .signature-block img {
            height: 30px;
            display: block;
            margin: 0 auto;
        }

        .signature-text {
            font-size: 8px;
            margin-top: 2px;
        }
    </style>
</head>
<body>
<div class="form-container">

    {{-- HEADER --}}
    <div class="header-block">
        <div class="header-flex">
            @if(!empty($logo_path))
                <img src="{{ $logo_path }}" class="logo" alt="ESI Logo">
            @else
                <div style="position:absolute;left:0;font-size:22px;line-height:1;">&#9737;</div>
            @endif
            <div class="header-text">
                <div class="corp">EMPLOYEES' STATE INSURANCE CORPORATION</div>
                <div class="title">ACCIDENT BOOK</div>
                <div class="reg">(Regulation 66)</div>
                <div class="formno">REG. FORM 11</div>
            </div>
        </div>
    </div>

    {{-- INFO SECTION --}}
    <table class="info-table">
        <tr>
            <td>Name of the Company</td>
            <td>: {{ $company_name ?? '' }}</td>
        </tr>
        <tr>
            <td>Name of the Contractor</td>
            <td>: {{ $contractor_name ?? '' }}</td>
        </tr>
        <tr>
            <td>Total number of workers employed</td>
            <td>: {{ $total_workers ?? '' }}</td>
        </tr>
        <tr>
            <td>Work location</td>
            <td>: {{ $work_location ?? '' }}</td>
        </tr>
        <tr>
            <td>Name of the Principal Employer</td>
            <td>: {{ $principal_employer ?? '' }}</td>
        </tr>
        <tr>
            <td>Month</td>
            <td>: {{ $month_year ?? '' }}</td>
        </tr>
    </table>

    {{-- DATA TABLE --}}
    @php $rows = $rows ?? $entries ?? []; @endphp

    <table class="data-table">
        <colgroup>
            <col style="width:4%">
            <col style="width:5%">
            <col style="width:5%">
            <col style="width:10%">
            <col style="width:3%">
            <col style="width:3%">
            <col style="width:6%">
            <col style="width:8%">
            <col style="width:5%">
            <col style="width:5%">
            <col style="width:5%">
            <col style="width:5%">
            <col style="width:5%">
            <col style="width:8%">
            <col style="width:10%">
            <col style="width:7%">
            <col style="width:7%">
            <col style="width:4%">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="2">Sl. No</th>
                <th rowspan="2">Date of Notice</th>
                <th rowspan="2">Time of Notice</th>
                <th rowspan="2">Name &amp; Address of Injured Person</th>
                <th rowspan="2">Sex</th>
                <th rowspan="2">Age</th>
                <th rowspan="2">Insurance No</th>
                <th rowspan="2">Shift, Department &amp; Occupation</th>
                <th colspan="5">Details of Injury</th>
                <th rowspan="2">What exactly was the injured person doing</th>
                <th rowspan="2">Name, Occupation, Address &amp; Thumb Impression</th>
                <th rowspan="2">Signature of Person Making Entry</th>
                <th rowspan="2">Witness Details</th>
                <th rowspan="2">Remarks</th>
            </tr>
            <tr>
                <th>Cause</th>
                <th>Nature</th>
                <th>Date</th>
                <th>Time</th>
                <th>Place</th>
            </tr>
            <tr class="col-numbers">
                <td>1</td><td>2</td><td>3</td><td>4</td><td>5</td><td>6</td>
                <td>7</td><td>8</td><td>9</td><td>10</td><td>11</td><td>12</td>
                <td>13</td><td>14</td><td>15</td><td>16</td><td>17</td><td>18</td>
            </tr>
        </thead>
        <tbody>
            @if(count($rows) > 0)
                @foreach($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['date_of_notice'] ?? '' }}</td>
                    <td>{{ $row['time_of_notice'] ?? '' }}</td>
                    <td style="text-align:left;">{{ $row['injured_person'] ?? '' }}</td>
                    <td>{{ $row['sex'] ?? '' }}</td>
                    <td>{{ $row['age'] ?? '' }}</td>
                    <td>{{ $row['insurance_no'] ?? '' }}</td>
                    <td style="text-align:left;">{{ $row['occupation'] ?? '' }}</td>
                    <td>{{ $row['cause'] ?? '' }}</td>
                    <td>{{ $row['nature'] ?? '' }}</td>
                    <td>{{ $row['injury_date'] ?? '' }}</td>
                    <td>{{ $row['injury_time'] ?? '' }}</td>
                    <td>{{ $row['place'] ?? '' }}</td>
                    <td style="text-align:left;">{{ $row['activity'] ?? '' }}</td>
                    <td style="text-align:left;">{{ $row['first_aid_person'] ?? '' }}</td>
                    <td>{{ $row['signature'] ?? '' }}</td>
                    <td style="text-align:left;">{{ $row['witnesses'] ?? '' }}</td>
                    <td>{{ $row['remarks'] ?? '' }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td>
                    <td></td><td></td><td></td><td></td><td></td><td></td>
                    <td></td><td></td><td></td><td></td><td></td><td></td>
                </tr>
                <tr>
                    <td colspan="18" class="nil-row">NIL</td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- SIGNATURE --}}
    <div class="signature-area">
        <div class="signature-block">
            @if(!empty($batch_signature))
                @if(!empty($batch_signature['seal_path']))
                    <img src="{{ storage_path('app/' . $batch_signature['seal_path']) }}" style="height:28px;">
                @endif
                <img src="{{ storage_path('app/' . $batch_signature['signature_path']) }}" style="height:20px;">
                <div class="signature-text">
                    {{ $batch_signature['signatory_name'] ?? 'Authorized Signatory' }}<br>
                    {{ $batch_signature['signatory_designation'] ?? '' }}
                </div>
            @elseif(!empty($company_signature))
                <img src="{{ storage_path('app/' . $company_signature) }}" style="height:20px;">
                <div class="signature-text">Authorized Signatory</div>
            @else
                <div style="height:30px;"></div>
                <div class="signature-text">Authorized Signatory</div>
            @endif
        </div>
    </div>

</div>
</body>
</html>
