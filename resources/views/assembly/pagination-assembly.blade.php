<div class="row">
    <div class="col-sm-10">
        <div class="form-group row">
            <label for="assembly_number" class="col-sm-2 col-form-label">Interview</label>
            <div class="col-sm-4 my-auto">
                @if ($assemblies->count())
                    <input type="number" class="form-control" id="assembly_number"
                           value="{{ $assemblies[0]->assembly_number }}" disabled>
                @endif
                <input type="number" class="form-control" id="a_assembly_number" placeholder="Enter interview number"
                       style="display: none;">
            </div>
            <div class="col-sm-1">
                <button class="btn btn-outline-secondary" data-toggle="modal" data-target="#assembly_tree_modal"
                        id="open_assembly">
                    <i class="fa fa-bars" aria-hidden="true"></i>
                </button>
            </div>
            <div class="form-check-inline my-auto">
                @if ($assemblies->count())
                    <input class="form-check-input my-auto" type="checkbox"
                           id="folder" {{ $assemblies[0]->is_folder ? 'checked' : '' }}>
                @endif
                <input class="form-check-input my-auto" type="checkbox" id="a_folder" style="display: none;">
                <label for="folder" class="form-check-label">Folder</label>

                @if ($assemblies->count())
                    <input class="form-check-input ml-3 my-auto" type="checkbox"
                           id="is_qv" {{ $assemblies[0]->is_qv ? 'checked' : '' }}
                            {{$assemblies[0]->is_folder ? 'disabled' : ''}}>
                @endif
                <input class="form-check-input ml-3 my-auto" type="checkbox" id="a_qv" style="display: none;">
                <label for="is_qv" class="form-check-label">Is this your Fix and Flip interview?</label>
            </div>
        </div>

        <div class="form-group row" id="desc_line">
            <label for="assembly_desc" class="col-sm-2 col-form-label">Description</label>
            <div class="col-sm-10">
                @if ($assemblies->count())
                    <textarea class="form-control" id="assembly_desc"
                              rows="1">{{ $assemblies[0]->assembly_desc }}</textarea>
                @endif
                <textarea class="form-control" id="a_assembly_desc" rows="1" placeholder="Enther interview description"
                          style="display: none;"></textarea>
            </div>
        </div>

        @if (count($items))
            <div class="form-group row" id="remove_selected_items_div">
                <a class="btn ml-4 remove_selected_items_icon" title="Delete selected items"></a>
            </div>
        @endif

        <div class="form-group row" id="a_remove_selected_items_div" style="display: none;">
            <a class="btn ml-4 a_remove_selected_items_icon" title="Delete selected items"></a>
        </div>

        <div id="asseblyContainer" class="table-responsive-sm" style="height: 200px; overflow: auto;resize: both;">
            <table class="table table-bordered table-hover table-sm" id="items">
                <thead>
                <tr>
                    @if (count($items))
                        <th scope="col" style="width: 40px;">
                            <input type="checkbox" class="select-all-assembly-item">
                        </th>
                    @endif
                    <th scope="col">Cost Group</th>
                    <th scope="col">Item Number</th>
                    <th scope="col">Description</th>
                    <th scope="col" style="width: 200px;">Action</th>
                </tr>
                </thead>
                <tbody id="item_tbl_body" {{count($items)}}>
                @if (count($items))
                    <?php $index = 0; ?>
                    @foreach ($items as $item)
                        <tr data-order="{{$item['id']}}">
                            @if (count($items))
                                <td style="width: 40px;">
                                    <input type="checkbox" class="select-assembly-item" data-id="{{$item['id']}}">
                                </td>
                            @endif
                            <td>{{$item['item_cost_group_number']}}</td>
                            <td>{{$item['item_number']}}</td>
                            <td>{{$item['item_desc']}}</td>
                            <td>
                                <div class="d-flex justify-content-center px-3">
                                    <div>
                                        <a href="javascript:;" class="text-danger mr-3 delete-item"
                                           data-id="{{$item['id']}}">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </div>
                                    <div>
                                        <a title="Open item formula"
                                           class="btn calculator-icon formula_modal_open" data-id="{{$item['id']}}"
                                           data-page_number="{{$assemblies->currentPage()}}" data-next="{{( $index+1 == count($items) ) ? $items[0]['id'] : $items[$index+1]['id'] }}" data-previous="{{( $index == 0 ) ? $items[count($items)-1]['id'] : $items[$index-1]['id'] }}">
                                        </a>
                                        @if ($item['is_formula_exist'])
                                            <a class="btn formula-exist-icon" title="Formula exist"></a>
                                        @endif
                                    </div>
                                    <div>
                                        <a class="btn grab-hand" title="Update order"></a>
                                        <a class="btn up-down-item" title="Update order"></a>
                                    </div>
                                </div>

                            </td>
                        </tr>
                        <?php $index++; ?>
                    @endforeach
                @endif
                </tbody>
            </table>

            <table class="table table-bordered table-hover table-sm" id="a_items" style="display: none;">
                <thead>
                <tr>
                    <th scope="col" style="width: 40px;">
                        <input type="checkbox" class="a_select-all-assembly-item">
                    </th>
                    <th scope="col">Cost Group</th>
                    <th scope="col">Item Number</th>
                    <th scope="col">Description</th>
                    <th scope="col" style="width: 200px;">Action</th>
                </tr>
                </thead>
                <tbody id="a_item_tbl_body">

                </tbody>
            </table>
        </div>
    </div>

    <div class="col-sm-2 d-flex flex-column">
        <div class="btn-group">
            <a href="{{ $assemblies->previousPageUrl() }}"
               class="{{ $assemblies->currentPage() == 1 ? 'btn btn-outline-secondary prev mr-1 disabled' : 'btn btn-outline-secondary prev mr-1' }}"><i
                        class="fa fa-angle-double-left" aria-hidden="true"></i></a>
            <a href="{{ $assemblies->nextPageUrl() }}"
               class="{{ $assemblies->total() == $assemblies->currentPage() ? 'btn btn-outline-secondary next disabled' : 'btn btn-outline-secondary next' }}"><i
                        class="fa fa-angle-double-right" aria-hidden="true"></i></a>
        </div>
        <button type="button" class="btn btn-outline-secondary add_btn mt-2">Add Interview</button>
        @if($assemblies->count())
            <button type="button" class="btn btn-outline-secondary delete mt-2" id="delete"
                    {{ $assemblies->count() ? '' : 'disabled' }}
                    data-id="{{ count($assemblies) ? $assemblies[0]->id : '' }}"
                    data-page="{{$assemblies->currentPage()}}">
                Delete
            </button>
            <button type="button" class="btn btn-outline-secondary renumber mt-2"
                    data-id="{{count($assemblies) ? $assemblies[0]->id : ''}}">Renumber
            </button>
        @endif
        <button type="button" class="btn btn-outline-secondary mt-2" data-toggle="modal"
                data-target="#assembly_costitem_tree_modal"
                id="open_assembly_costitem" {{ count($assemblies) && $assemblies[0]->is_folder ? 'disabled' : '' }}>
            Insert Items
        </button>
    </div>

</div>

<div class="card-footer bg-white border-top-0 text-center">
    <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary" id="back">Close</a>
    <button type="button" class="btn btn-outline-secondary cancel"
            data-id="{{ count($assemblies) ? $assemblies[0]->id : '' }}" style="display: none;">Cancel
    </button>
    <button type="button" class="btn btn-outline-secondary ok" id="add_update"
            data-id="{{count($assemblies) ? $assemblies[0]->id : ''}}"
            data-page="{{$assemblies->currentPage()}}"
            style="display: none;">
        Ok
    </button>
</div>

<script>
    var formulaItemArray = @json($items);
</script>
