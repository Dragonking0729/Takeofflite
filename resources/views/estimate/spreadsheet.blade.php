<div id="content" class="resize-handle--x pl-2" data-target="aside">
    <main>
        <div id="spreadsheet"></div>
        
        @if ( $existAddonLists && count($existAddonLists) )
            <div id="add_on_area" class="table-responsive">
                <table class="table table-bordered table-sm font-color-gray width-50p text-center">
                    <thead>
                    <tr>
                        <th>Add On</th>
                        <th>Cost Category(s)</th>
                        <th>Value</th>
                        <th>Method</th>
                        <th>Total Amount of Add On</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                        <tbody>
                            @foreach($existAddonLists as $eatl)
                                <tr id='eachaddon{{$eatl->id}}' class="addons-lightblue">
                                    <td>{{$eatl->ss_add_on_name}}</td>
                                    <td>{{$eatl->addon_category}}</td>
                                    <td>{{$eatl->addon_value}}</td>
                                    <td>{{$eatl->addon_method}}</td>
                                    <td>{{$eatl->ss_add_on_value}}</td>
                                    <td class="text-center">
                                        <img src="{{asset('icons/ss_delete.svg')}}" onclick="handleDeleteAddon({{$eatl->id}})" class="width-20px cursor-pointer"
                                                     alt="addon">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                </table>
            </div>
        @else
            <div id="add_on_area" class="table-responsive" style="display:none;">
                <table class="table table-bordered table-sm font-color-gray width-50p text-center">
                    <thead>
                        <tr>
                            <th>Add On</th>
                            <th>Cost Category(s)</th>
                            <th>Value</th>
                            <th>Method</th>
                            <th>Total Amount of Add On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                
                    </tbody>
                </table>
            </div>
        @endif
        
        


        <div id="ss_total_bar" class="sticky-position-bottom text-center"></div>
    </main>
</div>
@include('modals.spreadsheet.interview-ss')
@include('modals.spreadsheet.interview-assem')
@include('modals.spreadsheet.get-price')
@include('modals.spreadsheet.email-pdf')

@section('script')
    {{-- hot-formula-parser library --}}
    <script src="{{ asset('js/hot-formula-parser/dist/formula-parser.min.js') }}"></script>
    <script src="{{ asset('js/tree-dist/jstree.min.js') }}"></script>

    @include('scripts.spreadsheet.spreadsheet-sidebar-js')
    @include('scripts.spreadsheet.spreadsheet-js')

    @include('scripts.core-formula-js')
    @include('scripts.spreadsheet.interview-js')
    @include('scripts.spreadsheet.addon-js')

    {{--<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.4/lodash.min.js"></script>--}}
    {{--@include('scripts.resizable-sidebar-js');--}}
@endsection