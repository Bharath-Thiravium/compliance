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
            font-size: 7.5px;
            color: #000;
            line-height: 1.2;
            margin: 0;
            -webkit-print-color-adjust: exact;
        }

        .form-container {
            width: 95%;
            max-width: 1100px;
            border: 1px solid #000;
            margin: 20px auto 0 auto;
            padding: 6px 8px;
            box-sizing: border-box;
        }

        /* ── HEADER ── */
        .header-block {
            position: relative;
            text-align: center;
            padding: 5px 0 3px;
            border-bottom: 0.5px solid #000;
        }

        .esi-logo {
            position: absolute;
            left: 10px;
            top: 5px;
            height: 34px;
        }

        .header-text {
            display: inline-block;
            text-align: center;
            line-height: 1.05;
            font-size: 8px;
        }

        .header-text .t1 { font-size: 9px;  font-weight: bold; margin: 0; padding: 0; }
        .header-text .t2 { font-size: 10px; font-weight: bold; margin: 0; padding: 0; }
        .header-text .t3 { font-size: 8px;  margin: 0; padding: 0; }
        .header-text .t4 { font-size: 9px;  font-weight: bold; margin: 0; padding: 0; }

        /* ── INFO TABLE ── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            border-bottom: 0.5px solid #000;
            padding: 1px 3px;
            font-size: 7.2px;
            vertical-align: middle;
            line-height: 1.05;
        }

        .info-table .label {
            width: 34%;
            font-weight: bold;
            white-space: nowrap;
            text-align: left;
        }

        .info-table .colon {
            width: 1.5%;
            text-align: center;
            padding-left: 0;
            padding-right: 0;
        }

        .info-table .value {
            width: 64.5%;
            text-align: left;
            padding-left: 1px;
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
            font-size: 6.8px;
            text-align: center;
            vertical-align: middle;
            padding: 1px;
            word-wrap: break-word;
            word-break: break-word;
            line-height: 1.0;
        }

        .data-table th {
            font-weight: bold;
            background-color: #fff;
        }

        .data-table th[colspan="5"] {
            text-align: center;
        }

        .data-table td { height: 22px; }

        /* ── COLUMN NUMBERS ROW ── */
        .col-numbers td {
            font-size: 7px;
            text-align: center;
            padding: 1px;
            font-weight: bold;
            height: 8px;
            line-height: 8px;
        }

        /* ── NIL ROW ── */
        .nil-row td {
            height: 9px;
            font-size: 7px;
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="form-container">

    {{-- ── HEADER ── --}}
    <div class="header-block">
        @php
            $logoPath = public_path('images/esi_logo.png');
            $logoSrc  = file_exists($logoPath)
                ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
                : '';
        @endphp
        @if($logoSrc)
            <img src="{{ $logoSrc }}" class="esi-logo" alt="">
        @endif
        <div class="header-text">
            <div class="t1">EMPLOYEES' STATE INSURANCE CORPORATION</div>
            <div class="t2">ACCIDENT BOOK</div>
            <div class="t3">(Regulation 66)</div>
            <div class="t4">REG. FORM 11</div>
        </div>
    </div>

    {{-- ── EXTRACT HEADER VARIABLES ── --}}
    @php
        $company_name       = $header['company_name']       ?? $company_name       ?? '';
        $contractor_name    = $header['contractor_name']    ?? $contractor_name    ?? '';
        $total_workers      = $header['total_workers']      ?? $total_workers      ?? '';
        $work_location      = $header['work_location']      ?? $work_location      ?? '';
        $principal_employer = $header['principal_employer'] ?? $principal_employer ?? '';
        $month_year         = $header['month_year']         ?? $month_year         ?? '';
        $rows               = $rows ?? $entries ?? [];
        \Illuminate\Support\Facades\Log::info('FORM_11 render', [
            'company' => $company_name,
            'period'  => $month_year,
            'rows'    => count($rows),
            'is_nil'  => $is_nil ?? empty($rows),
        ]);
    @endphp

    {{-- ── INFO SECTION ── --}}
    <table class="info-table">
        <tr>
            <td class="label">Name of the Company</td>
            <td class="colon">:</td>
            <td class="value">{{ $company_name }}</td>
        </tr>
        <tr>
            <td class="label">Name of the Contractor</td>
            <td class="colon">:</td>
            <td class="value">{{ $contractor_name }}</td>
        </tr>
        <tr>
            <td class="label">Total number of workers employed</td>
            <td class="colon">:</td>
            <td class="value">{{ $total_workers }}</td>
        </tr>
        <tr>
            <td class="label">Work location</td>
            <td class="colon">:</td>
            <td class="value">{{ $work_location }}</td>
        </tr>
        <tr>
            <td class="label">Name of the Principal Employer</td>
            <td class="colon">:</td>
            <td class="value">{{ $principal_employer }}</td>
        </tr>
        <tr>
            <td class="label">Month</td>
            <td class="colon">:</td>
            <td class="value">{{ $month_year }}</td>
        </tr>
    </table>

    {{-- ── DATA TABLE ── --}}

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
                <th rowspan="2">Sl.<br>No</th>
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
                <tr class="nil-row">
                    <td>NIL</td><td>NIL</td><td>NIL</td><td>NIL</td><td>NIL</td><td>NIL</td>
                    <td>NIL</td><td>NIL</td><td>NIL</td><td>NIL</td><td>NIL</td><td>NIL</td>
                    <td>NIL</td><td>NIL</td><td>NIL</td><td>NIL</td><td>NIL</td><td>NIL</td>
                </tr>
            @endif

            {{-- ── SIGNATURE ROW ── --}}
            <tr>
                <td colspan="18" style="height:90px; position:relative; border-top:0.5px solid #000; border-left:none; border-right:none; border-bottom:none;">
                    <div style="position:absolute; right:25px; top:8px; font-size:8px;">
                        For {{ $company_name ?? ($header['tenant']['name'] ?? 'Company Name') }}
                    </div>
                    <div style="position:absolute; right:25px; bottom:8px; text-align:center;">
                        @if(!empty($batch_signature))
                            @if(!empty($batch_signature['seal_path']))
                                <img src="{{ storage_path('app/' . $batch_signature['seal_path']) }}" style="height:36px; display:inline-block; vertical-align:bottom; margin-right:3px;">
                            @endif
                            <img src="{{ storage_path('app/' . $batch_signature['signature_path']) }}" style="height:50px; display:block; margin:auto;">
                            <div style="font-size:8px; margin-top:4px;">{{ $batch_signature['signatory_name'] ?? 'Authorized Signatory' }}<br>{{ $batch_signature['signatory_designation'] ?? '' }}</div>
                        @elseif(!empty($company_signature))
                            <img src="{{ storage_path('app/' . $company_signature) }}" style="height:50px; display:block; margin:auto;">
                            <div style="font-size:8px; margin-top:4px;">Authorized Signatory</div>
                        @else
                            <div style="height:50px;"></div>
                            <div style="font-size:8px; margin-top:4px;">Authorized Signatory</div>
                        @endif
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

</div>
</body>
</html>
