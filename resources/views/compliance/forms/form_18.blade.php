@extends('compliance.layouts.preview')

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM 18 - Report of Accident</title>
    <style>
        * { margin: 0; padding: 0; }
        body { font-family: 'Times New Roman', Times, serif; padding: 6px; font-size: 7.5px; }
        .form-container { border: 1px solid black; padding: 5px 0; margin: 0 auto; width: 99%; }
        .form-inner { padding: 0 7px; }
        .form-header { text-align: center; margin-bottom: 3px; }
        .form-header div { margin: 1px 0; font-size: 8.5px; }
        .header-title { font-weight: bold; font-size: 11px; }
        .header-sub { font-weight: bold; font-size: 9px; }
        .note { text-align: center; font-size: 6.5px; font-style: italic; margin-bottom: 4px; }
        .section-title { font-weight: bold; font-size: 7.5px; margin: 3px 0 2px 0; text-decoration: underline; }

        /* Reporting table */
        .rep-table { width: 100%; border-collapse: collapse; font-size: 6.5px; margin-bottom: 4px; table-layout: fixed; }
        .rep-table th, .rep-table td { border: 1px solid black; padding: 1px 3px; vertical-align: top; word-wrap: break-word; }
        .rep-table th { font-weight: bold; text-align: center; background: #fff; }

        /* Two-column field grid */
        .fields-grid { display: flex; gap: 6px; }
        .fields-col { flex: 1; }
        .fr { display: flex; align-items: flex-end; margin-bottom: 1.5px; }
        .fl { font-weight: bold; white-space: nowrap; margin-right: 2px; flex-shrink: 0; font-size: 7px; }
        .fv { flex: 1; border-bottom: 1px solid black; min-height: 9px; font-size: 7px; }
        .sub { margin-left: 8px; }
        .two { display: flex; gap: 6px; }
        .two .fr { flex: 1; margin-bottom: 1.5px; }

        /* Cert & Inspector */
        .cert { margin-top: 3px; font-size: 7px; border-top: 1px solid #ccc; padding-top: 2px; }
        .cert-text { margin-bottom: 2px; font-style: italic; }
        .insp { margin-top: 3px; font-size: 7px; border-top: 1px solid black; padding-top: 2px; }
        .insp-title { font-weight: bold; font-style: italic; margin-bottom: 2px; }
        .insp-grid { display: flex; gap: 8px; }
        .insp-col { flex: 1; }
        .if { display: flex; align-items: flex-end; margin-bottom: 1.5px; }
        .il { font-weight: bold; flex-shrink: 0; margin-right: 2px; font-size: 6.5px; white-space: nowrap; }
        .iv { flex: 1; border-bottom: 1px solid black; min-height: 9px; }
    </style>
</head>
<body>
<div class="form-container">
    <div class="form-inner">
        <div class="form-header">
            <div>The Tamil Nadu Factories Rules</div>
            <div class="header-title">FORM 18</div>
            <div>(Prescribed under Rule 96)</div>
            <div class="header-sub">Report of Accident</div>
        </div>
        <div class="note">(A separate report is to be filled up in respect of each person killed or injured and each report will constitute a separate accident.)</div>
        <div class="section-title">SECTION 1 – REPORTING AUTHORITY</div>
    </div>

    <table class="rep-table">
        <colgroup><col style="width:28%"><col style="width:47%"><col style="width:25%"></colgroup>
        <thead>
            <tr>
                <th>Type of accident</th>
                <th>Authority to whom report is to be sent</th>
                <th>Within what period</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Fatal or serious accident likely to prove fatal</strong></td>
                <td>1. Inspector of Factories &nbsp; 2. Chief Inspector of Factories &nbsp; 3. District Magistrate or Sub-division Officer &nbsp; 4. Officer-in-charge of nearest police station</td>
                <td>Within 12 hours of the accident</td>
            </tr>
            <tr>
                <td><strong>Causes bodily injury preventing work for 48 hours immediately following accident</strong></td>
                <td>Inspector of Factories</td>
                <td>Within 24 hours of expiry of 48 hours after occurrence</td>
            </tr>
        </tbody>
    </table>

    <div class="form-inner">
        <div class="section-title">SECTION 2 – ACCIDENT INFORMATION</div>

        <div class="fields-grid">
            <!-- Left column: fields 1-9 -->
            <div class="fields-col">
                <div class="fr"><div class="fl">1. Registration number of factory</div><div class="fv">{{ $data['registration_number'] ?? '' }}</div></div>
                <div class="fr"><div class="fl">2. Running serial number / Calendar year</div><div class="fv">{{ $data['serial_number'] ?? '' }}</div></div>
                <div class="fr"><div class="fl">3. Name and address of factory</div><div class="fv">{{ ($data['factory_name'] ?? '') . ' ' . ($data['factory_address'] ?? '') }}</div></div>
                <div class="fr"><div class="fl">4. Nature of industry</div><div class="fv">{{ $data['nature_of_industry'] ?? '' }}</div></div>
                <div class="fr"><div class="fl">5. Name and address of Occupier</div><div class="fv">{{ $data['occupier_name'] ?? '' }}</div></div>
                <div class="fr"><div class="fl">6. Name and address of Manager</div><div class="fv">{{ $data['manager_name'] ?? '' }}</div></div>
                <div class="fr"><div class="fl">7. Exact place where accident occurred</div><div class="fv">{{ $data['accident_place'] ?? '' }}</div></div>
                <div class="fr"><div class="fl">8. Particulars of person injured:</div></div>
                <div class="sub">
                    <div class="fr"><div class="fl">(a) Name</div><div class="fv">{{ $data['injured_name'] ?? '' }}</div></div>
                    <div class="fr"><div class="fl">(b) Address</div><div class="fv">{{ $data['injured_address'] ?? '' }}</div></div>
                    <div class="two">
                        <div class="fr"><div class="fl">(c) Sex</div><div class="fv">{{ $data['sex'] ?? '' }}</div></div>
                        <div class="fr"><div class="fl">(d) Age</div><div class="fv">{{ $data['age'] ?? '' }}</div></div>
                    </div>
                    <div class="two">
                        <div class="fr"><div class="fl">(e) Occupation</div><div class="fv">{{ $data['occupation'] ?? '' }}</div></div>
                        <div class="fr"><div class="fl">(f) Monthly wages</div><div class="fv">{{ $data['monthly_wages'] ?? '' }}</div></div>
                    </div>
                </div>
                <div class="fr"><div class="fl">9. Date and hour of accident</div><div class="fv">{{ $data['accident_date'] ?? '' }}</div></div>
            </div>

            <!-- Right column: fields 10-18 -->
            <div class="fields-col">
                <div class="fr"><div class="fl">10. Hours person started work on day of accident</div><div class="fv">{{ $data['work_start_time'] ?? '' }}</div></div>
                <div class="fr"><div class="fl">11. Describe clearly how accident occurred</div><div class="fv">{{ $data['accident_description'] ?? '' }}</div></div>
                <div class="fr"><div class="fl">12. What person was doing at time of accident</div><div class="fv">{{ $data['activity_at_time'] ?? '' }}</div></div>
                <div class="fr"><div class="fl">13. If caused by machinery:</div></div>
                <div class="sub">
                    <div class="fr"><div class="fl">(a) Name and part of machine</div><div class="fv">{{ $data['machine_name'] ?? '' }}</div></div>
                    <div class="fr"><div class="fl">(b) Whether moved by mechanical power</div><div class="fv">{{ $data['mechanical_power'] ?? '' }}</div></div>
                </div>
                <div class="fr"><div class="fl">14. Names and addresses of witnesses</div><div class="fv">{{ $data['witnesses'] ?? '' }}</div></div>
                <div class="fr"><div class="fl">15. Nature, extent, location of injury</div><div class="fv">{{ $data['injury_details'] ?? '' }}</div></div>
                <div class="fr"><div class="fl">16. Name and address of Doctor or Hospital</div><div class="fv">{{ $data['doctor_hospital'] ?? '' }}</div></div>
                <div class="fr"><div class="fl">17. If person has died:</div></div>
                <div class="sub">
                    <div class="two">
                        <div class="fr"><div class="fl">(a) Date/hour of death</div><div class="fv">{{ $data['death_date'] ?? '' }}</div></div>
                        <div class="fr"><div class="fl">(b) Date/hour of post-mortem</div><div class="fv">{{ $data['postmortem_date'] ?? '' }}</div></div>
                    </div>
                    <div class="fr"><div class="fl">(c) Doctor who conducted post-mortem</div><div class="fv">{{ $data['postmortem_doctor'] ?? '' }}</div></div>
                    <div class="fr"><div class="fl">(d) Reasons therefore</div><div class="fv">{{ $data['death_reasons'] ?? '' }}</div></div>
                </div>
                <div class="fr"><div class="fl">18. Any other relevant information</div><div class="fv">{{ $data['other_info'] ?? '' }}</div></div>
            </div>
        </div>

        <!-- Certification -->
        <div class="cert">
            <div class="cert-text">I certify that to the best of my knowledge and belief the above particulars are correct in every respect.</div>
            <div class="two">
                <div class="fr"><div class="fl">Date of despatch of report</div><div class="fv">{{ $data['despatch_date'] ?? '' }}</div></div>
                <div class="fr"><div class="fl">Signature of Manager (Name in BLOCK letters)</div><div class="fv">{{ $data['manager_signature'] ?? '' }}</div></div>
            </div>
        </div>

        <!-- Inspector Section -->
        <div class="insp">
            <div class="insp-title">(This part is to be filled up by the Inspector of Factories)</div>
            <div class="insp-grid">
                <div class="insp-col">
                    <div class="if"><div class="il">R. No./Accident No</div><div class="iv"></div></div>
                    <div class="if"><div class="il">Date of receipt</div><div class="iv"></div></div>
                    <div class="if"><div class="il">Date of investigation</div><div class="iv"></div></div>
                    <div class="if"><div class="il">Result of investigation</div><div class="iv"></div></div>
                </div>
                <div class="insp-col">
                    <div class="if"><div class="il">Industry No</div><div class="iv"></div></div>
                    <div class="if"><div class="il">Causation No</div><div class="iv"></div></div>
                    <div class="if"><div class="il">Sex (M/W/F/A)</div><div class="iv"></div></div>
                    <div class="if"><div class="il">Fatal/site of injury</div><div class="iv"></div></div>
                </div>
                <div class="insp-col">
                    <div class="if"><div class="il">Date of return to work</div><div class="iv"></div></div>
                    <div class="if"><div class="il">Minor/serious</div><div class="iv"></div></div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
