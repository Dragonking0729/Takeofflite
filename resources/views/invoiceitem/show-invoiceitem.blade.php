@extends('layouts.app')

@section('title')
{{ trans('titles.invoiceitem') }}
@endsection

@section('custom_css')
<link rel="stylesheet" href="{{ asset('css/dist/themes/default/style.min.css') }}" />
<link rel="stylesheet" href="{{ asset('css/invoiceitem.css') }}" />
@endsection

@section('content')
<div class="container-fluid mt-3 mb-5">
    <div class="row">
        <div class="col-sm-12 mx-auto">
            <div class="card">
                <div class="card-header text-center">
                    INVOICE ITEMS
                </div>

                @include('invoiceitem.default-invoiceitem')
                @include('invoiceitem.create-invoiceitem')

            </div>
        </div>
    </div>
</div>

@include('modals.invoiceitem.invoiceitem-tree')
@include('modals.invoiceitem.invoiceitem-create-tree')

@endsection



@section('script')

{{-- hot-formula-parser library --}}
<script src="{{ asset('js/hot-formula-parser/dist/formula-parser.min.js') }}"></script>
<script src="{{ asset('js/tree-dist/jstree.min.js') }}"></script>

{{-- cost item script --}}
@include('scripts.invoiceitem.invoiceitem-js')
@include('scripts.invoiceitem.invoiceitem-treejs')
@endsection