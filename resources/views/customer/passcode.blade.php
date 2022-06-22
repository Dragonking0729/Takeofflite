<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">

    <title>Customer Passcode | Takeofflite</title>

    <style>
        body {
            background: #007bff;
            background: linear-gradient(to right, #d0d1d2, #efefef);
        }

        .btn-login {
            background-color: #365799;
            font-size: 0.9rem;
            letter-spacing: 0.05rem;
            padding: 0.75rem 1rem;
        }

    </style>
</head>

<body>
<div class="container" style="margin-top: 25vh;">
    <div class="row">
        <div class="col-sm-9 col-md-7 col-lg-5 mx-auto">
            <div class="card border-0 shadow rounded-3 my-5">
                <div class="card-body p-4 p-sm-5">
                    <div style="text-align: center; margin-bottom: 40px;">
                        <img src="{{asset('img/logo.jpg')}}" alt="TKL logo" width="60%">
                    </div>

                    <h4 class="card-title text-center mb-5 fw-light fs-5" style="color: #006b5c;">CUSTOMER PORTAL</h4>

                    @if ($message = Session::get('success'))
                        <div class="alert alert-success alert-block">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <strong>{{ $message }}</strong>
                        </div>
                    @endif

                    @if ($message = Session::get('error'))
                        <div class="alert alert-danger alert-block">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                            <strong>{{ $message }}</strong>
                        </div>
                    @endif

                    <form action="{{ route('customer.check_passcode') }}" method="POST">
                        @csrf
                        <div class="form-floating mb-3">
                            <label for="passcode">Passcode</label>
                            <input type="password" class="form-control" id="passcode" name="passcode"
                                   placeholder="Passcode" required>
                        </div>
                        <div class="d-flex justify-content-center">
                            <button class="btn btn-primary btn-login text-uppercase fw-bold" type="submit">Sign
                                in</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>

</html>
