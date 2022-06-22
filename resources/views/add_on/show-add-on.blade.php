@extends('layouts.app')

@section('title')
    {{ trans('titles.addon') }}
@endsection

@section('custom_css')
    <link rel="stylesheet" href="{{ asset('css/add_on.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/dist/themes/default/style.min.css') }}"/>
@endsection

@section('content')
    <div class="container-fluid mt-3 mb-5">
        <div class="row">
            <div class="col-sm-12 mx-auto">
                <div class="card">
                    <div class="card-header text-center">
                        Add Ons
                    </div>
                    @csrf
                    <div class="card-body" id="default_section">
                        @include('add_on.pagination')
                    </div>
                    @include('add_on.create-add-on')
                </div>
            </div>
        </div>
        @include('modals.add_on.add-on-treeview')
    </div>

@endsection


@section('script')
    <script src="{{ asset('js/tree-dist/jstree.min.js') }}"></script>
    @include('scripts.add_on.add-on-js')
    @include('scripts.add_on.add-on-treejs')
@endsection