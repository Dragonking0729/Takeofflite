@extends('layouts.app')

@section('title')
    {{ trans('titles.proposal_text') }}
@endsection

@section('custom_css')
    <link rel="stylesheet" href="{{ asset('css/proposal_text.css') }}"/>
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
                        Proposal Text
                    </div>
                    @csrf
                    <div class="card-body" id="default_section">
                        @include('proposal_text.pagination')
                    </div>
                    @include('proposal_text.create')
                </div>
            </div>
        </div>
        @include('modals.proposal_text.proposal-text-tree')
    </div>
@endsection


@section('script')
    <script src="{{ asset('js/tree-dist/jstree.min.js') }}"></script>
    @include('scripts.proposal_text.proposal-text-js')
    @include('scripts.proposal_text.proposal-text-treejs')
@endsection