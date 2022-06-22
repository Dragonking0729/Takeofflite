<div class="container-fluid mb-5">
    <div class="row" id="sortable">
        @if (count($sheets))
            @foreach ($sheets as $sheet)
                <div class="col-md-2 m-1 px-0 sheet_image" data-order="{{$sheet['id']}}">
                    <input type="checkbox" class="check_sheet" data-sheet_id="{{$sheet['id']}}">
                    <a href="{{asset($sheet->file)}}" data-fancybox="attached_files_list"
                       class="btn btn-link select_sheet" data-caption="{{$sheet->sheet_name}}">
                        <img src="{{asset($sheet->file)}}" alt="picture" class="image" title="{{$sheet->sheet_name}}">
                    </a>

                    <div class="d-flex justify-content-between sheet_title">
                        <div contenteditable data-name="sheet_label" class="text-center m-auto sheet_name_edit"
                             data-id="{{$sheet->id}}">
                            {{$sheet->sheet_name}}
                        </div>
                        <div class="d-flex">
                            <div class="download">
                                <a href="{{asset($sheet->file)}}" class="btn btn-link download_sheet" download
                                   data-id="{{$sheet->id}}" title="Download">
                                    <i class="fa fa-download"></i>
                                </a>
                            </div>
                            <div class="delete">
                                <a href="javascript:void(0)" class="btn btn-link delete_sheet"
                                   data-id="{{$sheet->id}}" title="Delete Picture">
                                    <i class="fa fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center">No pictures</div>
        @endif

        {{-- fixed right tool bar--}}
        @include('partials.sheetlist-toolbar')
    </div>
</div>


@section('script')
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.umd.js"></script>
    @include('scripts.sheetlist.sheetlist-sidebar-js')
    @include('scripts.sheetlist.sheetlist-js')
    @include('scripts.sheetlist.toolbar-js')
    @include('scripts.spreadsheet.measuring-TOQ-js')
@endsection