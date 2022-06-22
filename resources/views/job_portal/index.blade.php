@extends('job_portal.layout')

@section('title')
    {{ trans('titles.job_portal') }}
@endsection

@section('custom_css')
    <script>document.write("<link rel='stylesheet' href='{{asset("css/job_portal.css")}}?v=" + Date.now() + "'><\/link>");</script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row" style="height: 20vh;">
            <div id="map">
            </div>
            <div id="pano">
            </div>
        </div>
        <div class="tab-content">
            @include('job_portal.job_detail_page')
            @include('job_portal.job_budget_page')
            @include('job_portal.job_deal_analyzer_page')
            @include('job_portal.job_picture_page')
        </div>
        @include('job_portal.footer')
    </div>
@endsection


@section('script')
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    @include('scripts.job_portal.job_portal-js')
    <script
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAvd8WHv_GoEG5eaOCwPLku8JeQzNno7dA&callback=initMap&v=weekly"
            async></script>
@endsection