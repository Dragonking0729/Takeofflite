@extends('layouts.app')

@section('title')
{{ trans('titles.costitem') }}
@endsection

@section('custom_css')
<link rel="stylesheet" href="{{ asset('css/dist/themes/default/style.min.css') }}" />
<link rel="stylesheet" href="{{ asset('css/formula.css?v=1.1') }}" />
<link rel="stylesheet" href="{{ asset('css/costitem.css') }}" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection

@section('content')
<div class="container-fluid mt-3 mb-5">
    <div class="row">
        <div class="col-sm-12 mx-auto">
            <div class="card">
                <div class="card-header text-center">
                    COST ITEMS
                </div>

                @include('costitem.default-costitem')
                @include('costitem.create-costitem')

            </div>
        </div>
    </div>
</div>

@include('modals.costitem.costitem-tree')
@include('modals.question.create-new-question')
@include('modals.costitem.save-formula')
@include('modals.costitem.test-formula')
@include('modals.costitem.costitem-create-tree')

@endsection



@section('script')

{{-- hot-formula-parser library --}}
<script src="{{ asset('js/hot-formula-parser/dist/formula-parser.min.js') }}"></script>
<script src="{{ asset('js/tree-dist/jstree.min.js') }}"></script>

@include('scripts.question.new-question-js')
{{-- create and udpate formula --}}
@include('scripts.costitem.create-formula-js')
@include('scripts.costitem.update-formula-js')

{{-- test formula --}}
@include('scripts.core-formula-js')
@include('scripts.costitem.test-formula-js')

{{-- cost item script --}}
@include('scripts.costitem.costitem-js')
@include('scripts.costitem.costitem-treejs')
@endsection