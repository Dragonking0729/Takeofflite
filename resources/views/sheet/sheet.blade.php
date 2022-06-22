<div class="container-fluid sheet_div">
    <div class="measurement-top-area">
        <div class="mr-1">
            <button class="btn btn-sm btn-outline-secondary zoomOutBtn"></button>
            <button class="btn btn-sm btn-outline-secondary zoomResetBtn">100%</button>
            <button class="btn btn-sm btn-outline-secondary zoomInBtn"></button>

            <span class="ml-1">
                Scale:&nbsp;<span id="scale_span"></span>
                <span style="color: #006b5c;">You may also use mousewheel to zoom</span>
            </span>
        </div>
        <!-- <div id="current_function_in"></div> -->
    </div>

    <div class="measuring-area">
        <div onselectstart="javascript:/*IE8 hack*/return false" id="gfx_holder" style="width: 100%; height: 100%;overflow: scroll;"></div>
    </div>

</div>

@include('partials.measuring-toolbar')
@include('modals.measurement.create-measurement')
@include('modals.measurement.measurement-scale')
@include('modals.measurement.set-scale')

@section('script')
    @include('scripts.sheetlist.sheet-sidebar-js')
    @include('scripts.sheet-tabs-js')
    @include('scripts.sheetlist.toolbar-js')

    @include('scripts.measurement.measuring-takeofflite-js')
    @include('scripts.spreadsheet.measuring-TOQ-js')

    {{--<script>document.write("<script type='application/javascript' src='{{asset("js/takeofflite.js")}}?v=" + Date.now() + "'><\/script>");</script>--}}
    {{--<script>document.write("<script type='application/javascript' src='{{asset("js/takeofflite-drawing.js")}}?v=" + Date.now() + "'><\/script>");</script>--}}
@endsection