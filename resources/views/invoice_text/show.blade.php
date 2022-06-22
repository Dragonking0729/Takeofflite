@extends('layouts.app')

@section('title')
    {{ trans('titles.invoice_text') }}
@endsection

@section('custom_css')
    <link rel="stylesheet" href="{{ asset('css/invoice_text.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/dist/themes/default/style.min.css') }}"/>

    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>
@endsection

@section('content')
    <div class="container-fluid mt-3 mb-5">
        <div class="row">
            <div class="col-sm-12 mx-auto">
                <div class="card">
                    <div class="card-header text-center">
                        Invoice Text
                    </div>
                    @csrf
                    <div class="card-body" id="default_section">
                        @include('invoice_text.pagination')
                    </div>
                    @include('invoice_text.create')
                </div>
            </div>
        </div>
        @include('modals.invoice_text.invoice-text-tree')
    </div>
@endsection


@section('script')
    <script src="{{ asset('js/tree-dist/jstree.min.js') }}"></script>
    @include('scripts.invoice_text.invoice-text-js')
    @include('scripts.invoice_text.invoice-text-treejs')
@endsection