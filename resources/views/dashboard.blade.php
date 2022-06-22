@extends('layouts.app')

@section('title')
    {{ trans('titles.dashboard') }}
@endsection

@section('custom_css')
    <script>document.write("<link rel='stylesheet' href='{{asset("css/dashboard.css")}}?v=" + Date.now() + "'><\/link>");</script>
@endsection

@section('content')

    <div class="container-fluid mt-3 mb-5">
        <div class="row">
            <div class="col-sm-6">
                <div class="card mt-2">
                    <div class="card-header d-flex justify-content-between">
                        <div>
                            MY PROJECTS
                        </div>
                        <div>
                            <button type="button" class="btn btn-light" data-toggle="modal"
                                    data-target="#create_project">
                                <i class="fa fa-plus" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            @if (count($projects))
                                @foreach ($projects as $project)
                                    <div class="list-group-item mb-2 list-group-item-action {{$project['isShared'] ? 'shared_job' : ''}}">
                                        <div class="row">
                                            <a class="btn btn-outline-success card-link"
                                               href="{{ url('/estimate'.'/'.$project['id']) }}"
                                               role="button">
                                                {{--<p class="card-text"></p>--}}
                                                {{ $project['projectName'] }}
                                            </a>
                                        </div>

                                        <div class="row">
                                            <a class="btn btn-light edit-project"
                                               data-toggle="modal" data-target="#edit_project"
                                               data-id="{{ $project['id'] }}">
                                                <img src="{{asset('icons/edit_box.png')}}" class="tkl-icon" alt="Edit">
                                                <span class="dashboard-icon-text">Edit Project</span>
                                            </a>
                                            <a class="btn btn-light get_customer_portal_link"
                                               data-id="{{$project['id']}}">
                                                <img src="{{asset('icons/link.png')}}" class="tkl-icon"
                                                     alt="Portal">
                                                <span class="dashboard-icon-text">Customer Portal Link</span>
                                            </a>
                                            <a class="btn btn-light job_share"
                                               data-id="{{$project['id']}}">
                                                <img src="{{asset('img/noun-share.svg')}}" class="tkl-icon"
                                                     alt="Share">
                                                <span class="dashboard-icon-text">Share</span>
                                            </a>
                                            <a class="btn btn-light">
                                                <img src="{{asset('icons/archive.png')}}" class="tkl-icon"
                                                     alt="Archive">
                                                <span class="dashboard-icon-text">Archive Project</span>
                                            </a>
                                            <a class="btn btn-light delete-project"
                                               data-toggle="modal" data-target="#delete_project"
                                               data-id="{{ $project['id'] }}">
                                                <img src="{{asset('icons/delete.png')}}" class="tkl-icon" alt="Delete">
                                                <span class="dashboard-icon-text">Delete</span>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>


                    </div>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="card mt-2">
                    <div class="card-header d-flex justify-content-between">
                        <div>
                            My Takeoff Lite
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <a class="btn card-link edit_company" data-toggle="modal"
                                   data-target="#edit_company">
                                    <img src="{{asset('icons/about_us.png')}}" class="tkl-icon" alt="Company Info">
                                    Company Info
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{route('membership.index')}}" class="btn card-link">
                                    <img src="{{asset('icons/user.png')}}" class="tkl-icon" alt="Membership">
                                    Membership
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <a class="btn card-link">
                                    <img src="{{asset('icons/team.png')}}" class="tkl-icon" alt="Team members">
                                    Team members
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <a class="btn card-link">
                                    <img src="{{asset('icons/preferences.png')}}" class="tkl-icon" alt="Preferences">
                                    Preferences
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">MY LIBRARIES</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <a href="{{ url('costgroup') }}" class="btn card-link d-flex">
                                    <span class="cost-group-icon"></span>
                                    <div class="my-auto">Cost Groups</div>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ url('question') }}" class="btn card-link d-flex">
                                    <img src="{{asset('icons/questions.png')}}" class="tkl-icon"
                                         alt="Interview Questions">
                                    <div class="my-auto">Interview Questions</div>
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <a href="{{ url('costitem') }}" class="btn card-link d-flex">
                                    <span class="cost-item-icon"></span>
                                    <div class="my-auto">Cost Items</div>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ url('assembly') }}" class="btn card-link d-flex">
                                    <span class="interview-icon"></span>
                                    <div class="my-auto">Interviews</div>
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <a href="{{ url('add_on') }}" class="btn card-link d-flex">
                                    <img src="{{asset('img/noun-add.svg')}}" class="tkl-icon mr-5px"
                                                     alt="addon">
                                    <div class="my-auto">Add Ons</div>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ url('proposal_group') }}" class="btn card-link d-flex">
                                    <span class="cost-group-icon"></span>
                                    <div class="my-auto">Proposal Item Groups</div>
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <a href="{{ url('invoice_group') }}" class="btn card-link d-flex">
                                    <span class="cost-group-icon"></span>
                                    <div class="my-auto">Invoice Item Groups</div>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ url('proposal_item') }}" class="btn card-link d-flex">
                                    <span class="cost-item-icon"></span>
                                    <div class="my-auto">Proposal Items</div>
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <a href="{{ url('invoice_item') }}" class="btn card-link d-flex">
                                    <span class="cost-item-icon"></span>
                                    <div class="my-auto">Invoice Items</div>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ url('proposal_text') }}" class="btn card-link d-flex">
                                    <img src="{{asset('img/noun-text.svg')}}" class="tkl-icon"
                                                     alt="addon">
                                    <div class="my-auto">Proposal Text</div>
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <a href="{{ url('invoice_text') }}" class="btn card-link d-flex">
                                    <img src="{{asset('img/noun-text.svg')}}" class="tkl-icon"
                                                     alt="addon">
                                    <div class="my-auto">Invoice Text</div>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ url('formula') }}" class="btn card-link">
                                    <img src="{{asset('icons/stored_formula.png')}}" class="tkl-icon"
                                         alt="Stored Calculations">
                                    Stored Calculations
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('modals.dashboard.create-project')
    @include('modals.dashboard.edit-project')
    @include('modals.dashboard.delete-project')
    @include('modals.dashboard.create-customer-email')
    @include('modals.dashboard.edit-company')
    @include('modals.dashboard.job-share')

@endsection

@section('script')
    <script src="{{ asset('js/jquery-input-mask-phone-number.min.js') }}"></script>
    @include('scripts.dashboard.dashboard-js')
@endsection