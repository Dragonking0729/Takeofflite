<div class="container-fluid">
    {{--invoice toolbar--}}
    <div class="row invoice_toolbar">
        <div class="col-md-3 d-flex pl-0 align-items-center toolbar_icons">
            <button type="button" class="btn btn-sm btn-link mr-2 select_all" data-checked="0"
                    title="Select All" {{count($invoice_list) ? '' : 'disabled'}}>
            </button>
            <button type="button" class="btn btn-sm btn-link mr-2 create_new_invoice" data-checked="0"
                    title="Create new invoice">
            </button>
            <button type="button" class="btn btn-sm btn-link mr-2 invoice_email" title="Send email" {{count($invoice_list) ? '' : 'disabled'}}>
            </button>
            <button type="button" class="btn btn-sm btn-link mr-2 print_invoice" title="Print" {{count($invoice_list) ? '' : 'disabled'}}>
            </button>
            <button type="button" class="btn btn-sm btn-link mr-2 delete_invoice disabled"
                    title="Delete selected lines">
            </button>
            <button type="button" class="btn btn-sm btn-link mr-2 preview_invoice" title="invoice preview" {{count($invoice_list) ? '' : 'disabled'}}>
            </button>
            <button type="button" class="btn btn-sm btn-link mr-2 view_invoice" title="Exit preview mode" {{count($invoice_list) ? '' : 'disabled'}}>
            </button>
            <button type="button" class="btn btn-sm btn-link mr-2 unlock_preview" title="Lock proposal"
                    data-id="{{$page_info['invoice_id']}}" {{count($invoice_list) ? '' : 'disabled'}}>
            </button>
            <button type="button" class="btn btn-sm btn-link mr-2 lock_preview" title="Unlock proposal"
                    {{count($invoice_list) ? '' : 'disabled'}}
                    data-view="{{count($invoice_list) ? $invoice_list[0]->is_locked : ''}}"
                    data-id="{{$page_info['invoice_id']}}">
            </button>
            <button type="button" class="btn btn-sm btn-link mr-2 show_hide_fields" title="Show/hide line fields" {{count($invoice_list) ? '' : 'disabled'}}>
            </button>
            <button class="btn btn-sm btn-link mr-2 remove_invoice"
                    type="button" title="Delete invoice" {{count($invoice_list) ? '' : 'disabled'}}
                    data-id="{{$page_info['invoice_id']}}">
            </button>
        </div>
        <select class="col-md-2 select_invoice">
            @if (count($invoice_list))
                @foreach($invoice_list as $option)
                    <option value="{{$option->id}}" {{$page_info['invoice_id'] == $option->id ? 'selected' : ''}}>
                        {{$option->invoice_name}} #{{$option->id}}
                    </option>
                @endforeach
            @endif
        </select>
        <div class="col-md-5 total_values">
            <div class="contract_total_cost_div">
                Contract Cost Total: $ <span
                        class="contract_total_cost">{{count($invoice_total) ? $invoice_total[0]->contractor_cost_total : 0}}</span>
            </div>
            <div class="markup_total_div">
                Markup Total: $ <span
                        class="markup_total">{{count($invoice_total) ? $invoice_total[0]->markup_total : 0}}</span>
            </div>
            <div class="customer_price_div">
                Customer Price: $ <span
                        class="customer_price">{{count($invoice_total) ? $invoice_total[0]->customer_price : 0}}</span>
            </div>
        </div>
        <div class="col-md-2 invoice_date">
            09/30/21 2:00 PM
        </div>
    </div>

    {{-- show/hide detail --}}
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="show_hide_block" style="display: {{count($invoice_list) ? '' : 'none'}}">
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
    <div class="row mt-3" id="invoice_top_info_block" style="display: {{count($invoice_list) ? '' : 'none'}}">
        <div class="col-md-12">
            <div id="invoice_top_info"
                 class="summernote">{!!$invoice_texts ? $invoice_texts->top_info_text : ''!!}</div>
        </div>
    </div>

    {{--invoice items--}}
    <div class="row mt-1" id="invoice_items_block">
        <div class="col-md-12" id="invoice_items">
            @foreach($invoice_lines as $index => $invoice_line)
                <div class="invoice_item"
                     data-order="{{$invoice_line->id}}">
                    <div class="invoice_item_left">
                        <span class="select_invoice_item" data-checked="0" data-id="{{$invoice_line->id}}"></span>
                        <span class="invoice_item_circle"></span>
                        <span class="invoice_detail_open_close" data-open="0"></span>
                    </div>
                    <div class="invoice_item_right">
                        <div class="invoice_item_right_top">
                            <div class="item">
                                <label for="invoice_item__{{$invoice_line->id}}">invoice Item</label>
                                <input type="text" class="form-control invoice_item_number_description"
                                       id="invoice_item__{{$invoice_line->id}}"
                                       data-id="{{$invoice_line->id}}" disabled
                                       value="{{$invoice_line->invoice_item_number.' '.$invoice_line->invoice_item_description}}">
                            </div>
                            <div class="invoice_qty_uom_price_cost">
                                <div class="invoice_qty">
                                    <label for="invoice_quantity__{{$invoice_line->id}}">Quantity</label>
                                    <input type="text" class="form-control invoice_item_billing_quantity"
                                           data-id="{{$invoice_line->id}}"
                                           id="invoice_quantity__{{$invoice_line->id}}"
                                           value="{{$invoice_line->invoice_item_billing_quantity}}">
                                </div>
                                <div class="invoice_uom">
                                    <label for="invoice_uom__{{$invoice_line->id}}">UOM</label>
                                    <select class="form-control invoice_item_uom"
                                            id="invoice_uom__{{$invoice_line->id}}"
                                            data-id="{{$invoice_line->id}}">
                                        @foreach($uom as $option)
                                            <option value="{{$option}}" {{$option === $invoice_line->invoice_item_uom ? 'selected' : ''}}>
                                                {{$option}}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="invoice_price">
                                    <label for="invoice_price__{{$invoice_line->id}}">Unit Price</label>
                                    <input type="text" class="form-control invoice_unit_price"
                                           id="invoice_price__{{$invoice_line->id}}"
                                           value="{{$invoice_line->invoice_unit_price}}"
                                           data-id="{{$invoice_line->id}}">
                                </div>
                                <div class="invoice_contract_cost">
                                    <label for="invoice_contract_cost__{{$invoice_line->id}}">Contractor Cost</label>
                                    <input type="text" class="form-control invoice_contractor_cost_total"
                                           id="invoice_contract_cost__{{$invoice_line->id}}"
                                           data-id="{{$invoice_line->id}}"
                                           value="{{$invoice_line->invoice_contractor_cost_total}}">
                                </div>
                            </div>
                            <div class="invoice_markup">
                                <div>
                                    <label for="invoice_markup__{{$invoice_line->id}}">Markup Percent</label>
                                    <input type="text" class="form-control invoice_item_markup_percent"
                                           id="invoice_markup__{{$invoice_line->id}}"
                                           data-id="{{$invoice_line->id}}"
                                           value="{{$invoice_line->invoice_item_markup_percent}}">
                                </div>
                                <div>
                                    <label for="invoice_markup_dollars__{{$invoice_line->id}}">Markup Dollars</label>
                                    <input type="text" class="form-control invoice_markup_dollars"
                                           id="invoice_markup_dollars__{{$invoice_line->id}}"
                                           data-id="{{$invoice_line->id}}"
                                           value="{{$invoice_line->invoice_markup_dollars}}">
                                </div>
                            </div>
                            <div class="invoice_customer">
                                <div>
                                    <label for="invoice_customer_price_per_unit__{{$invoice_line->id}}">Customer Price
                                        Per Unit</label>
                                    <input type="text" class="form-control invoice_customer_price_per_unit"
                                           id="invoice_customer_price_per_unit__{{$invoice_line->id}}"
                                           data-id="{{$invoice_line->id}}"
                                           value="{{$invoice_line->invoice_customer_price_per_unit}}">
                                </div>
                                <div>
                                    <label for="invoice_customer_price__{{$invoice_line->id}}">Customer Total
                                        Price</label>
                                    <input type="text" class="form-control invoice_customer_price"
                                           data-id="{{$invoice_line->id}}"
                                           id="invoice_customer_price__{{$invoice_line->id}}"
                                           value="{{$invoice_line->invoice_customer_price}}">
                                </div>
                            </div>
                        </div>

                        <div class="invoice_item_right_bottom">
                            <div class="invoice_textarea">
                                <div class="invoice_title">
                                    <textarea class="form-control invoice_title_text" rows="2"
                                              placeholder="Enter a long title if needed - NOT SHOWN ON invoice"></textarea>
                                </div>
                                <div class="invoice_toilet">
                                    <textarea class="form-control invoice_customer_scope_explanation" rows="2"
                                              data-id="{{$invoice_line->id}}"
                                              id="invoice_customer_scope_explanation__{{$invoice_line->id}}"
                                              placeholder="Enter a detailed description - THIS IS SHOWN ON INVOICE">{{trim($invoice_line->invoice_customer_scope_explanation)}}</textarea>
                                </div>
                                <div class="invoice_notes">
                                    <textarea class="form-control invoice_internal_notes" rows="2"
                                              data-id="{{$invoice_line->id}}"
                                              id="invoice_note__{{$invoice_line->id}}"
                                              placeholder="Notes">{{trim($invoice_line->invoice_internal_notes)}}</textarea>
                                </div>
                                <span class="remove_single_invoice_line" data-id="{{$invoice_line->id}}"
                                      title="Remove this invoice"></span>
                                <span class="invoice_attach"></span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>

    {{-- bottom info --}}
    <div class="row mt-3" id="invoice_bottom_info_block" style="display: {{count($invoice_list) ? '' : 'none'}}">
        <div class="col-md-12">
            <div id="invoice_bottom_info"
                 class="summernote">{!! $invoice_texts ? $invoice_texts->bottom_info_text: '' !!}</div>
        </div>
    </div>

    {{--invoice preview--}}
    <div class="row mt-1" id="invoice_preview_block">
        <div class="col-md-12" id="invoice_preview_content">
            <div class="invoice_header_title">
                INVOICE
            </div>
            <div class="invoice_header">
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

            <div class="invoice_body_items">
                <div>&nbsp;</div>
                <table>
                    <thead>
                    <tr>
                        <th>Invoice Item</th>
                        <th>Description</th>
                    </tr>
                    </thead>
                    <tbody id="invoice_preview_body">

                    </tbody>
                </table>
            </div>

            <hr>

            <div class="my-3" id="invoice_preview_total_price">
                <div class="invoice_preview_total_price">
                    Total Price: $<span></span>
                </div>
            </div>

            <div class="invoice_pay_block">
                <div class="invoice_pay_button">
                    PAY NOW
                </div>
            </div>


        </div>
    </div>


    @include('modals.invoices.create-new-invoice-modal')

</div>

@section('script')
    <script src="{{ asset('js/tree-dist/jstree.min.js') }}"></script>
    <script src="{{ asset('js/print/printThis.js') }}"></script>

    @include('scripts.invoice.invoice-sidebar-js')
    @include('scripts.invoice.invoice-js')

    {{--<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.4/lodash.min.js"></script>--}}
    {{--@include('scripts.resizable-sidebar-js');--}}
@endsection