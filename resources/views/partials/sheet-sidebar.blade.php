@section('custom_css')
    <link rel="stylesheet" href="{{ asset('css/toolbar.css') }}"/>
    <link rel='stylesheet' href='{{asset("css/sheet_sidebar.css")}}'/>
    <link rel='stylesheet' href='{{asset("css/sheet.css")}}'/>
    <script>
        document.write("<link rel='stylesheet' href='{{asset("css/measurement.css")}}?v=" + Date.now() + "'><\/link>");
    </script>
@endsection

<nav id="sidebar" class="border-right border-light {{$is_sidebar_open ? '' : 'active'}}">
    @csrf
    <div class="sheet-sidebar">
        <div class="custom-menu">
            <button type="button" id="sidebarCollapse"
                    class="btn btn-outline-secondary {{$is_sidebar_open ? 'open' : ''}}"
                    data-sidebar_status="{{$is_sidebar_open}}">
            </button>
        </div>
        <ul class="list-unstyled pl-1 pb-3 components" id="sheet_sidebar">
            <li>
                <div class="d-flex justify-content-between" id="sheet_level">
                    <a href="#measurement_list" data-toggle="collapse" aria-expanded="true"
                       class="d-flex pl-1 dropdown-toggle collapsed">
                        <span class="sheet-folder-icon mr-3"></span> <span
                                id="sidebar_sheet_name">{{$sheet->sheet_name}}</span>
                    </a>
                    <form id="remove_measurement_objects_form" action="{{url('sheet/remove_all_segments')}}"
                          method="POST" class="pr-3">
                        @csrf
                        <input type="hidden" name="sheet_id" value="{{$sheet->id}}">
                        <i class="remove-measurements-icons" id="remove_measurement_objects"></i>
                    </form>
                </div>

                <ul class="list-unstyled collapse show" id="measurement_list">
                    @if(count($sheet_object_list))
                        @foreach($sheet_object_list as $item)
                            <li>
                                <i class="fa fa-eye measurement-eye" data-id="{{$item['segment_ids']}}"
                                   id="total_segment_ids_{{$item['id']}}"></i>
                                <a href="#{{$item['id']}}" data-toggle="collapse" aria-expanded="true"
                                   class="dropdown-toggle">
                                    {{--<a href="javascript:" data-toggle="collapse" aria-expanded="true" class="dropdown-toggle">--}}
                                    {{$item['text']}}
                                </a>
                                {{-- show total measured amount--}}
                                <div class="total_measurement_info" id="info_{{$item['id']}}">
                                    @if($item['formated_total_perimeter'])
                                        <span onclick="getTOQByMeasurement(this, 'line')"
                                              data-selected_ids="{{$item['segment_ids']}}"
                                              data-length="{{$item['total_perimeter']}}"
                                              id="total_length_toq_{{$item['id']}}">
                                            @if($item['formated_total_area'])
                                                Total Perimeter:
                                            @else
                                                Total Length:
                                            @endif
                                            <span id="total_length_span_{{$item['id']}}">
                                                {{$item['formated_total_perimeter']}}
                                            </span>
                                        </span>
                                    @endif
                                    @if($item['formated_total_area'])
                                        <span onclick="getTOQByMeasurement(this, 'area')"
                                              data-selected_ids="{{$item['segment_ids']}}"
                                              data-area="{{$item['total_area']}}"
                                              id="total_area_toq_{{$item['id']}}">
                                        Total Area: <span id="total_area_span_{{$item['id']}}">
                                            {{$item['formated_total_area']}}
                                            </span>
                                        </span>
                                    @endif
                                    @if($item['total_count'])
                                        <span onclick="getTOQByMeasurement(this, 'point')"
                                              data-selected_ids='{{$item['segment_ids']}}'
                                              data-count="{{$item['total_count']}}"
                                              id="total_count_toq_{{$item['id']}}">
                                    Total Count: <span id="total_count_span_{{$item['id']}}">
                                        {{$item['total_count']}}
                                        </span>
                                    </span>
                                    @endif
                                </div>
                                <ul class="list-unstyled collapse show" id="{{$item['id']}}" style="">
                                    @if (count($item['segments']))
                                        @foreach($item['segments'] as $segment)
                                            <li id="{{$segment['id']}}">
                                                <a href="javascript:">
                                                    <i class="fa fa-eye eye" data-id="{{$segment['id']}}"></i>
                                                    <span onclick="selectSegment('{{$segment['id']}}')">
                                                    {{$segment['text']}}
                                                </span>
                                                </a>
                                                <br>
                                                <div class="measurement_info" id="info_{{$segment['id']}}">
                                                    @if($segment['perimeter'])
                                                        <span onclick="getTOQ(this, '{{$segment['id']}}')"
                                                              data-length="{{$segment['perimeter_val']}}"
                                                              id="length_toq_{{$segment['id']}}">
                                                        @if ($segment['text'] === 'Area')
                                                                Perimeter:
                                                            @elseif ($segment['text'] === 'Line')
                                                                Length:
                                                            @endif
                                                            <span id="length_span_{{$segment['id']}}">
                                                            {{$segment['perimeter']}}
                                                        </span>
                                                    </span>
                                                    @endif
                                                    @if($segment['area'])
                                                        <span onclick="getTOQ(this, '{{$segment['id']}}')"
                                                              data-area="{{$segment['area_val']}}"
                                                              id="area_toq_{{$segment['id']}}">
                                                        Area: <span id="area_span_{{$segment['id']}}">
                                                            {{$segment['area']}}
                                                        </span>
                                                    </span>
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    @endif
                                </ul>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </li>
        </ul>
    </div>
</nav>