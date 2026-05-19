@extends('compliance.layouts.preview')

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM 17 - Health Register</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 8px;
            background: #fff;
            padding: 6px;
        }
        .page-wrap {
            width: 760px;
            margin: 0 auto;
        }
        .form-container {
            border: 1.5px solid black;
            padding: 8px 10px;
            width: 100%;
        }

        /* ── Header ── */
        .form-header {
            text-align: center;
            margin-bottom: 8px;
            font-size: 9px;
            line-height: 1.4;
        }
        .black-bold { font-weight: bold; }

        /* ── Certifying Surgeon ── */
        .surgeon-section { margin-bottom: 8px; font-size: 8px; }
        .surgeon-section .sec-title { font-weight: bold; margin-bottom: 3px; }
        .surgeon-row {
            display: flex;
            align-items: flex-end;
            margin-bottom: 4px;
            gap: 5px;
        }
        .surgeon-label { font-weight: bold; width: 18px; flex-shrink: 0; }
        .surgeon-name-line {
            flex: 1;
            border-bottom: 1px solid #000;
            min-height: 12px;
        }
        .period-group { display: flex; gap: 12px; flex-shrink: 0; }
        .period-item  { display: flex; align-items: flex-end; gap: 3px; }
        .period-item span { white-space: nowrap; font-size: 8px; }
        .period-line  { border-bottom: 1px solid #000; width: 70px; min-height: 12px; }

        /* ── Main Table ── */
        .register-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            font-size: 6px;
            margin-bottom: 6px;
            table-layout: fixed;
        }
        .register-table th,
        .register-table td {
            border: 1px solid black;
            padding: 1px 2px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .register-table th {
            font-weight: bold;
            background: #fff;
            line-height: 1.2;
        }
        .register-table td { height: 16px; }

        /* Column widths — total = 100% */
        .c1  { width: 3%;  }
        .c2  { width: 5%;  }
        .c3  { width: 14%; }
        .c4  { width: 3%;  }
        .c5  { width: 4%;  }
        .c6  { width: 7%;  }
        .c7  { width: 7%;  }
        .c8  { width: 7%;  }
        .c9  { width: 7%;  }
        .c10 { width: 7%;  }
        .c11 { width: 7%;  }
        .c12 { width: 8%;  }
        .c13 { width: 7%;  }
        .c14 { width: 7%;  }
        .c15 { width: 7%;  }

        .td-name   { text-align: left; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .td-left   { text-align: left; }
        .td-nowrap { white-space: nowrap; }

        /* ── Notes ── */
        .notes-section { margin-top: 6px; font-size: 7px; line-height: 1.4; }

        @media print {
            body { padding: 0; }
            .page-wrap { width: 100%; }
        }
    </style>
</head>
<body>
<div class="page-wrap">
<div class="form-container">

    <!-- Header -->
    <div class="form-header">
        <div>The Tamil Nadu Factories Rules</div>
        <div class="black-bold">FORM 17</div>
        <div>(Prescribed under Rule 14)</div>
        <div class="black-bold">Health Register</div>
        <div style="font-size:7.5px;">(In respect of persons employed in occupations declared to be dangerous operations under section 87)</div>
    </div>

    <!-- Certifying Surgeon -->
    <div class="surgeon-section">
        <div class="sec-title">Name of Certifying Surgeon:</div>

        @foreach(['a','b','c'] as $i => $letter)
        <div class="surgeon-row">
            <div class="surgeon-label">({{ $letter }})</div>
            <div class="surgeon-name-line">{{ $certifying_surgeons[$i]['name'] ?? '' }}</div>
            <div class="period-group">
                <div class="period-item">
                    <span>From</span>
                    <div class="period-line">{{ $certifying_surgeons[$i]['from_date'] ?? '' }}</div>
                </div>
                <div class="period-item">
                    <span>To</span>
                    <div class="period-line">{{ $certifying_surgeons[$i]['to_date'] ?? '' }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Register Table -->
    <table class="register-table">
        <thead>
            <tr>
                <th class="c1">Sl.<br>No</th>
                <th class="c2">Works<br>No.</th>
                <th class="c3">Name of Worker</th>
                <th class="c4">Sex</th>
                <th class="c5">Age<br>(last b'day)</th>
                <th class="c6">Date of<br>employment<br>on present<br>work</th>
                <th class="c7">Date of<br>leaving or<br>transfer to<br>other work</th>
                <th class="c8">Reason for<br>leaving,<br>transfer or<br>discharge</th>
                <th class="c9">Nature of<br>job, or<br>occupation</th>
                <th class="c10">Raw Material<br>or by-product<br>handled</th>
                <th class="c11">Result of<br>Medical Exam.</th>
                <th class="c12">If suspended<br>from work,<br>state period<br>with detailed<br>reasons</th>
                <th class="c13">Recertified<br>fit to resume<br>duty on<br>(with sign. of<br>Cert. Surgeon)</th>
                <th class="c14">If certificate<br>of unfitness<br>or suspension<br>issued to<br>workers</th>
                <th class="c15">Signature<br>with date<br>of Cert.<br>Surgeon</th>
            </tr>
            <tr>
                <th class="c1">(1)</th>
                <th class="c2">(2)</th>
                <th class="c3">(3)</th>
                <th class="c4">(4)</th>
                <th class="c5">(5)</th>
                <th class="c6">(6)</th>
                <th class="c7">(7)</th>
                <th class="c8">(8)</th>
                <th class="c9">(9)</th>
                <th class="c10">(10)</th>
                <th class="c11">(11)</th>
                <th class="c12">(12)</th>
                <th class="c13">(13)</th>
                <th class="c14">(14)</th>
                <th class="c15">(15)</th>
            </tr>
        </thead>
        <tbody>
            @if($is_nil)
                <tr><td colspan="15" style="text-align:center;font-weight:bold;padding:8px;">NIL</td></tr>
            @else
                @foreach($rows as $row)
                <tr>
                    <td>{{ $row['sl_no'] }}</td>
                    <td>{{ $row['works_no'] }}</td>
                    <td class="td-name">{{ $row['name_of_worker'] }}</td>
                    <td class="td-nowrap">{{ $row['sex'] }}</td>
                    <td>{{ $row['age_last_birthday'] }}</td>
                    <td>{{ $row['date_of_employment_on_present_work'] }}</td>
                    <td>{{ $row['date_of_leaving_or_transfer'] }}</td>
                    <td class="td-left">{{ $row['reason_for_leaving_transfer_or_discharge'] }}</td>
                    <td>{{ $row['nature_of_job_or_occupation'] }}</td>
                    <td>{{ $row['raw_material_or_byproduct_handled'] }}</td>
                    <td>{{ $row['result_of_medical_examination'] }}</td>
                    <td class="td-left">{{ $row['suspension_period_with_reasons'] }}</td>
                    <td>{{ $row['recertified_fit_to_resume_duty_on'] }}</td>
                    <td>{{ $row['certificate_of_unfitness_or_suspension_issued'] }}</td>
                    <td>{{ $row['signature_with_date_of_certifying_surgeon'] }}</td>
                </tr>
                @endforeach

                @for($i = 0; $i < max(0, 3 - count($rows)); $i++)
                <tr>
                    @for($c = 0; $c < 15; $c++)<td></td>@endfor
                </tr>
                @endfor
            @endif
        </tbody>
    </table>

    <!-- Notes -->
    <div class="notes-section">
        <strong>Note:</strong><br>
        (i) Column (8) – Detailed Summary of reasons for transfer or discharge should be stated<br>
        (ii) Column (11) – Should be expressed as fit/unfit/suspended
    </div>

</div>
</div>
</body>
</html>
