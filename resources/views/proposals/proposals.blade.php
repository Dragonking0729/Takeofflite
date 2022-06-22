<div class="container-fluid">
    {{--proposal toolbar--}}
    <div class="row proposal_toolbar">
        <div class="col-md-5 pl-0 align-items-center toolbar_icons mt-15px">
            <button type="button" class="btn btn-sm btn-link mr-2 select_all" data-checked="0"
                    title="Select All" {{count($proposal_list) ? '' : 'disabled'}}>
            </button>
            <button type="button" class="btn btn-sm btn-link mr-2 create_new_proposal" data-checked="0"
                    title="Create new proposal">
            </button>
            <button type="button" class="btn btn-sm btn-link mr-2 proposal_email"
                    title="Send email" {{count($proposal_list) ? '' : 'disabled'}}>
            </button>
            <button type="button" class="btn btn-sm btn-link mr-2 print_proposal"
                    title="Print" {{count($proposal_list) ? '' : 'disabled'}}>
            </button>
            <button type="button" class="btn btn-sm btn-link mr-2 delete_proposal disabled"
                    {{count($proposal_list) ? '' : 'disabled'}}
                    title="Delete selected lines">
            </button>
            <button type="button" class="btn btn-sm btn-link mr-2 preview_proposal"
                    title="Proposal preview" {{count($proposal_list) ? '' : 'disabled'}}>
            </button>
            <button type="button" class="btn btn-sm btn-link mr-2 view_proposal"
                    title="Exit preview mode" {{count($proposal_list) ? '' : 'disabled'}}>
            </button>
            <button type="button" class="btn btn-sm btn-link mr-2 unlock_preview" title="Lock proposal"
                    data-id="{{$page_info['proposal_id']}}" {{count($proposal_list) ? '' : 'disabled'}}>
            </button>
            <button type="button" class="btn btn-sm btn-link mr-2 lock_preview" title="Unlock proposal"
                    {{count($proposal_list) ? '' : 'disabled'}}
                    data-view="{{count($proposal_list) ? $proposal_list[0]->is_locked : ''}}"
                    data-id="{{$page_info['proposal_id']}}">
            </button>
            <button type="button" class="btn btn-sm btn-link mr-2 show_hide_fields"
                    title="Show/hide line fields" {{count($proposal_list) ? '' : 'disabled'}}>
            </button>
            <button type="button"
                    class="btn btn-sm btn-link mr-2 remove_proposal"
                    title="Delete proposal" {{count($proposal_list) ? '' : 'disabled'}}
                    data-id="{{$page_info['proposal_id']}}">
            </button>
            @if ($proposal_texts)
                <select class="proposal_status_dropdown {{$proposal_texts->approve_status == 'Not Sent' ? 'not_sent' : ($proposal_texts->approve_status == 'Approved' ? 'approved' : 'pending')}}">
                    <option value="Not Sent" {{$proposal_texts->approve_status == 'Not Sent' ? 'selected' : ''}}>
                        Not Sent
                    </option>
                    <option value="Pending Approval" {{$proposal_texts->approve_status == 'Pending Approval' ? 'selected' : ''}}>
                        Pending Approval
                    </option>
                    <option value="Approved" {{$proposal_texts->approve_status == 'Approved' ? 'selected' : ''}}>
                        Approved
                    </option>
                </select>
            @else
                <select class="proposal_status_dropdown not_sent" {{count($proposal_list) ? '' : 'disabled'}}>
                    <option value="Not Sent">
                        Not Sent
                    </option>
                    <option value="Pending Approval">
                        Pending Approval
                    </option>
                    <option value="Approved">
                        Approved
                    </option>
                </select>
            @endif
        </div>
        <select class="col-md-1 select_proposal">
            @if (count($proposal_list))
                @foreach($proposal_list as $option)
                    <option value="{{$option->id}}" {{$page_info['proposal_id'] == $option->id ? 'selected' : ''}}>
                        {{$option->proposal_name}} #{{$option->id}}
                    </option>
                @endforeach
            @endif
        </select>
        <div class="col-md-5 total_values">
            <div class="contract_total_cost_div">
                Contract Cost Total: $ <span
                        class="contract_total_cost">{{count($proposal_total) ? $proposal_total[0]->contractor_cost_total : 0}}</span>
            </div>
            <div class="markup_total_div">
                Markup Total: $ <span
                        class="markup_total">{{count($proposal_total) ? $proposal_total[0]->markup_total : 0}}</span>
            </div>
            <div class="customer_price_div">
                Customer Price: $ <span
                        class="customer_price">{{count($proposal_total) ? $proposal_total[0]->customer_price : 0}}</span>
            </div>
        </div>
        <div class="col-md-1 proposal_date">
            09/30/21
        </div>
    </div>

    {{-- show/hide detail --}}
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="show_hide_block" style="display: {{count($proposal_list) ? '' : 'none'}}">
                <div>
                    <label for="show_detail_number">Show detail numbers?</label>
                    <input type="checkbox" name="show_detail_number" id="show_detail_number">
                </div>
                <div>
                    <label for="document_text_top_dropdown">Top text to use:</label>
                    <select class="ml-3 document_text_dropdown" id="document_text_top_dropdown">
                        @if (count($document_text_list))
                            <option value=""></option>
                            @foreach($document_text_list as $option)
                                <option value="{{$option->id}}">
                                    {{$option->title}}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div>
                    <label for="document_text_bottom_dropdown">Bottom text to use:</label>
                    <select class="ml-3 document_text_dropdown" id="document_text_bottom_dropdown">
                        @if (count($document_text_list))
                            <option value=""></option>
                            @foreach($document_text_list as $option)
                                <option value="{{$option->id}}">
                                    {{$option->title}}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div>
                    <label for="include_approval_block">Include approval block at bottom?</label>
                    <input type="checkbox" name="include_approval_block" id="include_approval_block">
                </div>
            </div>
        </div>
    </div>

    {{-- top info --}}
    <div class="row mt-3" id="proposal_top_info_block" style="display: {{count($proposal_list) ? '' : 'none'}}">
        <div class="col-md-12">
            <div id="proposal_top_info"
                 class="summernote">{!!$proposal_texts ? $proposal_texts->top_info_text : ''!!}</div>
        </div>
    </div>

    {{--proposal items--}}
    <div class="row mt-1" id="proposal_items_block">
        <div class="col-md-12" id="proposal_items">
            @foreach($proposal_lines as $index => $proposal_line)
                <div class="proposal_item"
                     data-order="{{$proposal_line->id}}">
                    <div class="proposal_item_left">
                        <button type="button" class="btn select_proposal_item"
                                data-id="{{$proposal_line->id}}" data-checked="0"></button>
                        <span class="proposal_item_circle"></span>
                        <span class="proposal_detail_open_close" data-open="0"></span>
                    </div>
                    <div class="proposal_item_right">
                        <div class="proposal_item_right_top">
                            <div class="item">
                                <label for="proposal_item__{{$proposal_line->id}}">Proposal Item</label>
                                <input type="text" class="form-control proposal_item_number_description"
                                       id="proposal_item__{{$proposal_line->id}}"
                                       data-id="{{$proposal_line->id}}" disabled
                                       value="{{$proposal_line->proposal_item_number.' '.$proposal_line->proposal_item_description}}">
                            </div>
                            <div class="proposal_qty_uom_price_cost">
                                <div class="proposal_qty">
                                    <label for="proposal_quantity__{{$proposal_line->id}}">Quantity</label>
                                    <input type="text" class="form-control proposal_item_billing_quantity"
                                           data-id="{{$proposal_line->id}}"
                                           id="proposal_quantity__{{$proposal_line->id}}"
                                           value="{{$proposal_line->proposal_item_billing_quantity}}">
                                </div>
                                <div class="proposal_uom">
                                    <label for="proposal_uom__{{$proposal_line->id}}">UOM</label>
                                    <select class="form-control proposal_item_uom"
                                            id="proposal_uom__{{$proposal_line->id}}"
                                            data-id="{{$proposal_line->id}}">
                                        @foreach($uom as $option)
                                            <option value="{{$option}}" {{$option === $proposal_line->proposal_item_uom ? 'selected' : ''}}>
                                                {{$option}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="proposal_price">
                                    <label for="proposal_price__{{$proposal_line->id}}">Unit Price</label>
                                    <input type="text" class="form-control proposal_unit_price"
                                           id="proposal_price__{{$proposal_line->id}}"
                                           value="{{$proposal_line->proposal_unit_price}}"
                                           data-id="{{$proposal_line->id}}">
                                </div>
                                <div class="proposal_contract_cost">
                                    <label for="proposal_contract_cost__{{$proposal_line->id}}">Contractor Cost</label>
                                    <input type="text" class="form-control proposal_contractor_cost_total"
                                           id="proposal_contract_cost__{{$proposal_line->id}}"
                                           data-id="{{$proposal_line->id}}"
                                           value="{{$proposal_line->proposal_contractor_cost_total}}">
                                </div>
                            </div>
                            <div class="proposal_markup">
                                <div>
                                    <label for="proposal_markup__{{$proposal_line->id}}">Markup Percent</label>
                                    <input type="text" class="form-control proposal_item_markup_percent"
                                           id="proposal_markup__{{$proposal_line->id}}"
                                           data-id="{{$proposal_line->id}}"
                                           value="{{$proposal_line->proposal_item_markup_percent}}">
                                </div>
                                <div>
                                    <label for="proposal_markup_dollars__{{$proposal_line->id}}">Markup Dollars</label>
                                    <input type="text" class="form-control proposal_markup_dollars"
                                           id="proposal_markup_dollars__{{$proposal_line->id}}"
                                           data-id="{{$proposal_line->id}}"
                                           value="{{$proposal_line->proposal_markup_dollars}}">
                                </div>
                            </div>
                            <div class="proposal_customer">
                                <div>
                                    <label for="proposal_customer_price_per_unit__{{$proposal_line->id}}">Customer Price
                                        Per Unit</label>
                                    <input type="text" class="form-control proposal_customer_price_per_unit"
                                           id="proposal_customer_price_per_unit__{{$proposal_line->id}}"
                                           data-id="{{$proposal_line->id}}"
                                           value="{{$proposal_line->proposal_customer_price_per_unit}}">
                                </div>
                                <div>
                                    <label for="proposal_customer_price__{{$proposal_line->id}}">Customer Total
                                        Price</label>
                                    <input type="text" class="form-control proposal_customer_price"
                                           data-id="{{$proposal_line->id}}"
                                           id="proposal_customer_price__{{$proposal_line->id}}"
                                           value="{{$proposal_line->proposal_customer_price}}">
                                </div>
                            </div>
                        </div>

                        <div class="proposal_item_right_bottom">
                            <div class="proposal_textarea">
                                <div class="proposal_title">
                                    <textarea class="form-control proposal_title_text" rows="2"
                                              placeholder="Enter a long title if needed - NOT SHOWN ON PROPOSAL"></textarea>
                                </div>
                                <div class="proposal_toilet">
                                    <textarea class="form-control proposal_customer_scope_explanation" rows="2"
                                              data-id="{{$proposal_line->id}}"
                                              id="proposal_customer_scope_explanation__{{$proposal_line->id}}"
                                              placeholder="Enter a detailed description - THIS IS SHOWN ON PROPOSAL">{{trim($proposal_line->proposal_customer_scope_explanation)}}</textarea>
                                </div>
                                <div class="proposal_notes">
                                    <textarea class="form-control proposal_internal_notes" rows="2"
                                              data-id="{{$proposal_line->id}}"
                                              id="proposal_note__{{$proposal_line->id}}"
                                              placeholder="Notes">{{trim($proposal_line->proposal_internal_notes)}}</textarea>
                                </div>
                                <button type="button" class="btn btn-sm btn-link mr-2 remove_single_proposal_line"
                                        data-id="{{$proposal_line->id}}" title="Remove this proposal">
                                </button>
                                <span class="proposal_attach"></span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>

    {{-- bottom info --}}
    <div class="row mt-3" id="proposal_bottom_info_block" style="display: {{count($proposal_list) ? '' : 'none'}}">
        <div class="col-md-12">
            <div id="proposal_bottom_info"
                 class="summernote">{!! $proposal_texts ? $proposal_texts->bottom_info_text: '' !!}</div>
        </div>
    </div>

    {{--proposal preview--}}
    <div class="row mt-1" id="proposal_preview_block">
        <div class="col-md-12" id="proposal_preview_content">
            <div class="proposal_header">
                <div class="d-flex justify-content-between my-3 company_logo_info">
                    <div class="company_logo">
                        <img src="{{asset($company_info->company_logo)}}" width="20%" alt="company logo">
                        <div class="customer_name">{{$project_info ? $project_info->customer_name : ''}}</div>
                        <div class="customer_phone">{{$project_info ? $project_info->customer_phone : ''}}</div>
                    </div>
                    <div>
                        <div>{{$company_info ? $company_info->company_url : ''}}</div>
                        <div>
                            {{$company_info ? $company_info->city : ''}}
                            {{$company_info ? ', '.$company_info->state : ''}}
                            {{$company_info ? $company_info->postal_code : ''}}
                        </div>
                        <div>Phone: {{$company_info ? $company_info->phone : ''}}</div>
                    </div>
                </div>

                <div class="d-flex justify-content-between my-3 job_info">
                    <div class="job_address">
                        <div>{{$project_info ? $project_info->street_address_1.' '.$project_info->street_address_2 : ''}}</div>
                        <div>
                            {{$project_info ? $project_info->city : ''}}
                            {{$project_info ? ', '.$project_info->state : ''}}
                            {{$project_info ? $project_info->postal_code : ''}}
                        </div>
                    </div>
                    <div>
                        <div>Job Address:</div>
                        <div>{{$project_info ? $project_info->street_address_1.' '.$project_info->street_address_2 : ''}}</div>
                        <div>
                            {{$project_info ? $project_info->city : ''}}
                            {{$project_info ? ', '.$project_info->state : ''}}
                            {{$project_info ? $project_info->postal_code : ''}}
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="my-3" id="proposal_preview_top_info">

            </div>

            <hr>

            <div class="proposal_body_items">
                <div>
                    Scope of Work Description
                </div>
                <table>
                    <thead>
                    <tr>
                        <th>Proposal Item</th>
                        <th>Description</th>
                    </tr>
                    </thead>
                    <tbody id="proposal_preview_body">

                    </tbody>
                </table>
            </div>

            <hr>

            <div class="my-3" id="proposal_preview_bottom_info">

            </div>

            <hr>

            <div class="my-3" id="proposal_preview_total_price">
                <div class="proposal_preview_total_price">
                    Total Price: $<span></span>
                </div>
                <div>
                    I confirm that my action here represents my electronic signature and is binding.
                </div>
            </div>

            <div class="proposal_sign_print_date">
                <table>
                    <tr>
                        <td>Signature</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>
                    <tr>
                        <td>Print Name:</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>
                    <tr>
                        <td>Date:</td>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>
                </table>

            </div>
        </div>
    </div>


    @include('modals.proposals.create-new-proposal-modal')

</div>

@section('script')
    <script src="{{ asset('js/tree-dist/jstree.min.js') }}"></script>
    <script src="{{ asset('js/print/printThis.js') }}"></script>

    @include('scripts.proposals.proposals-sidebar-js')
    @include('scripts.proposals.proposals-js')

    {{--<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.4/lodash.min.js"></script>--}}
    {{--@include('scripts.resizable-sidebar-js');--}}
@endsection