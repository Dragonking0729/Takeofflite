@extends('layouts.app')

@section('title')
{{ trans('titles.question') }}
@endsection

@section('custom_css')
<link rel="stylesheet" href="{{ asset('css/question.css') }}" />
<link rel="stylesheet" href="{{ asset('css/dist/themes/default/style.min.css') }}" />
@endsection

@section('content')
<div class="container-fluid mt-3 mb-5">
    <div class="row">
        <div class="col-sm-12 mx-auto">
            <div class="card">
                <div class="card-header text-center">
                    Interview Questions
                </div>
                @csrf
                <div class="card-body" id="default_section">
                    @include('question.pagination')
                </div>
                @include('question.create-question')
            </div>
        </div>
    </div>
    @include('modals.question.question-tree')
</div>

@endsection


@section('script')
<script src="{{ asset('js/tree-dist/jstree.min.js') }}"></script>
@include('scripts.question.question-js')
@include('scripts.question.question-treejs')
@endsection