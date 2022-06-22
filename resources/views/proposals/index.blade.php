@extends('layouts.app')

@section('title')
    {{ trans('titles.proposals') }}
@endsection

@section('content')
    @include('partials.tabs')
    <div class="d-flex p-1 mb-2">
        @include('proposals.proposal-sidebar')
        @include('proposals.proposals')
    </div>
@endsection