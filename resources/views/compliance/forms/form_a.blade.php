<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM A - Employee Register</title>
    <style>
        @page { size: A4 landscape; margin: 8mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 3.5px; color: #000; display: flex; justify-content: center; align-items: center; min-height: 100vh; }

        .form-header { text-align: center; margin-bottom: 1mm; line-height: 1.2; }
        .form-header .t1 { font-size: 6px; font-weight: bold; color: #000; }
        .form-header .t2 { font-size: 5px; font-weight: bold; color: #000; }
        .form-header .t3 { font-size: 4px; font-weight: bold; color: #000; }

        .info-block { margin: 0.5mm 0 1mm 0; font-size: 4px; }
        .info-block p { margin: 1px 0; }
        .info-block p .lbl { font-weight: bold; display: inline-block; min-width: 140px; }

        .reg-table { width: 100%; border-collapse: collapse; table-layout: auto; font-size: 3px; }
        .reg-table th, .reg-table td {
            border: 0.5px solid #000;
            padding: 1px;
            vertical-align: middle;
            text-align: center;
            overflow: visible;
            white-space: normal;
            word-break: break-word;
            line-height: 1.2;
        }
        .reg-table thead th { font-weight: bold; font-size: 3px; color: #000; }
        .reg-table tbody td { height: auto; min-height: 10px; font-size: 3px; }
        .reg-table tr.col-num th { font-size: 3.5px; font-weight: bold; height: 6px; color: #000; }
        .tl { text-align: left !important; padding-left: 1px !important; }
        .tc { text-align: center !important; }
        .nil-row td { text-align: center; font-weight: bold; padding: 6px; }

        .sig-area { margin-top: 2mm; }
        .sig-row { width: 100%; margin-top: 3px; }
        .sig-row table { width: 100%; border-collapse: collapse; }
        .sig-row td { border: none; font-size: 6px; font-weight: bold; padding: 0; vertical-align: bottom; }
        .sig-left { text-align: left; width: 50%; vertical-align: bottom; }
        .sig-right { text-align: right; width: 50%; vertical-align: bottom; }
        .sig-block { display: inline-block; }
        .seal-img { height: 35px; display: block; margin: 0 auto 2px; }
        .sign-img  { height: 20px; display: block; margin: 0 auto; }
        .sig-name  { font-size: 5px; text-align: center; margin-top: 1px; }

        @media print {
            @page { size: A4 landscape; margin: 3mm; }
            body { margin: 0; }
            .reg-table tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

<div style="border: 1.5px solid #000; margin: auto; padding: 1.5mm; max-width: 90%; width: fit-content; transform: scale(0.85);">
<table style="width:100%; border:none; border-collapse:collapse;">
<tr><td style="padding:0.8mm; vertical-align:top; border:none;">

    <div class="form-header">
        <div class="t1">FORM A</div>
        <div class="t2">EMPLOYEE REGISTER</div>
        <div class="t3">[Part A :For all Establishments]</div>
    </div>

    @php
        $tenant    = $header['tenant'] ?? [];
        $branch    = $header['branch'] ?? [];
        $estName   = $tenant['establishment_name'] ?? $tenant['name'] ?? '';
        $ownerName = $tenant['name'] ?? '';
        $lin       = $tenant['lin_number'] ?? $tenant['factory_license_no'] ?? '';
        $rows      = $rows ?? [];
    @endphp

    <div class="info-block">
        <p><span class="lbl">Name of the Establishment</span> {{ $estName }}</p>
        <p><span class="lbl">Name of the Owner</span> {{ $ownerName }}</p>
        <p><span class="lbl">LIN</span> {{ $lin }}</p>
    </div>

    <table class="reg-table">
        <colgroup>
            <col style="width:1.5%">  {{-- 1  Sl No --}}
            <col style="width:3.5%">  {{-- 2  Emp Code --}}
            <col style="width:6.5%">  {{-- 3  Name --}}
            <col style="width:4%">    {{-- 4  Surname --}}
            <col style="width:2%">    {{-- 5  Gender --}}
            <col style="width:5%">    {{-- 6  Father/Spouse --}}
            <col style="width:4%">    {{-- 7  DOB --}}
            <col style="width:3%">    {{-- 8  Nationality --}}
            <col style="width:2.5%">  {{-- 9  Education --}}
            <col style="width:3.5%">  {{-- 10 DOJ --}}
            <col style="width:5%">    {{-- 11 Designation --}}
            <col style="width:4%">    {{-- 12 Category --}}
            <col style="width:4%">    {{-- 13 Employment --}}
            <col style="width:4.5%">  {{-- 14 Mobile --}}
            <col style="width:4.5%">  {{-- 15 UAN --}}
            <col style="width:3%">    {{-- 16 ESIC --}}
            <col style="width:2%">    {{-- 17 PF --}}
            <col style="width:1.5%">  {{-- 18 LWF --}}
            <col style="width:4%">    {{-- 19 Aadhar --}}
            <col style="width:4%">    {{-- 20 Bank A/c --}}
            <col style="width:4.5%">  {{-- 21 Branch IFSC --}}
            <col style="width:8%">    {{-- 22 Present Address --}}
            <col style="width:8%">    {{-- 23 Permanent Address --}}
            <col style="width:2%">    {{-- 24 Date of Exit --}}
            <col style="width:2%">    {{-- 25 Reason for Exit --}}
            <col style="width:4%">    {{-- 26 Mark of ID --}}
            <col style="width:3%">    {{-- 27 Photo --}}
            <col style="width:3%">    {{-- 28 Signature --}}
            <col style="width:1.5%">  {{-- 29 Remarks --}}
        </colgroup></colgroup>
        <thead>
            {{-- Row 1: column numbers --}}
            <tr class="col-num">
                <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th>
                <th>6</th><th>7</th><th>8</th><th>9</th><th>10</th>
                <th>11</th><th>12</th><th>13</th><th>14</th><th>15</th>
                <th>16</th><th>17</th><th>18</th><th>19</th><th>20</th>
                <th>21</th><th>22</th><th>23</th><th>24</th><th>25</th>
                <th>26</th><th>27</th><th>28</th><th>29</th>
            </tr>
            {{-- Row 2: main headers --}}
            <tr>
                <th style="white-space:nowrap;">Sl No</th>
                <th style="white-space:nowrap;">Employee Code</th>
                <th>Name</th>
                <th style="white-space:nowrap;">Surname</th>
                <th style="white-space:nowrap;">Gender</th>
                <th style="white-space:nowrap;">Father's/Spouse Name</th>
                <th style="white-space:nowrap;">Date of Birth<br>(D/M/Y)</th>
                <th style="white-space:nowrap;">Nationality</th>
                <th style="white-space:nowrap;">Education<br>Level</th>
                <th style="white-space:nowrap;">Date of<br>Joining</th>
                <th style="white-space:nowrap; min-width:40px;">Designation</th>
                          <th style="white-space:normal; font-size:3.5px; min-width:42px;">Category Address<br>(HS/SC/SC/ST)</th>
                <th style="white-space:nowrap;">Type of<br>Employment</th>
                <th style="white-space:nowrap;">Mobile</th>
                <th style="white-space:nowrap;">UAN</th>
                <th style="white-space:nowrap; min-width:30px;">ESIC/P</th>
                <th style="white-space:nowrap; min-width:30px;">PF</th>
                <th style="white-space:nowrap;">LWF</th>
                <th style="white-space:nowrap;">Aadhar Card No.</th>
                <th style="white-space:nowrap;">Bank A/c Number</th>
                <th style="white-space:nowrap;">Branch Name (IFSC)</th>
                <th style="white-space:nowrap;">Present Address</th>
                <th style="white-space:nowrap;">Permanent Address</th>
                <th style="white-space:nowrap;">Date of<br>Exit</th>
                <th style="white-space:nowrap;">Reason<br>for Exit</th>
                <th style="white-space:nowrap;">Marks of Identification</th>
                <th style="white-space:nowrap;">Photo</th>
                <th style="white-space:nowrap;">Specimen<br>Signature/Thumb<br>Impression</th>
                <th style="white-space:nowrap;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
            <tr>
                <td class="tc">{{ $i + 1 }}</td>
                <td class="tc">{{ $row['employee_code'] ?? '' }}</td>
                <td class="tl" style="white-space:nowrap;">{{ $row['employee_name'] ?? '' }}</td>
                <td class="tl">{{ $row['surname'] ?? '' }}</td>
                <td class="tc">{{ $row['gender'] ?? '' }}</td>
                <td class="tl">{{ $row['father_name'] ?? '' }}</td>
                <td class="tc">{{ $row['date_of_birth'] ?? '' }}</td>
                <td class="tc">{{ $row['nationality'] ?? 'Indian' }}</td>
                <td class="tc">{{ $row['education_level'] ?? '' }}</td>
                <td class="tc" style="white-space:nowrap;">{{ $row['date_of_joining'] ?? '' }}</td>
                <td class="tl">{{ $row['designation'] ?? '' }}</td>
                <td class="tc">{{ $row['category'] ?? '' }}</td>
                <td class="tc" style="white-space:nowrap; word-break:keep-all;">{{ $row['employment_type'] ?? '' }}</td>
                <td class="tc">{{ $row['mobile'] ?? '' }}</td>
                <td class="tc">{{ $row['uan_number'] ?? '' }}</td>
                <td class="tc" style="white-space:nowrap;">{{ $row['esi_number'] ?? '' }}</td>
                <td class="tc" style="white-space:nowrap;">{{ $row['pf_number'] ?? '' }}</td>
                <td class="tc">{{ $row['lwf'] ?? '-' }}</td>
                <td class="tc">{{ $row['aadhaar'] ?? '' }}</td>
                <td class="tc">{{ $row['bank_account'] ?? '' }}</td>
                <td class="tl">{{ $row['branch_ifsc'] ?? (($row['bank_name'] ?? '') . ($row['ifsc'] ? ' (' . $row['ifsc'] . ')' : '')) }}</td>
                <td class="tl">{{ $row['present_address'] ?? '' }}</td>
                <td class="tl">{{ $row['permanent_address'] ?? '' }}</td>
                <td class="tc">{{ $row['date_of_exit'] ?? '' }}</td>
                <td class="tc">{{ $row['reason_for_exit'] ?? '' }}</td>
                <td class="tl">{{ $row['identification_mark'] ?? '' }}</td>
                <td class="tc"></td>
                <td class="tc"></td>
                <td class="tc"></td>
            </tr>
            @empty
            <tr class="nil-row"><td colspan="29">No records found</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="font-size:5px; margin-top:2px;">*(Highly Skilled Skilled Semi Skilled Un Skilled)</div>

    <div class="sig-area">
        <table style="width:100%; border-collapse:collapse; margin-top:3mm;">
            <tr>
                <td style="width:50%; border:none; padding:0; height:40px; vertical-align:top;">&nbsp;</td>
                <td style="width:50%; border:none; padding:0; text-align:center; height:40px; vertical-align:top;">
                    @if(!empty($batch_signature))
                        @if(!empty($batch_signature['seal_path']))
                            <img src="{{ storage_path('app/' . $batch_signature['seal_path']) }}" class="seal-img">
                        @endif
                        <img src="{{ storage_path('app/' . $batch_signature['signature_path']) }}" class="sign-img">
                        <div class="sig-name">{{ $batch_signature['signatory_name'] ?? '' }}</div>
                    @elseif(!empty($company_signature))
                        <img src="{{ storage_path('app/' . $company_signature) }}" class="sign-img">
                    @endif
                </td>
            </tr>
        </table>
        <div style="margin-top:-2mm; padding-top:1mm;">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="width:50%; border:none; font-size:6px; font-weight:bold; text-align:left; padding:0;">Signature of The Principal Employer</td>
                    <td style="width:50%; border:none; font-size:6px; font-weight:bold; text-align:left; padding:0;">Seal &amp; Signature of The Contractor</td>
                </tr>
            </table>
        </div>
    </div>

</td></tr>
</table>
</div>

</body>
</html>
