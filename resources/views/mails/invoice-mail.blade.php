<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>TakeOffLite Invoice Email</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: "Roboto";
        }
        .invoice_header_title {
            background-color: #365799;
            color: white;
            text-align: center;
            font-size: 60px;
            font-weight: bold;
        }

        .invoice_pay_block {
            margin-top: 50px;
            margin-bottom: 50px;
        }

        .invoice_pay_button {
            background-color: #365799;
            color: white;
            text-align: center;
            font-size: 30px;
            font-weight: bold;
            margin: auto;
            width: 30%;
            padding: 5px;
        }

        .invoice_pay_button:hover {
            cursor: pointer;
            opacity: 0.8;
        }

        .invoice_preview_header {
            color: #006c5c;
            border-bottom: 1px solid;
        }

        .invoice_body_items {
            padding-top: 50px;
            padding-bottom: 50px;
        }

        .invoice_body_items table {
            width: 100%;
        }

        .invoice_body_items tr {
            border-bottom: 1px solid grey;
        }

        .invoice_body_items table tr th:nth-child(1) {
            width: 20%;
        }

        .invoice_body_items table tr th:nth-child(2) {
            width: 80%;
        }

        .invoice_body_items div {
            color: white;
            background-color: #365799;
            font-weight: 400;
            font-size: 1.5rem;
            padding-left: 10px;
            padding-right: 10px;
        }

        .invoice_preview_total_price {
            text-align: right;
            font-size: 1.5rem;
            font-weight: bold;
            border-bottom: 1px solid grey;
        }

    </style>
</head>

<body>
    {!! $content !!}
</body>

</html>
