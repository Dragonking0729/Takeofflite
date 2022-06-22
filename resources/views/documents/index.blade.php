@extends('layouts.app')

@section('title')
    {{ trans('titles.documents') }}
@endsection

@section('content')
    @include('partials.tabs')
    <div class="d-flex p-1 mb-2">
        @include('partials.sheetlist-sidebar')
        @if ($category == 'plan')
            @include('documents.plan_files')
        @elseif ($category == 'picture')
            @include('documents.picture_files')
        @elseif ($category == 'video')
            @include('documents.video_files')
        @elseif ($category == 'other')
            @include('documents.other_files')
        @endif
    </div>
@endsection