@extends('compliance.layouts.preview')

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM 25 - Muster Roll</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            padding: 12px;
            font-size: 9px;
        }
        .form-container {
            border: 1px solid black;
            padding: 10px;
            margin: 0 auto;
            width: 99%;
        }
        .form-header {
            text-align: center;
            margin-bottom: 10px;
            font-size: 10px;
        }
        .form-header div {
            margin: 2px 0;
        }
        .header-title {
            font-weight: bold;
        }
        .factory-details {
            margin-bottom: 8px;
            font-size: 9px;
        }
        .detail-row {
            margin-bottom: 4px;
            display: flex;
            align-items: center;
        }
        .detail-label {
            font-weight: bold;
            width: 25%;
            margin-right: 5px;
        }
        .detail-line {
            flex: 1;
            border-bottom: 1px solid black;
            height: 10px;
            padding-left: 3px;
        }
        .detail-row-three-col {
            display: flex;
            gap: 15px;
            margin-bottom: 4px;
        }
        .detail-col {
            flex: 1;
            display: flex;
            align-items: center;
        }
        .detail-col .detail-label {
            width: 40%;
        }
        .detail-col .detail-line {
            flex: 1;
        }
        .muster-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid black;
            font-size: 8px;
            margin-bottom: 8px;
            table-layout: fixed;
        }
        .muster-table th,
        .muster-table td {
            border: 1px solid black;
            padding: 2px 2px;
            text-align: center;
            vertical-align: middle;
            height: 16px;
            word-wrap: break-word;
        }
        .muster-table th {
            font-weight: bold;
            background-color: #fff;
            line-height: 1.1;
        }
        .muster-table td {
            text-align: left;
        }
        .col-1 {
            width: 5%;
        }
        .col-2 {
            width: 12%;
        }
        .col-3 {
            width: 10%;
        }
        .col-4 {
            width: 12%;
        }
        .col-5 {
            width: 10%;
        }
        .col-6 {
            width: 10%;
        }
        .col-7 {
            width: 8%;
        }
        .col-8 {
            width: 8%;
        }
        .col-9 {
            width: 12%;
        }
        .col-10 {
            width: 13%;
        }
        .text-center {
            text-align: center;
        }
        .footer-section {
            margin-top: 8px;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
        }
        .footer-left {
            flex: 1;
        }
        .footer-right {
            flex: 1;
            text-align: right;
        }
        .signature-line {
            margin-top: 30px;
            border-top: 1px solid #000;
            width: 150px;
            margin-left: auto;
        }
        .signature-label {
            margin-top: 2px;
            font-weight: bold;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <!-- Header -->
        <div class="form-header">
            <div>The Tamil Nadu Factories Rules</div>
            <div class="header-title">FORM 25 – Muster Roll</div>
            <div>(Prescribed under rule 103)</div>
        </div>

        <!-- Factory Details -->
        <div class="factory-details">
            <div class="detail-row">
                <div class="detail-label">Name and Address of the Factory</div>
                <div class="detail-line"></div>
            </div>

            <div class="detail-row-three-col">
                <div class="detail-col">
                    <div class="detail-label">Registration Number</div>
                    <div class="detail-line"></div>
                </div>
                <div class="detail-col">
                    <div class="detail-label">Muster Roll</div>
                    <div class="detail-line"></div>
                </div>
                <div class="detail-col">
                    <div class="detail-label">For the Month of</div>
                    <div class="detail-line"></div>
                </div>
            </div>
        </div>

        <!-- Muster Roll Table -->
        <table class="muster-table">
            <thead>
                <tr>
                    <th class="col-1">Sl. No.</th>
                    <th class="col-2">Name of the worker</th>
                    <th class="col-3">Father's Name</th>
                    <th class="col-4">Designation / nature of work</th>
                    <th class="col-5">Date of birth to be supported by extract from birth Register</th>
                    <th class="col-6">Place of employment</th>
                    <th class="col-7">Group</th>
                    <th class="col-8">Relay</th>
                    <th class="col-9">Periods of work</th>
                    <th class="col-10">Date</th>
                </tr>
                <tr>
                    <th class="col-5" colspan="1"></th>
                    <th class="col-5" colspan="1"></th>
                    <th class="col-5" colspan="1"></th>
                    <th class="col-5" colspan="1"></th>
                    <th class="col-5">Year &nbsp; Month &nbsp; Day</th>
                    <th class="col-5" colspan="1"></th>
                    <th class="col-5" colspan="1"></th>
                    <th class="col-5" colspan="1"></th>
                    <th class="col-5" colspan="1"></th>
                    <th class="col-5">1,2,3 to 31</th>
                </tr>
                <tr>
                    <th class="col-1 text-center">(1)</th>
                    <th class="col-2 text-center">(2)</th>
                    <th class="col-3 text-center">(3)</th>
                    <th class="col-4 text-center">(4)</th>
                    <th class="col-5 text-center">(5)</th>
                    <th class="col-6 text-center">(6)</th>
                    <th class="col-7 text-center">(7)</th>
                    <th class="col-8 text-center">(8)</th>
                    <th class="col-9 text-center">(9)</th>
                    <th class="col-10 text-center">(10)</th>
                </tr>
            </thead>
            <tbody>
                @for($i = 0; $i < 12; $i++)
                <tr>
                    <td class="col-1"></td>
                    <td class="col-2"></td>
                    <td class="col-3"></td>
                    <td class="col-4"></td>
                    <td class="col-5"></td>
                    <td class="col-6"></td>
                    <td class="col-7"></td>
                    <td class="col-8"></td>
                    <td class="col-9"></td>
                    <td class="col-10"></td>
                </tr>
                @endfor
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer-section">
            <div class="footer-left">
                <div>Date: _______________</div>
            </div>
            <div class="footer-right">
                <div class="signature-line"></div>
                <div class="signature-label"><strong>Signature of Manager/Occupier</strong><br>Name: _______________</div>
            </div>
        </div>
    </div>
</body>
</html>
