@extends('compliance.layouts.preview')

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FORM 13 - Leave Book</title>
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
        .employee-info {
            margin-bottom: 8px;
            font-size: 9px;
        }
        .info-row {
            margin-bottom: 3px;
            display: flex;
            align-items: center;
        }
        .info-label {
            font-weight: bold;
            width: 25%;
            margin-right: 5px;
        }
        .info-line {
            flex: 1;
            border-bottom: 1px solid black;
            height: 10px;
            padding-left: 3px;
        }
        .register-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
            margin-bottom: 10px;
            table-layout: fixed;
        }
        .register-table th,
        .register-table td {
            border: 1px solid black;
            padding: 3px;
            text-align: center;
            vertical-align: middle;
            height: 16px;
            word-wrap: break-word;
            word-break: break-word;
        }
        .register-table th {
            font-weight: bold;
            background-color: #fff;
            line-height: 1.1;
        }
        .register-table td {
            text-align: left;
        }
        .col-sl {
            width: 5%;
        }
        .col-year {
            width: 8%;
        }
        .col-credit {
            width: 14%;
        }
        .col-earned {
            width: 14%;
        }
        .col-total {
            width: 14%;
        }
        .col-from {
            width: 10%;
        }
        .col-to {
            width: 10%;
        }
        .col-days {
            width: 10%;
        }
        .col-balance {
            width: 10%;
        }
        .col-remarks {
            width: 15%;
        }
        .text-center {
            text-align: center;
        }
        .footer-section {
            margin-top: 10px;
            font-size: 9px;
        }
        .footer-row {
            margin-bottom: 3px;
            display: flex;
            align-items: center;
        }
        .footer-label {
            font-weight: bold;
            width: 30%;
            margin-right: 5px;
        }
        .footer-line {
            flex: 1;
            border-bottom: 1px solid black;
            height: 10px;
            padding-left: 3px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <!-- Header -->
        <div class="form-header">
            <div>Tamil Nadu Shops and Establishments Rules</div>
            <div class="header-title">FORM 13</div>
            <div class="header-title">LEAVE BOOK</div>
        </div>

        <!-- Employee Information Section -->
        <div class="employee-info">
            <div class="info-row">
                <div class="info-label">Name of Establishment</div>
                <div class="info-line"></div>
            </div>

            <div class="info-row">
                <div class="info-label">Name of Employee</div>
                <div class="info-line"></div>
            </div>

            <div class="info-row">
                <div class="info-label">Employee No</div>
                <div class="info-line"></div>
            </div>

            <div class="info-row">
                <div class="info-label">Designation</div>
                <div class="info-line"></div>
            </div>

            <div class="info-row">
                <div class="info-label">Date of Joining</div>
                <div class="info-line"></div>
            </div>
        </div>

        <!-- Leave Register Table -->
        <table class="register-table">
            <thead>
                <tr>
                    <th class="col-sl">Sl. No.</th>
                    <th class="col-year">Year</th>
                    <th class="col-credit">Leave at credit at beginning of year</th>
                    <th class="col-earned">Leave earned during the year</th>
                    <th class="col-total">Total leave available</th>
                    <th class="col-from">From</th>
                    <th class="col-to">To</th>
                    <th class="col-days">No. of days</th>
                    <th class="col-balance">Balance</th>
                    <th class="col-remarks">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @for($i = 0; $i < 12; $i++)
                <tr>
                    <td class="col-sl"></td>
                    <td class="col-year"></td>
                    <td class="col-credit"></td>
                    <td class="col-earned"></td>
                    <td class="col-total"></td>
                    <td class="col-from"></td>
                    <td class="col-to"></td>
                    <td class="col-days"></td>
                    <td class="col-balance"></td>
                    <td class="col-remarks"></td>
                </tr>
                @endfor
            </tbody>
        </table>

        <!-- Footer Section -->
        <div class="footer-section">
            <div class="footer-row">
                <div class="footer-label">Employee Signature</div>
                <div class="footer-line"></div>
            </div>

            <div class="footer-row">
                <div class="footer-label">Employer / Manager Signature</div>
                <div class="footer-line"></div>
            </div>
        </div>
    </div>
</body>
</html>
