@extends('layouts.app')

@section('title')
{{ trans('titles.proposalgroup') }}
@endsection

@section('custom_css')
<link rel="stylesheet" href="{{ asset('css/proposal_group.css') }}" />
<link rel="stylesheet" href="{{ asset('css/dist/themes/default/style.min.css') }}" />
@endsection

@section('content')
<div class="container-fluid mt-3 mb-5">
    <div class="row">
        <div class="col-sm-12 mx-auto">
            <div class="card">
                <div class="card-header text-center">
                    PROPOSAL ITEM GROUPS
                </div>
                @csrf
                <div class="card-body" id="default_section">
                    @include('proposal_group.pagination')
                </div>
                @include('proposal_group.create-proposalgroup')
            </div>
        </div>
    </div>
    @include('modals.proposal_group.proposalgroup-treeview')
</div>
@endsection


@section('script')
<script src="{{ asset('js/tree-dist/jstree.min.js') }}"></script>
@include('scripts.proposal_group.proposalgroup-js')
@include('scripts.proposal_group.proposalgroup-treejs')
@endsection