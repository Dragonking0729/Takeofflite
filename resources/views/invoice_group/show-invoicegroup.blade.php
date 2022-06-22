@extends('layouts.app')

@section('title')
{{ trans('titles.invoicegroup') }}
@endsection

@section('custom_css')
<link rel="stylesheet" href="{{ asset('css/invoice_group.css') }}" />
<link rel="stylesheet" href="{{ asset('css/dist/themes/default/style.min.css') }}" />
@endsection

@section('content')
<div class="container-fluid mt-3 mb-5">
    <div class="row">
        <div class="col-sm-12 mx-auto">
            <div class="card">
                <div class="card-header text-center">
                    INVOICE ITEM GROUPS
                </div>
                @csrf
                <div class="card-body" id="default_section">
                    @include('invoice_group.pagination')
                </div>
                @include('invoice_group.create-invoicegroup')
            </div>
        </div>
    </div>
    @include('modals.invoice_group.invoicegroup-treeview')
</div>
@endsection


@section('script')
<script src="{{ asset('js/tree-dist/jstree.min.js') }}"></script>
@include('scripts.invoice_group.invoicegroup-js')
@include('scripts.invoice_group.invoicegroup-treejs')
@endsection