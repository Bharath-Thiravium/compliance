@extends('compliance.layouts.preview')

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ESI FORM 12 - Accident Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        @page { size: A4 portrait; margin: 8mm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 7.5px; margin: 0; padding: 0; }
        table { border-collapse: collapse; table-layout: fixed; }
        td { font-size: 7.5px; padding: 2px 3px; vertical-align: top; }
        .lbl { font-weight: bold; width: 35%; }
        .val { width: 65%; }
        .half { width: 50%; }
        .sec { font-weight: bold; font-size: 8px; padding: 2px 3px; border-bottom: 1px solid #000; margin-top: 2px; }
        .cb { border: 1px solid #000; width: 10px; height: 10px; display: inline-block; vertical-align: middle; text-align: center; line-height: 10px; font-size: 8px; margin-right: 2px; }
        .cg { margin-right: 10px; display: inline-block; }
    </style>
</head>
<body>
@php
    $d = array_merge([
        'employer_name'=>'','code_no'=>'','branch_office'=>'',
        'industry_nature'=>'','insured_name'=>'','insurance_no'=>'',
        'sex'=>'','age'=>'','occupation'=>'','accident_address'=>'',
        'department'=>'','shift_hour'=>'','exact_place'=>'',
        'injury_nature'=>'','injury_location'=>'','hospital_info'=>'',
        'accident_description'=>'','death'=>'no','death_date'=>'',
        'wages_payable'=>'no','contravention'=>'no','witness_1'=>'',
        'witness_2'=>'','machine_involved'=>'','machinery_fenced'=>'no',
        'person_doing'=>'','employer_vehicle'=>'no','employer_permission'=>'no',
        'transport_operated'=>'no','despatch_date'=>'','designation'=>'',
        'diary_no'=>'','branch_manager'=>'',
    ], ($rows ?? [])[0] ?? []);
    function ynCb2($v,$o){$c=($v==$o)?'&#10003;':'';return '<span class="cg"><span class="cb">'.$c.'</span> '.ucfirst($o).'</span>';}
@endphp

{{-- OUTER BORDER: single table, single row, single cell with border on all 4 sides --}}
<table width="100%" cellspacing="0" cellpadding="4" style="width:100%;border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;">
<tr>
<td style="border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;padding:3px;">

    {{-- HEADER --}}
    <table width="100%" cellspacing="0" style="width:100%;margin-bottom:2px;">
        <tr>
            <td width="33%" style="width:33%;text-align:center;font-weight:bold;font-size:9px;border:1px solid #000;padding:4px;">FORM 12<br>(REGULATION 68)</td>
            <td width="34%" style="width:34%;text-align:center;font-weight:bold;font-size:9px;border:1px solid #000;padding:4px;">E.S.I CORPORATION</td>
            <td width="33%" style="width:33%;text-align:center;font-weight:bold;font-size:9px;border:1px solid #000;padding:4px;">ACCIDENT REPORT</td>
        </tr>
    </table>

    {{-- EMPLOYEE IDENTIFICATION --}}
    <div class="sec">EMPLOYEE IDENTIFICATION</div>
    <table width="100%" cellspacing="0" style="width:100%;margin-bottom:2px;">
        <tr>
            <td class="lbl" style="border:1px solid #000;">Name of Employer</td>
            <td class="val" style="border:1px solid #000;">{{ $d['employer_name'] }}</td>
        </tr>
        <tr>
            <td class="half" style="border:1px solid #000;"><strong>Code No.</strong><br>{{ $d['code_no'] }}</td>
            <td class="half" style="border:1px solid #000;"><strong>Branch Office</strong><br>{{ $d['branch_office'] }}</td>
        </tr>
    </table>

    {{-- MAIN DETAILS --}}
    <div class="sec">MAIN DETAILS</div>
    <table width="100%" cellspacing="0" style="width:100%;margin-bottom:2px;">
        <tr>
            <td class="lbl" style="border:1px solid #000;">Nature of Industry / Business</td>
            <td class="val" style="border:1px solid #000;">{{ $d['industry_nature'] }}</td>
        </tr>
        <tr>
            <td class="lbl" style="border:1px solid #000;">Name &amp; Address of Insured Person</td>
            <td class="val" style="border:1px solid #000;">{{ $d['insured_name'] }}</td>
        </tr>
        <tr>
            <td class="half" style="border:1px solid #000;"><strong>Insurance No</strong><br>{{ $d['insurance_no'] }}</td>
            <td class="half" style="border:1px solid #000;"><strong>Sex</strong><br>{{ $d['sex'] }}</td>
        </tr>
        <tr>
            <td class="half" style="border:1px solid #000;"><strong>Age (Last Birthday)</strong><br>{{ $d['age'] }}</td>
            <td class="half" style="border:1px solid #000;"><strong>Occupation</strong><br>{{ $d['occupation'] }}</td>
        </tr>
    </table>

    {{-- ACCIDENT LOCATION --}}
    <div class="sec">ACCIDENT LOCATION DETAILS</div>
    <table width="100%" cellspacing="0" style="width:100%;margin-bottom:2px;">
        <tr>
            <td class="lbl" style="border:1px solid #000;">Address of premises where accident happened</td>
            <td class="val" style="border:1px solid #000;">{{ $d['accident_address'] }}</td>
        </tr>
        <tr>
            <td class="half" style="border:1px solid #000;"><strong>Department</strong><br>{{ $d['department'] }}</td>
            <td class="half" style="border:1px solid #000;"><strong>Shift Hour</strong><br>{{ $d['shift_hour'] }}</td>
        </tr>
        <tr>
            <td class="lbl" style="border:1px solid #000;">Exact place of accident</td>
            <td class="val" style="border:1px solid #000;">{{ $d['exact_place'] }}</td>
        </tr>
    </table>

    {{-- INJURY INFORMATION --}}
    <div class="sec">INJURY INFORMATION</div>
    <table width="100%" cellspacing="0" style="width:100%;margin-bottom:2px;">
        <tr>
            <td class="lbl" style="border:1px solid #000;">Nature and extent of injury</td>
            <td class="val" style="border:1px solid #000;">{{ $d['injury_nature'] }}</td>
        </tr>
        <tr>
            <td class="lbl" style="border:1px solid #000;">Location of injury</td>
            <td class="val" style="border:1px solid #000;">{{ $d['injury_location'] }}</td>
        </tr>
        <tr>
            <td class="lbl" style="border:1px solid #000;">Dispensary / ESI Hospital / Insurance No</td>
            <td class="val" style="border:1px solid #000;">{{ $d['hospital_info'] }}</td>
        </tr>
        <tr>
            <td class="lbl" style="border:1px solid #000;">Brief description of accident</td>
            <td class="val" style="border:1px solid #000;height:22px;">{{ $d['accident_description'] }}</td>
        </tr>
    </table>

    {{-- ACCIDENT CONSEQUENCE --}}
    <div class="sec">ACCIDENT CONSEQUENCE</div>
    <table width="100%" cellspacing="0" style="width:100%;margin-bottom:2px;">
        <tr>
            <td class="lbl" style="border:1px solid #000;">Did accident cause death</td>
            <td class="val" style="border:1px solid #000;">{!! ynCb2($d['death'],'yes') !!}{!! ynCb2($d['death'],'no') !!}</td>
        </tr>
        <tr>
            <td class="lbl" style="border:1px solid #000;">Date of Death</td>
            <td class="val" style="border:1px solid #000;">{{ $d['death_date'] }}</td>
        </tr>
    </table>

    {{-- DISABILITY / BENEFIT --}}
    <div class="sec">DISABILITY / BENEFIT</div>
    <table width="100%" cellspacing="0" style="width:100%;margin-bottom:2px;">
        <tr>
            <td class="lbl" style="border:1px solid #000;">Whether wages in full or part payable</td>
            <td class="val" style="border:1px solid #000;">{!! ynCb2($d['wages_payable'],'yes') !!}{!! ynCb2($d['wages_payable'],'no') !!}</td>
        </tr>
        <tr>
            <td class="lbl" style="border:1px solid #000;">Whether injured person was acting in contravention of rules</td>
            <td class="val" style="border:1px solid #000;">{!! ynCb2($d['contravention'],'yes') !!}{!! ynCb2($d['contravention'],'no') !!}</td>
        </tr>
    </table>

    {{-- WITNESS INFORMATION --}}
    <div class="sec">WITNESS INFORMATION</div>
    <table width="100%" cellspacing="0" style="width:100%;margin-bottom:2px;">
        <tr>
            <td width="4%" style="width:4%;border:1px solid #000;font-weight:bold;">1.</td>
            <td style="border:1px solid #000;height:20px;"><strong>Name and address of witness</strong><br>{{ $d['witness_1'] }}</td>
        </tr>
        <tr>
            <td width="4%" style="width:4%;border:1px solid #000;font-weight:bold;">2.</td>
            <td style="border:1px solid #000;height:20px;"><strong>Name and address of witness</strong><br>{{ $d['witness_2'] }}</td>
        </tr>
    </table>

    {{-- CAUSE OF ACCIDENT --}}
    <div class="sec">CAUSE OF ACCIDENT</div>
    <table width="100%" cellspacing="0" style="width:100%;margin-bottom:2px;">
        <tr>
            <td class="lbl" style="border:1px solid #000;">Machine involved</td>
            <td class="val" style="border:1px solid #000;">{{ $d['machine_involved'] }}</td>
        </tr>
        <tr>
            <td class="lbl" style="border:1px solid #000;">State whether machinery fenced</td>
            <td class="val" style="border:1px solid #000;">{!! ynCb2($d['machinery_fenced'],'yes') !!}{!! ynCb2($d['machinery_fenced'],'no') !!}</td>
        </tr>
        <tr>
            <td class="lbl" style="border:1px solid #000;">State what injured person was doing</td>
            <td class="val" style="border:1px solid #000;height:18px;">{{ $d['person_doing'] }}</td>
        </tr>
    </table>

    {{-- TRANSPORT ACCIDENT DETAILS --}}
    <div class="sec">TRANSPORT ACCIDENT DETAILS</div>
    <table width="100%" cellspacing="0" style="width:100%;margin-bottom:2px;">
        <tr>
            <td class="lbl" style="border:1px solid #000;">Was employee travelling in employer vehicle</td>
            <td class="val" style="border:1px solid #000;">{!! ynCb2($d['employer_vehicle'],'yes') !!}{!! ynCb2($d['employer_vehicle'],'no') !!}</td>
        </tr>
        <tr>
            <td class="lbl" style="border:1px solid #000;">With employer permission</td>
            <td class="val" style="border:1px solid #000;">{!! ynCb2($d['employer_permission'],'yes') !!}{!! ynCb2($d['employer_permission'],'no') !!}</td>
        </tr>
        <tr>
            <td class="lbl" style="border:1px solid #000;">Transport operated by employer</td>
            <td class="val" style="border:1px solid #000;">{!! ynCb2($d['transport_operated'],'yes') !!}{!! ynCb2($d['transport_operated'],'no') !!}</td>
        </tr>
    </table>

    {{-- DECLARATION --}}
    <div class="sec">DECLARATION</div>
    <table width="100%" cellspacing="0" style="width:100%;margin-bottom:2px;">
        <tr>
            <td style="border:1px solid #000;height:28px;font-size:7.5px;">
                I certify that to the best of my knowledge and belief the above particulars are correct.
            </td>
        </tr>
    </table>

    {{-- DESPATCH + SIGNATURE --}}
    <table width="100%" cellspacing="0" style="width:100%;margin-bottom:2px;">
        <tr>
            <td class="lbl" style="border:1px solid #000;">Date of despatch of accident report</td>
            <td class="val" style="border:1px solid #000;">{{ $d['despatch_date'] }}</td>
        </tr>
        <tr>
            <td class="half" style="border:1px solid #000;height:26px;"><strong>Signature</strong></td>
            <td class="half" style="border:1px solid #000;"><strong>Designation</strong><br>{{ $d['designation'] }}</td>
        </tr>
    </table>

    {{-- FOOTER --}}
    <table width="100%" cellspacing="0" style="width:100%;margin-bottom:0;">
        <tr>
            <td class="half" style="border:1px solid #000;height:28px;"><strong>Diary No &amp; Date</strong><br>{{ $d['diary_no'] }}</td>
            <td class="half" style="border:1px solid #000;"><strong>Branch Office Manager</strong><br>{{ $d['branch_manager'] }}</td>
        </tr>
    </table>

</td>
</tr>
</table>

</body>
</html>
