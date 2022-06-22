@extends('layouts.app')

@section('title')
    {{ trans('titles.assembly') }}
@endsection

@section('custom_css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/dist/themes/default/style.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/formula.css?v=1.1') }}" />
    <link rel="stylesheet" href="{{ asset('css/assembly.css') }}"/>
@endsection

@section('content')
    <div class="container-fluid mt-3 mb-5">
        <div class="row">
            <div class="col-sm-12 mx-auto">
                <div class="card">
                    <div class="card-header text-center">
                        Interview
                    </div>
                    @csrf
                    <div class="card-body" id="default_section">
                        @include('assembly.pagination-assembly')
                    </div>
                </div>
            </div>
        </div>
        @include('modals.assembly.assembly-tree')
        @include('modals.assembly.assembly-costitem-tree')

        @include('modals.assembly.assembly-item-formula')
{{--        @include('modals.assembly.add-assembly-item-formula')--}}
        @include('modals.assembly.assembly-item-formula-test')

        @include('modals.question.create-new-question')
        @include('modals.costitem.save-formula')
    </div>
@endsection


@section('script')
    {{-- hot-formula-parser library --}}
    <script src="{{ asset('js/hot-formula-parser/dist/formula-parser.min.js') }}"></script>
    <script src="{{ asset('js/tree-dist/jstree.min.js') }}"></script>

    @include('scripts.assembly.new-question-js')
    {{-- create and udpate formula --}}
{{--    @include('scripts.assembly.create-assemblyitem-formula-js')--}}
    @include('scripts.assembly.update-assemblyitem-formula-js')

    {{-- test formula --}}
    @include('scripts.core-formula-js')
    @include('scripts.assembly.test-assemblyitem-formula-js')

    {{-- assembly script --}}
    @include('scripts.assembly.assembly-js')
    @include('scripts.assembly.assembly-treejs')
@endsection