<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectShare;
use App\Models\Spreadsht;
use App\Models\ProjectSetting;
use App\Models\Uom;
use App\Models\User;
use App\Models\UserInvoiceDetail;
use App\Models\UserInvoiceGroup;
use App\Models\UserinvoiceItem;
use App\Models\UserInvoices;
use App\Models\UserInvoiceText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class InvoiceController extends Controller
{
    protected $user_id = 0;
    protected $page_name = 'Invoices';

    public function __construct()
    {
        $this->user_id = Session::get('user_id');
        $this->page_name = 'Invoices';
    }

    // get invoice group > invoice item tree
    private function buildinvoiceItemTree($invoice_group, $invoice_items)
    {
        $tree_data = [];
        $opened = false;
        $updated_invoice_group = [];
        $initial_tree_state = array("opened" => $opened);
        $folder_id = '';
        foreach ($invoice_group as $item) {
            if ($item['is_folder']) {
                $folder_id = $item['id'];
                $updated_invoice_group[$folder_id][] = $item;
            } else {
                $updated_invoice_group[$folder_id][] = $item;
            }
        }

        $updated_invoice_items = [];
        foreach ($invoice_items as $item) {
            $updated_invoice_items[$item['invoice_standard_item_group_number']][] = $item;
        }
        foreach ($invoice_group as $item) {
            $group_folder_desc = $item['invoice_standard_item_group_description'] ? '-' . $item['invoice_standard_item_group_description'] : '';
//            $group_folder_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_group_folder_desc);
            $group_folder_text = "{$item['invoice_standard_item_group_number']}{$group_folder_desc}";
            if ($item['is_folder']) {
                // get folder children
                $folder_children = [];
                $groups = $updated_invoice_group[$item['id']];
                foreach ($groups as $group) {
                    if (empty($group['is_folder'])) {
                        $group_desc = $group['invoice_standard_item_group_description'] ? '-' . $group['invoice_standard_item_group_description'] : '';
//                        $group_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_group_desc);
                        $group_text = "{$group['invoice_standard_item_group_number']}{$group_desc}";
                        if (array_key_exists($group['invoice_standard_item_group_number'], $updated_invoice_items)) { // item node
                            // get group children
                            $invoiceitems = $updated_invoice_items[$group['invoice_standard_item_group_number']];
                            $group_children = [];
                            foreach ($invoiceitems as $invoiceitem) {
                                $item_desc = $invoiceitem['invoice_standard_item_description'] ? '-' . $invoiceitem['invoice_standard_item_description'] : '';
//                                $item_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_item_desc);
                                $item_text = "{$invoiceitem['invoice_standard_item_number']}{$item_desc}";

                                $group_children[] = [
                                    "id" => "invoice_item-{$invoiceitem['id']}",
                                    "text" => $item_text,
                                    "type" => "child"
                                ];
                            }
                            $folder_children[] = [
                                "id" => "invoice_group-{$group['id']}",
                                "text" => $group_text,
                                "state" => $initial_tree_state,
                                "children" => $group_children
                            ];
                        } else {
                            $folder_children[] = [
                                "id" => "invoice_group-{$group['id']}",
                                "text" => $group_text,
                                "state" => $initial_tree_state,
                            ];
                        }
                    }
                }
                $tree_data[] = [
                    "id" => "folder-{$item['id']}",
                    "text" => $group_folder_text,
                    "state" => $initial_tree_state,
                    "children" => $folder_children
                ];
            }
        }

        return $tree_data;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect('dashboard');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id , @param int $invoice_id_param
     * @return \Illuminate\Http\Response
     */
    public function show($id, $invoice_id_param = '')
    {
        $sharedProjectId = ProjectShare::where('share_receiver_user_id', $this->user_id)->pluck('share_project_number');
        $sharedProjects = Project::whereIn('id', $sharedProjectId)->orderBy('project_name')->get(); // shared projects
        $privateProjects = Project::where('user_id', $this->user_id)->orderBy('project_name')->get(); // private
        $projects = get_project_list($privateProjects, $sharedProjects);

        $project_name = Project::find($id)->project_name;
        $page_info = array(
            'project_id' => $id,
            'project_name' => $project_name,
            'name' => $this->page_name,
            'invoice_id' => $invoice_id_param
        );

        $company_info = User::find($this->user_id);
        $project_info = Project::find($id);

        $all_invoice_group = UserInvoiceGroup::where('user_id', $this->user_id)->orderBy(DB::raw('invoice_standard_item_group_number+0'))->get()->toArray();
        $all_invoice_items = UserinvoiceItem::where('user_id', $this->user_id)->orderBy(DB::raw('invoice_standard_item_group_number+0'))->orderBy(DB::raw('invoice_standard_item_number+0'))->get()->toArray();
        $invoice_item_tree_data = $this->buildinvoiceItemTree($all_invoice_group, $all_invoice_items);

        $invoice_list = UserInvoices::where('user_id', $this->user_id)->where('project_id', $id)->orderBy('id', 'DESC')->get();

        $invoice_lines = [];
        $invoice_total = [];
        $invoice_texts = null;
        if ($invoice_id_param) {
            // select from invoice dropdown
            $invoice_lines = UserInvoiceDetail::where('user_id', $this->user_id)->where('project_id', $id)
                ->where('invoice_id', $invoice_id_param)->orderBy('invoice_list_order')->get();

            if (count($invoice_lines)) {
                $invoice_total_query = "SELECT SUM(invoice_contractor_cost_total) AS contractor_cost_total, SUM(invoice_markup_dollars) AS markup_total, SUM(invoice_customer_price) AS customer_price FROM user_invoice_detail WHERE user_id='" . $this->user_id . "' AND project_id='" . $id . "' AND invoice_id = '" . $invoice_id_param . "'";
                $invoice_total = DB::select($invoice_total_query);
            }

            $invoice_texts = UserInvoices::find($invoice_id_param);
        } else {
            if (count($invoice_list)) {
                $invoice_id = $invoice_list[0]->id;
                $page_info['invoice_id'] = $invoice_id;
                $invoice_lines = UserInvoiceDetail::where('user_id', $this->user_id)->where('project_id', $id)
                    ->where('invoice_id', $invoice_id)->orderBy('invoice_list_order')->get();
                if (count($invoice_lines)) {
                    $invoice_total_query = "SELECT SUM(invoice_contractor_cost_total) AS contractor_cost_total, SUM(invoice_markup_dollars) AS markup_total, SUM(invoice_customer_price) AS customer_price FROM user_invoice_detail WHERE user_id='" . $this->user_id . "' AND project_id='" . $id . "' AND invoice_id = '" . $invoice_id . "'";
                    $invoice_total = DB::select($invoice_total_query);
                }

                $invoice_texts = UserInvoices::find($invoice_id);
            }
        }

        $document_text_list = UserInvoiceText::where('user_id', $this->user_id)->get();


        $uom = [];
        $uom_array = Uom::orderBy('uom_name')->get();
        foreach ($uom_array as $item) {
            array_push($uom, $item->uom_name);
        }

        $is_sidebar_open = 1;
        $ss_setting_count = ProjectSetting::where('user_id', $this->user_id)->where('project_id', $id)->where('page_name', $this->page_name)->get();
        if (count($ss_setting_count)) {
            $is_sidebar_open = $ss_setting_count[0]->is_sidebar_open;
        }

        return view('invoices.index',
            compact('projects',
                'company_info',
                'project_info',
                'page_info',
                'is_sidebar_open',
                'invoice_item_tree_data',
                'uom',
                'invoice_list',
                'invoice_lines',
                'invoice_total',
                'invoice_texts',
                'document_text_list'
            )
        );
    }


    // remove selected invoice lines
    public function remove_bulk_invoice_items(Request $request)
    {
        $selected_rows = $request->selectedLines;
        UserInvoiceDetail::whereIn('id', $selected_rows)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice Items are removed successfully',
        ]);
    }


    // remove single invoice line
    public function remove_single_invoice_line(Request $request)
    {
        $invoiceId = $request->invoiceId;
        UserInvoiceDetail::find($invoiceId)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice item is removed successfully',
        ]);
    }


    // remove invoice
    public function remove_invoice(Request $request)
    {
        $project_id = $request->project_id;
        $invoice_id = $request->invoice_id;
        UserInvoiceDetail::where('invoice_id', $invoice_id)->where('project_id', $project_id)->where('user_id', $this->user_id)->delete();
        UserInvoices::find($invoice_id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice removed successfully',
        ]);
    }

    // update lock status - published/unpublished
    public function update_lock(Request $request)
    {
        $invoice_id = $request->invoice_id;
        $invoice = UserInvoices::find($invoice_id);
        $invoice->is_locked = $request->is_locked;
        $invoice->preview_content = $request->preview_content;

        if ($request->is_locked) {
            $invoice->is_viewed = 0;
            $message = 'Invoice is published successfully';
        } else {
            $message = 'Invoice is unpublished successfully';
        }

        $invoice->save();

        return response()->json([
            'status' => 'success',
            'message' => $message,
        ]);
    }


    // get document text
    public function get_document_text(Request $request)
    {
        $id = $request->id;
        $invoice = UserInvoiceText::find($id);
        $text = $invoice->text;

        return response()->json([
            'status' => 'success',
            'message' => 'Document text fetched successfully!',
            'data' => $text
        ]);
    }

    // add invoice line by tree clicking
    public function add_invoice_line_by_tree(Request $request)
    {
        $create_new_invoice = $request->create_new_invoice;

        $invoice_item_id = $request->invoice_item_id;
        $invoice_id = $request->invoice_id;
        $project_id = $request->project_id;
        $is_new_invoice = 0;
        if ($create_new_invoice) {
            // create new invoice
            $is_new_invoice = 1;
            $new_invoice = new UserInvoices;
            $new_invoice->user_id = $this->user_id;
            $new_invoice->project_id = $project_id;
            $new_invoice->save();
            $invoice_id = $new_invoice->id;
        }

        $invoice_item = UserinvoiceItem::find($invoice_item_id);
        $invoice_item_number = $invoice_item->invoice_standard_item_number;
        $invoice_line = UserInvoiceDetail::where('user_id', $this->user_id)->where('project_id', $project_id)->where('invoice_id', $invoice_id)->where('invoice_item_number', $invoice_item_number)->first();
        if ($invoice_line !== null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invoice line already exist'
            ]);
        }

        $invoice = new UserInvoiceDetail;
        $invoice_item_description = $invoice_item->invoice_standard_item_description;
        $invoice_group_number = $invoice_item->invoice_standard_item_group_number;
        $invoice_uom = $invoice_item->invoice_standard_item_uom;
        $invoice_markup_percent = $invoice_item->invoice_standard_item_default_markup_percent ? $invoice_item->invoice_standard_item_default_markup_percent : 0;
        $invoice_text = $invoice_item->invoice_standard_item_explanatory_text ? $invoice_item->invoice_standard_item_explanatory_text : '';
        $invoice_notes = $invoice_item->invoice_standard_item_internal_notes ? $invoice_item->invoice_standard_item_internal_notes : '';

        $invoice_group = UserInvoiceGroup::where('user_id', $this->user_id)->where('invoice_standard_item_group_number', $invoice_group_number)->first();
        $invoice_group_desc = $invoice_group->invoice_standard_item_group_description;

        $invoice->user_id = $this->user_id;
        $invoice->project_id = $project_id;
        $invoice->invoice_id = $invoice_id;
        $invoice->invoice_item_group_number = $invoice_group_number;
        $invoice->invoice_item_group_desc = $invoice_group_desc;
        $invoice->invoice_item_number = $invoice_item_number;
        $invoice->invoice_item_description = $invoice_item_description;
        $invoice->invoice_item_uom = $invoice_uom;
        $invoice->invoice_item_markup_percent = $invoice_markup_percent;
        $invoice->invoice_customer_scope_explanation = $invoice_text;
        $invoice->invoice_internal_notes = $invoice_notes;
        $invoice->invoice_unit_price = 0;
        $invoice->invoice_markup_dollars = 0;
        $invoice->invoice_contractor_cost_total = 0;
        $invoice->invoice_customer_price = 0;
        $invoice->invoice_customer_price_per_unit = 0;

        $invoice->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Invoice line is added successfully',
            'data' => [
                'is_new_invoice' => $is_new_invoice,
                'invoice_id' => $invoice_id,
                'invoice_line_id' => $invoice->id,
                'markup_percent' => $invoice_markup_percent,
                'invoice_description' => $invoice_item_description,
                'uom' => $invoice_uom,
                'invoice_text' => $invoice_text,
                'invoice_note' => $invoice_notes,
            ]
        ]);
    }


    // update invoice line order
    public function update_invoice_line_order(Request $request)
    {
        $order = $request->order;
        for ($position = 1; $position <= count($order); $position++) {
            $id = $order[$position - 1];
            $invoice_line = UserInvoiceDetail::find($id);
            $invoice_line->invoice_list_order = $position;
            $invoice_line->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice line order is updated successfully'
        ]);
    }


    // update invoice line each field
    public function update_invoice_line_field(Request $request)
    {
        $invoice_id = $request->invoice_id;
        $field = $request->field;
        $value = $request->value;
        $invoice_line = UserInvoiceDetail::find($invoice_id);
        if ($field !== 'invoice_item_number_description') {
            $invoice_line[$field] = $value;
        }

        $invoice_contractor_cost_total = $invoice_line->invoice_contractor_cost_total;
        if ($field === 'invoice_item_billing_quantity') {
            $invoice_customer_price = $invoice_line->invoice_customer_price;
            $invoice_unit_price = round($invoice_contractor_cost_total / $value, 2);
            $invoice_customer_price_per_unit = round($invoice_customer_price / $value, 2);

            $invoice_line->invoice_unit_price = $invoice_unit_price;
            $invoice_line->invoice_customer_price_per_unit = $invoice_customer_price_per_unit;
        } else if ($field === 'invoice_item_markup_percent') {
            $qty = $invoice_line->invoice_item_billing_quantity;
            $invoice_markup_dollars = round($invoice_contractor_cost_total * $value / 100, 2);
            $invoice_customer_price = $invoice_contractor_cost_total + $invoice_markup_dollars;
            $invoice_customer_price_per_unit = round($invoice_customer_price / $qty, 2);

            $invoice_line->invoice_markup_dollars = $invoice_markup_dollars;
            $invoice_line->invoice_customer_price_per_unit = $invoice_customer_price_per_unit;
            $invoice_line->invoice_customer_price = $invoice_customer_price;
        } else if ($field === 'invoice_item_number_description') {
            $invoice_line->invoice_item_number = $value['invoice_item_number'];
            $invoice_line->invoice_item_description = $value['invoice_item_description'];
        } else if ($field === 'invoice_unit_price') {
            $qty = $invoice_line->invoice_item_billing_quantity;
            $contractorCost = round($value * $qty, 2);
            $invoice_line->invoice_contractor_cost_total = $contractorCost;

            $markupPercent = $invoice_line->invoice_item_markup_percent;
            $markupDollars = $contractorCost * $markupPercent / 100;
            $invoice_line->invoice_markup_dollars = $markupDollars;

            $customerTotalPrice = $contractorCost + $markupDollars;
            $invoice_line->invoice_customer_price = $customerTotalPrice;

            $customerPricePerUnit = round($customerTotalPrice / $qty, 2);
            $invoice_line->invoice_customer_price_per_unit = $customerPricePerUnit;
        } else if ($field === 'invoice_contractor_cost_total') {
            $qty = $invoice_line->invoice_item_billing_quantity;
            $unitPrice = round($value / $qty, 2);
            $invoice_line->invoice_unit_price = $unitPrice;

            $markupPercent = $invoice_line->invoice_item_markup_percent;
            $markupDollars = 0;
            if ($markupPercent) {
                $markupDollars = round($value * $markupPercent / 100, 2);
            }
            $invoice_line->invoice_markup_dollars = $markupDollars;

            $customerTotalPrice = $value + $markupDollars;
            $invoice_line->invoice_customer_price = $customerTotalPrice;

            $customerPricePerUnit = round($customerTotalPrice / $qty, 2);
            $invoice_line->invoice_customer_price_per_unit = $customerPricePerUnit;
        } else if ($field === 'invoice_markup_dollars') {
            $qty = $invoice_line->invoice_item_billing_quantity;
            $contractorCost = $invoice_line->invoice_contractor_cost_total;
            $markupPercent = round($value / $contractorCost * 100, 2);
            $invoice_line->invoice_item_markup_percent = $markupPercent;

            $customerPrice = $contractorCost + $value;
            $invoice_line->invoice_customer_price = $customerPrice;

            $customerPricePerUnit = round($customerPrice / $qty, 2);
            $invoice_line->invoice_customer_price_per_unit = $customerPricePerUnit;
        } else if ($field === 'invoice_customer_price') {
            $qty = $invoice_line->invoice_item_billing_quantity;
            $customerPricePerUnit = round($value / $qty, 2);
            $invoice_line->invoice_customer_price_per_unit = $customerPricePerUnit;

            $markupDollars = $value - $invoice_contractor_cost_total;
            $invoice_line->invoice_markup_dollars = $markupDollars;

            if ($invoice_contractor_cost_total !== '0.00') {
                $markupPercent = round($markupDollars / $invoice_contractor_cost_total * 100, 2);
                $invoice_line->invoice_item_markup_percent = $markupPercent;
            }
        } else if ($field === 'invoice_customer_price_per_unit') {
            $qty = $invoice_line->invoice_item_billing_quantity;
            $customerPrice = round($value * $qty, 2);
            $invoice_line->invoice_customer_price = $customerPrice;

            $markupDollars = $invoice_contractor_cost_total + $customerPrice;
            $invoice_line->invoice_markup_dollars = $markupDollars;

            if ($invoice_contractor_cost_total !== '0.00') {
                $markupPercent = round($markupDollars / $invoice_contractor_cost_total * 100, 2);
                $invoice_line->invoice_item_markup_percent = $markupPercent;
            }
        }

        $invoice_line->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice line is updated successfully'
        ]);
    }


    // update invoice info
    public function update_invoice_info(Request $request)
    {
        $invoice_id = $request->invoice_id;
        $val = $request->val;
        $position = $request->position;

        $invoice = UserInvoices::find($invoice_id);
        $invoice[$position] = $val;
        $invoice->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice Info is updated successfully'
        ]);
    }


    public function update_invoice_sidebar_status(Request $request)
    {
        $sidebar_status = $request->sidebar_status;
        $project_id = $request->project_id;

        ProjectSetting::updateOrCreate(
            [
                'user_id' => $this->user_id,
                'project_id' => $project_id,
                'page_name' => $this->page_name
            ],
            [
                'user_id' => $this->user_id,
                'project_id' => $project_id,
                'page_name' => $this->page_name,
                'is_sidebar_open' => $sidebar_status
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Sidebar status updated successfully'
        ]);
    }


    public function send_invoice_email(Request $request)
    {
        $project_id = $request->project_id;
        $user_id = $this->user_id;

        // get customer portal link
        $result = genCustomerPortalLink($user_id, $project_id);
        $url = $result['url'];

        $contractor_info = User::find($user_id);
        $data = [
            'portal_link' => $url,
            'pass_code' => $user_id,
            'contractor_name' => $contractor_info->display_name
        ];
        $customer_info = Project::find($project_id);
        $from = $contractor_info->company_name ? $contractor_info->company_name . ' via Takeoff Lite' : $contractor_info->display_name . ' via Takeoff Lite';
        if ($customer_info->customer_email) {
            Mail::send('mails.customer-portal-invoice-mail', $data, function ($messages) use ($customer_info, $from) {
                $messages->to($customer_info->customer_email);
                $messages->subject('Takeoff Lite');
                $messages->from('email@takeofflite.com', $from);
            });
            return response()->json([
                'status' => 'success',
                'message' => 'Email sent successfully'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Customer email not existing'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $project_id = $request->project_id;
        $create_from = $request->create_from; // create_new or import_existing
        $invoice_name = $request->invoice_name;

        $record = [
            'user_id' => $this->user_id,
            'project_id' => $project_id,
            'invoice_name' => $invoice_name
        ];
        $new_invoice = UserInvoices::create($record);
        $invoice_id = $new_invoice->id;

        if ($create_from === 'import_existing') {
            $sort = 'ss_quote_or_invoice_item';
            $data = Spreadsht::where('user_id', $this->user_id)->where('project_id', $project_id)->orderBy($sort)->get()->toArray();
            $updated = [];
            foreach ($data as $row) {
                $updated[$row[$sort]][] = $row;
            }

            $over_line_array = [];
            foreach ($data as $row) {
                $sort_val = $row[$sort];
                if (!in_array($sort_val, $over_line_array)) {
                    array_push($over_line_array, $sort_val);
                    $labor_total = 0;
                    $material_total = 0;
                    $subcontract_total = 0;
                    $other_total = 0;
                    for ($i = 0; $i < count($updated[$sort_val]); $i++) {
                        $labor_total += Round($updated[$sort_val][$i]['ss_item_takeoff_quantity'] / $updated[$sort_val][$i]['ss_labor_conversion_factor'] * $updated[$sort_val][$i]['ss_labor_price'], 2);
                        $material_total += Round($updated[$sort_val][$i]['ss_item_takeoff_quantity'] / $updated[$sort_val][$i]['ss_material_conversion_factor'] * $updated[$sort_val][$i]['ss_material_price'], 2);
                        $subcontract_total += Round($updated[$sort_val][$i]['ss_item_takeoff_quantity'] / $updated[$sort_val][$i]['ss_subcontract_conversion_factor'] * $updated[$sort_val][$i]['ss_subcontract_price'], 2);
                        $other_total += Round($updated[$sort_val][$i]['ss_item_takeoff_quantity'] / $updated[$sort_val][$i]['ss_other_conversion_factor'] * $updated[$sort_val][$i]['ss_other_price'], 2);
                    }
                    $contractor_cost = $labor_total + $material_total + $subcontract_total + $other_total;

                    $invoice_default = $sort_val;
                    $preg = explode(' ', $invoice_default, 2);
                    if ($preg[0] !== "") {
                        $invoice_item_number = $preg[0];
                        $invoice_item_description = $preg[1];
                        $invoice_item = UserinvoiceItem::where('user_id', $this->user_id)->where('invoice_standard_item_number', $invoice_item_number)
                            ->where('invoice_standard_item_description', $invoice_item_description)->first();
                        if (isset($invoice_item)) {
                            $invoice_group_number = $invoice_item->invoice_standard_item_group_number;
                            $invoice_uom = $invoice_item->invoice_standard_item_uom;
                            $invoice_markup_percent = $invoice_item->invoice_standard_item_default_markup_percent;
                            $invoice_text = $invoice_item->invoice_standard_item_explanatory_text;
                            $invoice_notes = $invoice_item->invoice_standard_item_internal_notes;

                            $invoice_group = UserInvoiceGroup::where('user_id', $this->user_id)->where('invoice_standard_item_group_number', $invoice_group_number)->first();
                            $invoice_group_desc = $invoice_group->invoice_standard_item_group_description;

                            $invoice_ = new UserInvoiceDetail;
                            $invoice_->user_id = $this->user_id;
                            $invoice_->project_id = $project_id;
                            $invoice_->invoice_id = $invoice_id;
                            $invoice_->invoice_item_group_number = $invoice_group_number;
                            $invoice_->invoice_item_group_desc = $invoice_group_desc;
                            $invoice_->invoice_item_number = $invoice_item_number;
                            $invoice_->invoice_item_description = $invoice_item_description;
                            $invoice_->invoice_item_uom = $invoice_uom;
                            $invoice_->invoice_item_markup_percent = $invoice_markup_percent;
                            $invoice_->invoice_customer_scope_explanation = $invoice_text;
                            $invoice_->invoice_internal_notes = $invoice_notes;

                            $invoice_->invoice_unit_price = $contractor_cost;
                            $invoice_markup_dollars = $contractor_cost * $invoice_markup_percent / 100;
                            $invoice_->invoice_markup_dollars = $invoice_markup_dollars;
                            // the total cost of all items in the SS assigned to this invoice item
                            $invoice_->invoice_contractor_cost_total = $contractor_cost;
                            // contractor_cost_total is the total total contractor cost plus markup dollars
                            $invoice_contractor_cost_total = $contractor_cost + $invoice_markup_dollars;
                            $invoice_->invoice_customer_price = $invoice_contractor_cost_total;
                            $invoice_->invoice_customer_price_per_unit = $invoice_contractor_cost_total;

                            $invoice_->save();
                        }
                    }
                }
            }
        }

        return back()->with('success', 'Created successfully');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
