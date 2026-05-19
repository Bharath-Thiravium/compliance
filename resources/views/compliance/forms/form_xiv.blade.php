<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM XIV - Employment Card</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9px;
            color: #000;
            background: #fff;
        }

        .form-container {
            width: 560px;
            margin: 20px auto;
        }

        /* ── SINGLE MASTER TABLE ── */
        .form-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .form-table td {
            border: 1px solid #000;
            padding: 3px 6px;
            vertical-align: middle;
            font-size: 9px;
            line-height: 1.3;
        }

        /* Header cell */
        .form-table td.header-cell {
            text-align: center;
            padding: 5px 6px;
            font-size: 10px;
            font-weight: bold;
            line-height: 1.4;
        }

        /* Label column */
        .form-table td.lbl {
            width: 42%;
            font-weight: bold;
        }

        /* Value column */
        .form-table td.val {
            width: 58%;
            font-weight: normal;
        }

        /* Signature row */
        .form-table td.sig-cell {
            padding: 4px 6px;
            vertical-align: bottom;
            font-weight: bold;
            font-size: 9px;
        }

        /* Signature space row */
        .form-table td.sig-space {
            height: 80px;
            border-bottom: none;
            vertical-align: bottom;
            text-align: right;
            padding: 4px 8px;
        }
        .sig-content {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }
        .sig-content .seal-img { width: 44px; height: auto; }
        .sig-content .sign-img { width: 72px; height: auto; }

        .page-break { page-break-after: always; }

        @media print {
            body { margin: 0; }
            .form-container { width: 100%; margin: 0; }
        }
    </style>
</head>
<body>

@forelse($cards as $card)
<div class="form-container">
<table class="form-table">

    {{-- ── HEADER ── --}}
    <tr>
        <td class="header-cell" colspan="2">
            FORM XIV<br>
            <span style="font-size:13px; font-weight:bold;">See Rule 76 Employment Card</span>
        </td>
    </tr>

    {{-- ── TOP INFO ROWS ── --}}
    <tr>
        <td class="lbl">Name and address of establishment in/under which contract is carried on :</td>
        <td class="val">{{ $card['establishment_name'] ?? '' }}</td>
    </tr>
    <tr>
        <td class="lbl">Name and address of Contractor :</td>
        <td class="val">{{ $card['contractor_name'] ?? '' }}</td>
    </tr>
    <tr>
        <td class="lbl">Nature of Work and Location of Work :</td>
        <td class="val">{{ $card['work_location'] ?? '' }}</td>
    </tr>
    <tr>
        <td class="lbl">Name and address of Principal Employer :</td>
        <td class="val">{{ $card['principal_employer'] ?? '' }}</td>
    </tr>

    {{-- ── NUMBERED DETAIL ROWS ── --}}
    <tr>
        <td class="lbl">1.&nbsp;&nbsp; Name of the workman</td>
        <td class="val">{{ $card['workman_name'] ?? '' }}</td>
    </tr>
    <tr>
        <td class="lbl">2.&nbsp;&nbsp; Sl. No. in the register of workmen employed</td>
        <td class="val">{{ $card['register_serial'] ?? '' }}</td>
    </tr>
    <tr>
        <td class="lbl">3.&nbsp;&nbsp; Nature of employment / Designation</td>
        <td class="val">{{ $card['designation'] ?? '' }}</td>
    </tr>
    <tr>
        <td class="lbl">4.&nbsp;&nbsp; Date of Joining</td>
        <td class="val">{{ $card['tenure'] ?? '' }}</td>
    </tr>
    <tr>
        <td class="lbl">5.&nbsp;&nbsp; Wage rate (with particulars of unit, in case of piece-work)</td>
        <td class="val">{{ $card['wage_rate'] ?? '' }}</td>
    </tr>
    <tr>
        <td class="lbl">6.&nbsp;&nbsp; Wage Period</td>
        <td class="val">{{ $card['wage_period'] ?? '' }}</td>
    </tr>
    <tr>
        <td class="lbl">7.&nbsp;&nbsp; Tenure of employment</td>
        <td class="val">From : {{ $card['tenure'] ?? '' }}</td>
    </tr>
    <tr>
        <td class="lbl">8.&nbsp;&nbsp; Remarks</td>
        <td class="val">{{ $card['remarks'] ?: '-' }}</td>
    </tr>

    {{-- ── SEAL / SIGNATURE SPACE ── --}}
    <tr>
        <td colspan="2" class="sig-space">
            <div class="sig-content">
                @if(!empty($card['seal_path']))
                    <img src="{{ $card['seal_path'] }}" class="seal-img" alt="seal">
                @endif
                @if(!empty($card['signature_path']))
                    <img src="{{ $card['signature_path'] }}" class="sign-img" alt="signature">
                @endif
            </div>
        </td>
    </tr>

    {{-- ── SIGNATURE LABELS ── --}}
    <tr>
        <td class="sig-cell">Signature of Individual</td>
        <td class="sig-cell" style="text-align:right;">Signature of Contractor</td>
    </tr>

</table>
</div>

@if(!$loop->last)
    <div class="page-break"></div>
@endif

@empty
<div class="form-container">
<table class="form-table">
    <tr>
        <td class="header-cell" colspan="2">
            FORM XIV<br>
            <span style="font-size:13px;">See Rule 76 Employment Card</span>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="text-align:center; padding:30px; font-size:13px;">
            No employment records found for the selected period.
        </td>
    </tr>
</table>
</div>
@endforelse

</body>
</html>
