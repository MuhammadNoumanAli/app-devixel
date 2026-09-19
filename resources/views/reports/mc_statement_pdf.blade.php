<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style type="text/css">
        body {
            font-family: 'Roboto Condensed', sans-serif;
            color: #333;
            font-size: 13px;
        }
        .m-0 { margin: 0; }
        .pt-5 { padding-top: 5px; }
        .mt-10 { margin-top: 10px; }
        .mt-20 { margin-top: 20px; }
        .w-100 { width: 100%; }
        .w-50 { width: 50%; }
        .float-left { float: left; }
        .clear { clear: both; }
        .text-bold { font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .header-box {
            margin-bottom: 25px;
            height: 140px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #283862;
        }
        .title-badge {
            font-size: 22px;
            font-weight: bold;
            color: #283862;
            text-align: right;
        }

        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table th, table td {
            border: 1px solid #cbd5e1;
            padding: 7px 10px;
        }
        table th {
            background-color: #283862;
            color: #fff;
            font-size: 12px;
            text-transform: uppercase;
        }
        table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-due { background-color: #fee2e2; color: #991b1b; }
        .badge-partial { background-color: #e0f2fe; color: #075985; }
        .badge-paid { background-color: #dcfce7; color: #166534; }
    </style>
</head>
<body>

<div class="header-box">
    <div class="w-50 float-left">
        <p class="company-name m-0">Get Best Logistics</p>
        <p class="m-0 pt-5" style="color: #64748b;">
            15740 E US Hwy 40, Kansas City, MO 64136<br>
            info@getbestlogistics.com | (214) 432-3249
        </p>
    </div>
    <div class="w-50 float-left title-badge">
        CARRIER STATEMENT
        <p class="m-0 pt-5" style="font-size: 13px; font-weight: normal; color: #64748b;">
            Date: {{ date('F d, Y') }}
        </p>
    </div>
    <div class="clear"></div>
</div>

<div class="summary-card">
    <table style="margin-top: 0; border: none;">
        <tr style="background: none; border: none;">
            <td style="border: none; width: 50%;">
                <p class="m-0 text-bold" style="font-size: 14px; color: #283862;">Carrier Details</p>
                <p class="m-0 pt-5"><b>Company:</b> {{ $carrier_name }}</p>
                <p class="m-0 pt-5"><b>MC Number:</b> {{ $mc_number }}</p>
                @if($carrier)
                    <p class="m-0 pt-5"><b>Phone:</b> {{ $carrier->number }}</p>
                    <p class="m-0 pt-5"><b>Address:</b> {{ $carrier->street_address }} {{ $carrier->city_name }} {{ $carrier->zip_code }}</p>
                @endif
            </td>
            <td style="border: none; width: 50%; vertical-align: top;">
                <p class="m-0 text-bold" style="font-size: 14px; color: #283862;">Financial Overview</p>
                <p class="m-0 pt-5"><b>Total Invoiced (Payable):</b> ${{ number_format($totalInvoiced, 2) }}</p>
                <p class="m-0 pt-5" style="color: #166534;"><b>Total Paid:</b> ${{ number_format($totalPaid, 2) }}</p>
                <p class="m-0 pt-5" style="color: {{ $totalDue > 0 ? '#991b1b' : '#166534' }}; font-size: 15px; font-weight: bold;">
                    <b>Outstanding Balance:</b> ${{ number_format($totalDue, 2) }}
                </p>
            </td>
        </tr>
    </table>
</div>

<h4 style="color: #283862; margin-bottom: 5px; margin-top: 25px;">All Invoices for MC #{{ $mc_number }}</h4>
<table>
    <thead>
        <tr>
            <th>Invoice #</th>
            <th>Date</th>
            <th>Loads Attached</th>
            <th class="text-right">Payable Amount</th>
            <th class="text-right">Paid Amount</th>
            <th class="text-right">Remaining Due</th>
            <th class="text-center">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoices as $inv)
            <tr>
                <td class="text-bold">{{ $inv->invoice_no }}</td>
                <td>{{ $inv->invoice_date ? $inv->invoice_date->format('M d, Y') : 'N/A' }}</td>
                <td>
                    @foreach($inv->invoiceDispatches as $item)
                        <span style="font-size: 11px;">#{{ $item->load_number }}</span>@if(!$loop->last), @endif
                    @endforeach
                </td>
                <td class="text-right">${{ number_format($inv->total_amount, 2) }}</td>
                <td class="text-right" style="color: #166534;">${{ number_format($inv->paid_amount, 2) }}</td>
                <td class="text-right text-bold" style="color: {{ $inv->due_amount > 0 ? '#991b1b' : '#166534' }};">
                    ${{ number_format($inv->due_amount, 2) }}
                </td>
                <td class="text-center">
                    @if($inv->status === 'paid')
                        <span class="badge badge-paid">PAID</span>
                    @elseif($inv->status === 'partial')
                        <span class="badge badge-partial">PARTIAL</span>
                    @else
                        <span class="badge badge-due">DUE</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="font-weight: bold; background-color: #f1f5f9;">
            <td colspan="3" class="text-right">Total:</td>
            <td class="text-right">${{ number_format($totalInvoiced, 2) }}</td>
            <td class="text-right" style="color: #166534;">${{ number_format($totalPaid, 2) }}</td>
            <td class="text-right" style="color: {{ $totalDue > 0 ? '#991b1b' : '#166534' }};">${{ number_format($totalDue, 2) }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

<div style="position: absolute; bottom: 20px; left: 0; right: 0; border-top: 1px solid #e2e8f0; padding-top: 10px;">
    <p class="m-0 text-bold">Payment Instructions:</p>
    <p class="m-0 pt-5" style="color: #64748b;">Zelle / ACH: (703) 656-5014 | Perfect Freight Solution</p>
</div>

</body>
</html>
