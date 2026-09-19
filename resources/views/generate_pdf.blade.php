<html>
<head>
    <title>{{ $title }}</title>

    <style type="text/css">

        body{
            font-family: 'Roboto Condensed', sans-serif;
        }
        .m-0{
            margin: 0px;
        }
        .p-0{
            padding: 0px;
        }
        .pt-5{
            padding-top:5px;
        }
        .mt-10{
            margin-top:10px;
        }
        .mt-50{
            margin-top:50px;
        }
        .text-center{
            text-align:center !important;
        }
        .w-100{
            width: 100%;
        }
        .w-80{
            width:80%;
        }
        .w-50{
            width:50%;
        }
        .w-48{
            width:48%;
        }
        .w-25{
            width:25%;
        }
        .w-15{
            width:15%;
        }
        .logo img{
            width:200px;
            height:60px;
        }
        .gray-color{
            color:#5D5D5D;
        }
        .text-bold{
            font-weight: bold;
        }
        .border{
            border:1px solid black;
        }
        table tr,th,td{
            border: 1px solid #000;
            border-collapse:collapse;
            padding:7px 8px;
        }
        table tr th{
            font-size:15px;
        }
        table tr td{
            font-size:13px;
        }
        table{
            border-collapse:collapse;
        }
        .float-left{
            float:left;
        }
        .table tbody tr:nth-child(odd){
            background-color: #ccc;
        }
        .add-detail{
            margin-bottom: 60px !important;
            height: 230px;
        }
        body{
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        footer{
            position: absolute; bottom: 0; left: 0; right: 0; padding: 10px; text-align: left;
        }
    </style>
</head>
<body>

<div class="add-detail mt-10">
    <div class="w-50 float-left mt-10" style="color: #283862;">
        <p class="m-0 pt-5 text-bold w-100" style="letter-spacing: -1px">Get Best Logistics</p>
        <p class="m-0 pt-5" style="width: 50%;font-size: 13px;">
            15740 E US Hwy 40,
            Kansas City, MO 64136
            info@getbestlogistics.com
            Office #: (214) 432-3249</p>
    </div>
    <div class="w-48 float-left text-bold mt-10" style="margin-top:30px; font-size:21px; text-align: right; color: #283862;">
        Invoice
    </div>



    <div style="clear: both;"></div>
    <p style="border-bottom: 1px #000 dashed; margin-top: 30px; margin-bottom: 40px;"></p>
    <div class="w-50 float-left">
        <p class="m-0 pt-5 text-bold w-100" style="font-size:14px;">Bill To:
            <span style="font-weight:normal; border: 1px #000 solid; padding: 2px;display: block;margin-top: -24px; margin-left: 80px;">
                <b>Company Name: </b>{{ $carrier->company_name }}</br>
                <b>MC Number: </b> {{ $carrier->mc_number }}</br>
                <b>Phone Number: </b> {{ $carrier->number }}</br>
                <b>Company Address: </b> {{ $carrier->street_address }} {{ $carrier->city_name }} {{ $carrier->zip_code }}
            </span>
        </p>
    </div>
    <div class="w-50 float-left" style="font-size:14px;">
        <p class="m-0 pt-5 text-bold w-80" style="text-align: left; margin-left: 90px;">Dated: <span>{{ date('F j, Y') }}</span></p>
        <p class="m-0 pt-5 text-bold w-80" style="text-align: left; margin-left: 90px;">Invoice Number: <span>{{$invoice_no}}</span></p>
    </div>
</div>


<table class="table w-100" style="margin-top: 100px !important;">
    <tr>
        <th class="w-5">Load #</th>
        <th class="w-5">Load Origin</th>
        <th class="w-5">Destination</th>
        <th class="w-5">PU Date</th>
        <th class="w-5">DL Date</th>
        <th class="w-5">Gross Rate</th>
    </tr>
    @php
        $gross_rate = $receivable = 0;
    @endphp
    @foreach($dispatchers as $dispatcher)
        @php
            $gross_rate += $dispatcher->rate;
            $receivable += $dispatcher->receivable;
        @endphp
        <tr align="center">
            <td>{{ $dispatcher->load_number}}</td>
            <td>{{ $dispatcher->pick_location}}</td>
            <td>{{ $dispatcher->delivery_location }}</td>
            <td>{{ \Carbon\Carbon::parse($dispatcher->pick_date)->format('F d, Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($dispatcher->delivery_date)->format('F d, Y') }}</td>
            <td style="font-weight: bold;">${{$dispatcher->rate}}</td>
        </tr>
    @endforeach
    <tr align="center" style="border: none;">

        <td colspan="6" style="border: none;background-color: #fff;border-color: #fff;outline:#fff"></td>
        {{--        <td></td>--}}
        {{--        <td></td>--}}
        {{--        <td></td>--}}
        {{--        <td></td>--}}
        {{--        <td></td>--}}
    </tr>
    <tr align="center" style="height: 90px; border: none;">

        <td colspan="6" style="border: none;background-color: #fff;border-color: #fff;outline:#fff"></td>
    </tr>
    <tr align="center" style="border: none; text-align: left!important;">

        <td style="border: none;background-color: #fff;border-color: #fff;outline:#fff"></td>
        <td style="border: none;background-color: #fff;border-color: #fff;outline:#fff"></td>
        <td style="border: none;background-color: #fff;border-color: #fff;outline:#fff"></td>
        <td colspan="2">Gross Amount</td>
        <td style="font-weight: bold;">${{ number_format($gross_rate, 2) }}</td>
    </tr>
    <tr align="center" style="border: none; text-align: left !important;">

        <td style="border: none;background-color: #fff;border-color: #fff;outline:#fff"></td>
        <td style="border: none;background-color: #fff;border-color: #fff;outline:#fff"></td>
        <td style="border: none;background-color: #fff;border-color: #fff;outline:#fff"></td>
        <td colspan="2">Total Payable Fee {{ $dispatcher->percentage ? '(' . $dispatcher->percentage . '%)' : '' }}</td>
        <td style="font-weight: bold;">${{ number_format($receivable, 2) }}</td>
    </tr>
    @if(isset($invoice))
    <tr align="center" style="border: none; text-align: left !important;">
        <td style="border: none;background-color: #fff;border-color: #fff;outline:#fff"></td>
        <td style="border: none;background-color: #fff;border-color: #fff;outline:#fff"></td>
        <td style="border: none;background-color: #fff;border-color: #fff;outline:#fff"></td>
        <td colspan="2">Paid to Date</td>
        <td style="font-weight: bold; color: #166534;">${{ number_format($invoice->paid_amount, 2) }}</td>
    </tr>
    <tr align="center" style="border: none; text-align: left !important;">
        <td style="border: none;background-color: #fff;border-color: #fff;outline:#fff"></td>
        <td style="border: none;background-color: #fff;border-color: #fff;outline:#fff"></td>
        <td style="border: none;background-color: #fff;border-color: #fff;outline:#fff"></td>
        <td colspan="2">Remaining Due ({{ strtoupper($invoice->status) }})</td>
        <td style="font-weight: bold; color: {{ $invoice->due_amount > 0 ? '#991b1b' : '#166534' }};">${{ number_format($invoice->due_amount, 2) }}</td>
    </tr>
    @endif
</table>
<div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 10px; text-align: left;">
    <p class="m-0 pt-5 text-bold w-100" style="margin-top: 40px; margin-bottom: 20px;">Payment Method: <span style="font-weight: normal;">(703) 656-5014 (Perfect Fright Solution)</span></p>
    <p class="m-0 pt-5 text-bold w-100" style="align-content: center; align-items: center;display: flex;">Supervisor Sign:
    <div style="margin-left: 140px; margin-top: -22px;">
        <img src="{{ asset('images/signature.png') }}" width="158" height="31" />
    </div>
    </p>
</div>

</p>
</body>
</html>
