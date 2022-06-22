@section('custom_css')
    <link rel="stylesheet" href="{{ asset('css/toolbar.css?v=1.1') }}"/>
    <link rel='stylesheet' href='{{asset("css/sheetlist.css?v=1.1")}}'/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css"/>
@endsection

<nav id="sidebar" class="border-right border-light mb-3 {{$is_sidebar_open ? '' : 'active'}}">
    @csrf
    <div id="sidebar_sheet">
        <div class="custom-menu">
            <button type="button" id="sidebarCollapse"
                    class="btn btn-outline-secondary {{$is_sidebar_open ? 'open' : ''}}"
                    data-sidebar_status="{{$is_sidebar_open}}">
            </button>
        </div>
        {{-- plan files --}}
        <a href="{{$category == 'plan' ? '#sheet_sidebar' : url('documents/'.$page_info['project_id'].'/plan')}}"
           class="d-flex document_left_button {{$category == 'plan' ? 'dropdown-toggle left_selected collapsed' : ''}}"
           data-toggle="{{$category == 'plan' ? 'collapse aria-expanded="false"' : ''}}">
            <span class="plan-icon"></span>
            <span class="my-auto ml-1">PLANS</span>
        </a>
        <ul class="list-unstyled collapse pl-1 components" id="sheet_sidebar">
            @if (count($sheet_object_list))
                @foreach($sheet_object_list as $sheet_group)
                    @if (count($sheet_group))
                        {{-- sheet --}}
                        <li>
                            <a href="#{{$sheet_group['id']}}" data-toggle="collapse" aria-expanded="false"
                               class="d-flex pl-3 dropdown-toggle collapsed">
                                <span class="sheet-folder-icon mr-3"></span>
                                <span id="sheet_group_txt_{{$sheet_group['id']}}">{{$sheet_group['text']}}</span>
                            </a>
                            {{-- measurement --}}
                            <ul class="list-unstyled pl-4 pt-2 collapse" id="{{$sheet_group['id']}}">
                                @if (count($sheet_group['measurements']))
                                    @foreach($sheet_group['measurements'] as $measurement)
                                        <li>
                                            <a href="#{{$measurement['id']}}" data-toggle="collapse"
                                               aria-expanded="false" class="dropdown-toggle collapsed">
                                                <i class="fa fa-compass"></i> {{$measurement['text']}}
                                            </a>
                                            {{-- segments --}}
                                            <ul class="list-unstyled pl-2 collapse" id="{{$measurement['id']}}"
                                                style="">
                                                @if (count($measurement['segments']))
                                                    @foreach($measurement['segments'] as $segment)
                                                        <li id="{{$segment['id']}}" class="pt-2">
                                                            <a href="{{ url('/sheet'.'/'.$page_info['project_id'].'/'.$segment['sheet_id'].'/'.$segment['id']) }}">
                                                                <i class="fa fa-eye eye"
                                                                   data-id="{{$segment['id']}}"></i>
                                                                {{$segment['text']}}
                                                            </a>
                                                            <br>
                                                            <div class="text-info measurement_info">
                                                                @if($segment['perimeter'])
                                                                    <span>
                                                                    @if ($segment['text'] === 'Area')
                                                                            Perimeter: {{$segment['perimeter']}}
                                                                        @elseif ($segment['text'] === 'Line')
                                                                            Length: {{$segment['perimeter']}}
                                                                        @endif
                                                                </span>
                                                                @endif
                                                                @if($segment['area'])
                                                                    <span>
                                                                        Area: {{$segment['area']}}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                @endif
                                            </ul>
                                            {{-- segements end --}}
                                        </li>
                                    @endforeach
                                @endif
                            </ul>
                            {{-- measurement end --}}
                        </li>
                        {{-- sheet end --}}
                    @endif
                @endforeach
            @endif
        </ul>

        {{-- pictures --}}
        <a href="{{url('documents/'.$page_info['project_id'].'/picture')}}"
           class="d-flex document_left_button {{$category == 'picture' ? 'left_selected' : ''}}">
            <span class="picture-icon"></span>
            <span class="my-auto ml-1">PICTURES</span>
        </a>

        {{-- videos --}}
        <a href="{{url('documents/'.$page_info['project_id'].'/video')}}"
           class="d-flex document_left_button {{$category == 'video' ? 'left_selected' : ''}}">
            <span class="videos-icon"></span>
            <span class="my-auto ml-1">VIDEOS</span>
        </a>

        {{-- others files --}}
        <a href="{{url('documents/'.$page_info['project_id'].'/other')}}"
           class="d-flex document_left_button {{$category == 'other' ? 'left_selected' : ''}}">
            <span class="other-files-icon"></span>
            <span class="my-auto ml-1">OTHER FILES</span>
        </a>

    </div>
</nav>