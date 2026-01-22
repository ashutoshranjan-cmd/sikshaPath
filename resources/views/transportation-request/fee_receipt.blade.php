<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Transportation Fee Receipt</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            background: #fff;
            color: #333;
        }

        .receipt-container {
            max-width: 700px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header img {
            height: 60px;
            margin-bottom: 8px;
        }

        .header h2 {
            margin: 5px 0;
        }

        .header small {
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table,
        th,
        td {
            border: 1px solid #999;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background: #f4f4f4;
        }

        .summary {
            margin-top: 20px;
            border-top: 2px solid #444;
            padding-top: 10px;
            font-size: 14px;
        }

        .footer {
            text-align: center;
            margin-top: 25px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>

<body>

    <div class="receipt-container">
        <!-- Receipt Header -->
        <div class="header">
            @if ($school['horizontal_logo'] ?? '')
                <img src="{{ public_path('storage/') . $school['horizontal_logo'] }}" alt="School Logo">
            @else
                <img src="{{ public_path('assets/horizontal-logo2.svg') }}" alt="School Logo">
            @endif
            <h2>{{$school['school_name'] ?? ''}}</h2>
            <small>{{$school['school_address'] ?? ''}}</small><br>
            <h3>Transportation Fee Receipt</h3>
        </div>

        <!-- Student Details -->
        <table>
            <tr>
                <th>Receipt No.</th>
                <td>{{ $TransportationPayment->id }}</td>
                <th>Date</th>
                <td>{{ date('d-m-Y', strtotime($TransportationPayment->paid_at)) }}</td>
            </tr>
            <tr>
                <th>Student Name</th>
                <td>{{ $student->full_name }}</td>
                <th>Admission No.</th>
                <td>{{ $student->student->admission_no }}</td>
            </tr>
            <tr>
                <th>Class</th>
                <td>{{ $student->student->class_section->class->name }}</td>
                <th>Section</th>
                <td>{{ $student->student->class_section->section->name ?? '' }}</td>
            </tr>
        </table>

        <!-- Payment Details -->
        <table>
            <thead>
                <tr>
                    <th>Fee Description</th>
                    <th>Pickup Point</th>
                    <th>Duration</th>
                    <th>Amount (₹)</th>
                    <th>Mode</th>
                    @if($TransportationPayment->paymentTransaction->payment_gateway === 'cheque')
                        <th>Cheque number</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Transportation Fee</td>
                    <td>{{ $TransportationPayment->pickupPoint->name }}</td>
                    <td>{{ $TransportationPayment->transportationFee->duration }} Days</td>
                    <td>{{ $TransportationPayment->transportationFee->fee_amount }}</td>
                    <td>{{ ucwords($TransportationPayment->paymentTransaction->payment_gateway) }}</td>
                    @if($TransportationPayment->paymentTransaction->payment_gateway === 'cheque')
                        <td>{{ $TransportationPayment->paymentTransaction->order_id }}</td>
                    @endif
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    @if($TransportationPayment->paymentTransaction->payment_gateway === 'cheque')
                        <th colspan="5" style="text-align:right">Total Paid</th>
                    @else
                        <th colspan="4" style="text-align:right">Total Paid</th>
                    @endif
                    <th>{{ $TransportationPayment->transportationFee->fee_amount }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

</body>

</html>