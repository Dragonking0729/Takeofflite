@extends('layouts.app')

@section('title')
    {{ trans('titles.404') }}
@endsection

@section('custom_css')
    <link rel="stylesheet" href="{{ asset('css/errors.css') }}" />
@endsection

@section('content')
    <div id="notfound">
        <div class="notfound">
            <div class="notfound-404"></div>
            <h1>404</h1>
            <h2>Oops! Page Not Be Found</h2>
            <p>Sorry but the page you are looking for does not exist, have been removed. name changed or is temporarily unavailable</p>
            <a href="{{url('dashboard')}}">Back to homepage</a>
        </div>
    </div>

@endsection



@section('script')
@endsection