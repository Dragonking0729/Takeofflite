@extends('layouts.app')

@section('title')
{{ trans('titles.costgroup') }}
@endsection

@section('custom_css')
<link rel="stylesheet" href="{{ asset('css/costgroup.css') }}" />
<link rel="stylesheet" href="{{ asset('css/dist/themes/default/style.min.css') }}" />
@endsection

@section('content')
<div class="container-fluid mt-3 mb-5">
    <div class="row">
        <div class="col-sm-12 mx-auto">
            <div class="card">
                <div class="card-header text-center">
                    COST GROUPS
                </div>
                @csrf
                <div class="card-body" id="default_section">
                    @include('costgroup.pagination')
                </div>
                @include('costgroup.create-costgroup')
            </div>
        </div>
    </div>
    @include('modals.costgroup.costgroup-treeview')
</div>
@endsection


@section('script')
<script src="{{ asset('js/tree-dist/jstree.min.js') }}"></script>
@include('scripts.costgroup.costgroup-js')
@include('scripts.costgroup.costgroup-treejs')
@endsection