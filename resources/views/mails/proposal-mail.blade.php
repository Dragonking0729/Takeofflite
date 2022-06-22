<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>TakeOffLite Proposal Email</title>

    <style>
        a {
            font-weight: bold;
            font-size: 18px;
            padding: 18px;
            line-height: 100%;
            text-align: center;
            text-decoration: none;
            display: block;
            background-color: #70c2c3;
            border-radius: 3px;
            width: 70%;
            margin: auto;
        }
    </style>
</head>

<body style="background: #e8e9eb; font-family: Arial,Helvetica,sans-serif;">
<div style="margin: 2% 5%; padding: 20px 20px 10px; line-height: 24px; font-size: 16px; background-color: white;">
    <div style="text-align: center;">
        <img src="{{ asset('img/invoice-logo.png') }}" width="20%" alt="TKL_logo">
    </div>

    <p style="text-align: center; font-size: 36px; font-weight: bold;">
        PROPOSAL
    </p>
    <p style="padding-top: 25px; padding-bottom: 10px;">
        {{$customer_name}}<br>
        {{$address}}<br>
        {{$city}} {{$state}} {{$postal_code}}
    </p>

    @foreach($proposal_data as $item)
        <p style="padding-top: 15px;">
            {{$item->proposal_item_description}}
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$&nbsp;{{round($item->proposal_customer_price,2)}}
            <br>
            {{$item->proposal_customer_scope_explanation}}
        </p>
    @endforeach

    <p style="padding-top: 50px;">
        <strong>Proposal
            Total&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$&nbsp;{{$proposal_total}}</strong>
        <br><br><br><br>
        Thank you for your business!
    </p>
    <br><br><br><br>

    <p>
        <a href="#" style="color: white;">
            Submit payment
        </a>
    </p>

</div>
</body>

</html>
