@extends('layouts.app')

@section('title')
    {{ trans('titles.formula') }}
@endsection

@section('custom_css')
    <link rel="stylesheet" href="{{ asset('css/dist/themes/default/style.min.css') }}"/>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/formula.css?v=1.1') }}"/>
    <link rel="stylesheet" href="{{ asset('css/stored-formula.css') }}"/>
@endsection

@section('content')
    <div class="container-fluid mt-3 mb-5">
        <div class="row">
            <div class="col-sm-12 mx-auto">
                <div class="card">
                    <div class="card-header text-center">
                        Stored Calculations
                    </div>
                    @csrf
                    <div class="card-body" id="default_section">
                        @include('formula.pagination')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('modals.question.create-new-question')
    @include('modals.costitem.save-formula')
    @include('modals.formula.formula-tree')
    @include('modals.costitem.test-formula')

@endsection


@section('script')
    <script src="{{ asset('js/hot-formula-parser/dist/formula-parser.min.js') }}"></script>
    <script src="{{ asset('js/tree-dist/jstree.min.js') }}"></script>
    {{-- create and udpate formula --}}
    @include('scripts.formula.update-formula-js')

    {{-- test formula --}}
    @include('scripts.core-formula-js')
    @include('scripts.formula.test-formula-js')

    @include('scripts.question.new-question-js')

    {{-- stored formula js --}}
    @include('scripts.formula.stored-formula-js')
    @include('scripts.formula.stored-formula-tree-js')
@endsection