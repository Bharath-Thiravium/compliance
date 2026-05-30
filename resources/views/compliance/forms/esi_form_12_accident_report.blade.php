<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ESI FORM 12 - Accident Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { size: A4 portrait; margin: 6mm; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 7.5px;
            margin: 0;
            padding: 6px;
            background: #fff;
            color: #000;
        }
        .form-container {
            border: 2px solid #000;
            width: 100%;
            box-sizing: border-box;
            padding: 6px;
        }
        .form-header {
            display: table;
            width: 100%;
            margin-bottom: 4px;
            border: 1px solid black;
        }
        .header-cell {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 6px;
            border-right: 1px solid black;
            font-weight: bold;
            font-size: 10px;
        }
        .header-cell:last-child {
            border-right: none;
        }
        .section-title {
            font-weight: bold;
            font-size: 8px;
            margin-top: 3px;
            margin-bottom: 2px;
            border-bottom: 1px solid #000;
            padding: 1px 2px;
        }
        .field-row {
            display: table;
            width: 100%;
            margin-bottom: 2px;
            border: 1px solid #000;
        }
        .field-label {
            display: table-cell;
            width: 35%;
            padding: 2px 3px;
            font-weight: bold;
            border-right: 1px solid #000;
            vertical-align: top;
        }
        .field-value {
            display: table-cell;
            width: 65%;
            padding: 2px 3px;
            vertical-align: top;
            min-height: 14px;
        }
        .two-column-row {
            display: table;
            width: 100%;
            margin-bottom: 2px;
        }
        .two-column-cell {
            display: table-cell;
            width: 50%;
            border: 1px solid #000;
            padding: 2px 3px;
        }
        .two-column-cell:first-child {
            border-right: 1px solid black;
        }
        .checkbox-group {
            display: inline-block;
            margin-right: 15px;
        }
        .checkbox {
            border: 1px solid black;
            width: 12px;
            height: 12px;
            display: inline-block;
            margin-right: 3px;
            vertical-align: middle;
        }
        .checkbox.checked {
            text-align: center;
            line-height: 12px;
            font-size: 10px;
        }
        .witness-row {
            display: table;
            width: 100%;
            margin-bottom: 2px;
            border: 1px solid #000;
        }
        .witness-label {
            display: table-cell;
            width: 5%;
            padding: 2px 3px;
            border-right: 1px solid #000;
            font-weight: bold;
            vertical-align: top;
        }
        .witness-value {
            display: table-cell;
            width: 95%;
            padding: 2px 3px;
            vertical-align: top;
            min-height: 20px;
        }
        .declaration-section {
            border: 1px solid #000;
            padding: 4px;
            margin-bottom: 2px;
            font-size: 7.5px;
            line-height: 1.3;
        }
        .footer-row {
            display: table;
            width: 100%;
            margin-bottom: 2px;
            border: 1px solid #000;
        }
        .footer-cell {
            display: table-cell;
            width: 50%;
            padding: 2px 3px;
            border-right: 1px solid #000;
            vertical-align: top;
            min-height: 30px;
        }
        .footer-cell:last-child { border-right: none; }
        .footer-label { font-weight: bold; font-size: 8px; }
    </style>
</head>
<body>
    <div class="form-container">
        <!-- Header -->
        <div class="form-header">
            <div class="header-cell">FORM 12<br>(REGULATION 68)</div>
            <div class="header-cell">E.S.I CORPORATION</div>
            <div class="header-cell">ACCIDENT REPORT</div>
        </div>

        <!-- Employee Identification -->
        <div class="section-title">EMPLOYEE IDENTIFICATION</div>
        <div class="field-row">
            <div class="field-label">Name of Employer</div>
            <div class="field-value">{{ $data['employer_name'] ?? '' }}</div>
        </div>
        <div class="two-column-row">
            <div class="two-column-cell">
                <strong>Code No.</strong><br>{{ $data['code_no'] ?? '' }}
            </div>
            <div class="two-column-cell">
                <strong>Branch Office</strong><br>{{ $data['branch_office'] ?? '' }}
            </div>
        </div>

        <!-- Main Details -->
        <div class="section-title">MAIN DETAILS</div>
        <div class="field-row">
            <div class="field-label">Nature of Industry / Business</div>
            <div class="field-value">{{ $data['industry_nature'] ?? '' }}</div>
        </div>
        <div class="field-row">
            <div class="field-label">Name & Address of Insured Person</div>
            <div class="field-value">{{ $data['insured_name'] ?? '' }}</div>
        </div>
        <div class="two-column-row">
            <div class="two-column-cell">
                <strong>Insurance No</strong><br>{{ $data['insurance_no'] ?? '' }}
            </div>
            <div class="two-column-cell">
                <strong>Sex</strong><br>{{ $data['sex'] ?? '' }}
            </div>
        </div>
        <div class="two-column-row">
            <div class="two-column-cell">
                <strong>Age (Last Birthday)</strong><br>{{ $data['age'] ?? '' }}
            </div>
            <div class="two-column-cell">
                <strong>Occupation</strong><br>{{ $data['occupation'] ?? '' }}
            </div>
        </div>

        <!-- Accident Location -->
        <div class="section-title">ACCIDENT LOCATION DETAILS</div>
        <div class="field-row">
            <div class="field-label">Address of premises where accident happened</div>
            <div class="field-value">{{ $data['accident_address'] ?? '' }}</div>
        </div>
        <div class="two-column-row">
            <div class="two-column-cell">
                <strong>Department</strong><br>{{ $data['department'] ?? '' }}
            </div>
            <div class="two-column-cell">
                <strong>Shift Hour</strong><br>{{ $data['shift_hour'] ?? '' }}
            </div>
        </div>
        <div class="field-row">
            <div class="field-label">Exact place of accident</div>
            <div class="field-value">{{ $data['exact_place'] ?? '' }}</div>
        </div>

        <!-- Injury Information -->
        <div class="section-title">INJURY INFORMATION</div>
        <div class="field-row">
            <div class="field-label">Nature and extent of injury</div>
            <div class="field-value">{{ $data['injury_nature'] ?? '' }}</div>
        </div>
        <div class="field-row">
            <div class="field-label">Location of injury</div>
            <div class="field-value">{{ $data['injury_location'] ?? '' }}</div>
        </div>
        <div class="field-row">
            <div class="field-label">Dispensary / ESI Hospital / Insurance No</div>
            <div class="field-value">{{ $data['hospital_info'] ?? '' }}</div>
        </div>
        <div class="field-row">
            <div class="field-label">Brief description of accident</div>
            <div class="field-value" style="min-height: 40px;">{{ $data['accident_description'] ?? '' }}</div>
        </div>

        <!-- Accident Consequence -->
        <div class="section-title">ACCIDENT CONSEQUENCE</div>
        <div class="field-row">
            <div class="field-label">Did accident cause death</div>
            <div class="field-value">
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['death'] ?? '' == 'yes' ? 'checked' : '' }}">{{ $data['death'] ?? '' == 'yes' ? '✓' : '' }}</span> Yes
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['death'] ?? '' == 'no' ? 'checked' : '' }}">{{ $data['death'] ?? '' == 'no' ? '✓' : '' }}</span> No
                </span>
            </div>
        </div>
        <div class="field-row">
            <div class="field-label">Date of Death</div>
            <div class="field-value">{{ $data['death_date'] ?? '' }}</div>
        </div>

        <!-- Disability / Benefit -->
        <div class="section-title">DISABILITY / BENEFIT</div>
        <div class="field-row">
            <div class="field-label">Whether wages in full or part payable</div>
            <div class="field-value">
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['wages_payable'] ?? '' == 'yes' ? 'checked' : '' }}">{{ $data['wages_payable'] ?? '' == 'yes' ? '✓' : '' }}</span> Yes
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['wages_payable'] ?? '' == 'no' ? 'checked' : '' }}">{{ $data['wages_payable'] ?? '' == 'no' ? '✓' : '' }}</span> No
                </span>
            </div>
        </div>
        <div class="field-row">
            <div class="field-label">Whether injured person was acting in contravention of rules</div>
            <div class="field-value">
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['contravention'] ?? '' == 'yes' ? 'checked' : '' }}">{{ $data['contravention'] ?? '' == 'yes' ? '✓' : '' }}</span> Yes
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['contravention'] ?? '' == 'no' ? 'checked' : '' }}">{{ $data['contravention'] ?? '' == 'no' ? '✓' : '' }}</span> No
                </span>
            </div>
        </div>

        <!-- Witness Section -->
        <div class="section-title">WITNESS INFORMATION</div>
        <div class="witness-row">
            <div class="witness-label">1.</div>
            <div class="witness-value">
                <strong>Name and address of witness</strong><br>{{ $data['witness_1'] ?? '' }}
            </div>
        </div>
        <div class="witness-row">
            <div class="witness-label">2.</div>
            <div class="witness-value">
                <strong>Name and address of witness</strong><br>{{ $data['witness_2'] ?? '' }}
            </div>
        </div>

        <!-- Cause of Accident -->
        <div class="section-title">CAUSE OF ACCIDENT</div>
        <div class="field-row">
            <div class="field-label">Machine involved</div>
            <div class="field-value">{{ $data['machine_involved'] ?? '' }}</div>
        </div>
        <div class="field-row">
            <div class="field-label">State whether machinery fenced</div>
            <div class="field-value">
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['machinery_fenced'] ?? '' == 'yes' ? 'checked' : '' }}">{{ $data['machinery_fenced'] ?? '' == 'yes' ? '✓' : '' }}</span> Yes
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['machinery_fenced'] ?? '' == 'no' ? 'checked' : '' }}">{{ $data['machinery_fenced'] ?? '' == 'no' ? '✓' : '' }}</span> No
                </span>
            </div>
        </div>
        <div class="field-row">
            <div class="field-label">State what injured person was doing</div>
            <div class="field-value" style="min-height: 30px;">{{ $data['person_doing'] ?? '' }}</div>
        </div>

        <!-- Transport Accident -->
        <div class="section-title">TRANSPORT ACCIDENT DETAILS</div>
        <div class="field-row">
            <div class="field-label">Was employee travelling in employer vehicle</div>
            <div class="field-value">
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['employer_vehicle'] ?? '' == 'yes' ? 'checked' : '' }}">{{ $data['employer_vehicle'] ?? '' == 'yes' ? '✓' : '' }}</span> Yes
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['employer_vehicle'] ?? '' == 'no' ? 'checked' : '' }}">{{ $data['employer_vehicle'] ?? '' == 'no' ? '✓' : '' }}</span> No
                </span>
            </div>
        </div>
        <div class="field-row">
            <div class="field-label">With employer permission</div>
            <div class="field-value">
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['employer_permission'] ?? '' == 'yes' ? 'checked' : '' }}">{{ $data['employer_permission'] ?? '' == 'yes' ? '✓' : '' }}</span> Yes
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['employer_permission'] ?? '' == 'no' ? 'checked' : '' }}">{{ $data['employer_permission'] ?? '' == 'no' ? '✓' : '' }}</span> No
                </span>
            </div>
        </div>
        <div class="field-row">
            <div class="field-label">Transport operated by employer</div>
            <div class="field-value">
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['transport_operated'] ?? '' == 'yes' ? 'checked' : '' }}">{{ $data['transport_operated'] ?? '' == 'yes' ? '✓' : '' }}</span> Yes
                </span>
                <span class="checkbox-group">
                    <span class="checkbox {{ $data['transport_operated'] ?? '' == 'no' ? 'checked' : '' }}">{{ $data['transport_operated'] ?? '' == 'no' ? '✓' : '' }}</span> No
                </span>
            </div>
        </div>

        <!-- Declaration -->
        <div class="declaration-section">
            <strong>DECLARATION</strong><br><br>
            I certify that to the best of my knowledge and belief the above particulars are correct.
        </div>

        <div class="field-row">
            <div class="field-label">Date of despatch of accident report</div>
            <div class="field-value">{{ $data['despatch_date'] ?? '' }}</div>
        </div>
        <div class="two-column-row">
            <div class="two-column-cell">
                <strong>Signature</strong><br><br><br>
            </div>
            <div class="two-column-cell">
                <strong>Designation</strong><br>{{ $data['designation'] ?? '' }}
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-row">
            <div class="footer-cell">
                <div class="footer-label">Diary No & Date</div>
                <div style="margin-top: 20px;">{{ $data['diary_no'] ?? '' }}</div>
            </div>
            <div class="footer-cell">
                <div class="footer-label">Branch Office Manager</div>
                <div style="margin-top: 20px;">{{ $data['branch_manager'] ?? '' }}</div>
            </div>
        </div>
    </div>
</body>
</html>
