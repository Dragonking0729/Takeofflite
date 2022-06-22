@extends('layouts.app')

@section('title')
    {{ trans('titles.membership') }}
@endsection

@section('custom_css')
    <link rel="stylesheet" href="{{ asset('css/membership.css') }}"/>
@endsection

@section('content')
    <div class="container-fluid mt-3 mb-5">
        <div class="row">
            <div class="col-sm-12 mx-auto">
                <div class="card">
                    <div class="card-header text-center">
                        Membership
                    </div>
                    <div class="card-body">
                        @if (session('error'))
                            <div class="alert alert-danger alert-block">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                <strong>
                                    {{ session('error') }}
                                </strong>
                            </div>
                        @endif
                        @if (session('success'))
                            <div class="alert alert-success alert-block">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                <strong>
                                    {{ session('success') }}
                                </strong>
                            </div>
                        @endif

                        <div class="alert alert-info">
                            <strong>Terms:</strong> No charge for the first 30 days. You may cancel before the 30 days
                            to avoid any charges. Once subscription is started you may cancel anytime. Upon
                            cancellation, you will have access until the end of you current billing cycle.
                        </div>

                        <div class="row justify-content-md-center">
                            <div class="col-md-6">
                                <div class="plan-item" style="border: 1px solid #006b5c;">
                                    <div class="plan-item__name-bg theme-bg"></div>
                                    <div class="plan-item__name" style="font-size: 20px;">ESTIMATING + TAKEOFF +
                                        PROPOSALS +
                                        INVOICING*
                                    </div>
                                    <div class="plan-item__price"
                                         style="display: flex; justify-content: space-evenly; background: #e2e2e2;">
                                        <div>
                                            <span class="plan-item__sign">$</span>
                                            <span class="plan-item__currency">30</span>
                                            <span class="plan-item__month">/MONTH</span>
                                        </div>
                                        <div>
                                            <span class="plan-item__sign">$</span>
                                            <span class="plan-item__currency">299</span>
                                            <span class="plan-item__month">/YEAR</span>
                                        </div>
                                    </div>

                                    <ul class="plan-item__features">
                                        <li class="plan-item__features-item">FIRST 30 DAYS FREE, CANCEL ANYTIME</li>
                                        <li class="plan-item__features-item">Cloud-based estimate spreadsheet.</li>
                                        <li class="plan-item__features-item">Share jobs with others on any device</li>
                                        <li class="plan-item__features-item">Lightning fast interview takeoff</li>
                                        <li class="plan-item__features-item">Retrieve big-box vendor material prices
                                            real-time
                                        </li>
                                        <li class="plan-item__features-item">Proposals and Invoicing
                                        <li>
                                        <li class="plan-item__features-item">Give clients the option of paying by credit
                                            card
                                        </li>
                                        <li class="plan-item__features-item">*PROPOSALS AND INVOICING AVAILABLE
                                            10/15/21
                                        </li>
                                    </ul>
                                    <div class="plan__button-wrapper">
                                        <form method="POST" action="{{route('membership.manage_billing')}}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success">Manage billing
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection


@section('script')
    @include('scripts.membership.membership-js')
@endsection