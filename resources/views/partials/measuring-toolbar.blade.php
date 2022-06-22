<nav id="drawing-toolbar" class="active">
    <ul class="list-unstyled components mb-0">
        {{-- select single, multiple object --}}
        {{--<li>--}}
        {{--<span>--}}
        {{--<span class="fas fa-mouse-pointer" title="Select single object"></span>--}}
        {{--</span>--}}
        {{--</li>--}}
        {{--<li>--}}
        {{--<span>--}}
        {{--<span class="fas fa-object-group" title="Select multiple object"></span>--}}
        {{--</span>--}}
        {{--</li>--}}
        {{--<li>--}}
        {{--<span>--}}
        {{--<span class="fas fa-vector-square" title="Polygon"></span>--}}
        {{--</span>--}}
        {{--</li>--}}
        <li class="move_toolbar"></li>
        {{-- Set scale --}}
        <li>
            <span class="scale-icon scale" title="Set scale" onclick="openSetScaleModal();"></span>
        </li>
        {{-- draw count, single line, polyline, poligon, area --}}
        <li>
            <span class="area-icon area" title="Area" onclick="createSegment('area')"></span>
        </li>
        <li>
            <span class="polyline-icon polyline" title="Poly line" onclick="createSegment('polyline')"></span>
        </li>
        <li>
            <span class="line-icon line" title="Single line" onclick="createSegment('line');"></span>
        </li>
        <li>
            <span class="point-icon point" title="Count" onclick="createSegment('point');"></span>
        </li>
        {{-- Exit measuring --}}
        <li>
            <span class="stop-measuring-icon stop-measuring" title="Stop measuring" onclick="exitMeasuring();"></span>
        </li>
        {{-- Abort measuring --}}
        <li>
            <span class="abort-measuring-icon abort-measuring" title="Abort measuring"
                  onclick="abortMeasuring();"></span>
        </li>
        {{-- Remove object --}}
        <li>
            <span class="trash-icon" title="Remove selected objects" onclick="removeObjects();"></span>
        </li>
        {{-- cut out --}}
        {{--        <li>--}}
        {{--            <span class="cutout-icon cutout" title="Cut out" onclick="createSegment('cutout')"></span>--}}
        {{--        </li>--}}
        {{-- rotate, undo, redo --}}
        {{--        <li>--}}
        {{--            <span class="rotate-icon rotate" title="Rotate object 90&deg;" onclick="createSegment('rotate')"></span>--}}
        {{--        </li>--}}
        {{--<li>--}}
        {{--<span>--}}
        {{--<span class="fas fa-undo" title="Undo"></span>--}}
        {{--</span>--}}
        {{--</li>--}}
        {{--<li>--}}
        {{--<span>--}}
        {{--<span class="fas fa-redo" title="Redo"></span>--}}
        {{--</span>--}}
        {{--</li>--}}

    </ul>
</nav>