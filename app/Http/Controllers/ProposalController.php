<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectShare;
use App\Models\Spreadsht;
use App\Models\ProjectSetting;
use App\Models\Uom;
use App\Models\User;
use App\Models\UserProposalDetail;
use App\Models\UserProposalGroup;
use App\Models\UserProposalItem;
use App\Models\UserProposals;
use App\Models\UserProposalText;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class ProposalController extends Controller
{
    protected $user_id = 0;
    protected $page_name = 'Proposals';

    public function __construct()
    {
        $this->user_id = Session::get('user_id');
        $this->page_name = 'Proposals';
    }

    // get proposalitem group > proposalitem item tree
    private function buildProposalItemTree($proposal_group, $proposal_items)
    {
        $tree_data = [];
        $opened = false;
        $updated_proposal_group = [];
        $initial_tree_state = array("opened" => $opened);
        $folder_id = '';
        foreach ($proposal_group as $item) {
            if ($item['is_folder']) {
                $folder_id = $item['id'];
                $updated_proposal_group[$folder_id][] = $item;
            } else {
                $updated_proposal_group[$folder_id][] = $item;
            }
        }

        $updated_proposal_items = [];
        foreach ($proposal_items as $item) {
            $updated_proposal_items[$item['proposal_standard_item_group_number']][] = $item;
        }
        foreach ($proposal_group as $item) {
            $group_folder_desc = $item['proposal_standard_item_group_description'] ? '-' . $item['proposal_standard_item_group_description'] : '';
            //            $group_folder_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_group_folder_desc);
            $group_folder_text = "{$item['proposal_standard_item_group_number']}{$group_folder_desc}";
            if ($item['is_folder']) {
                // get folder children
                $folder_children = [];
                $groups = $updated_proposal_group[$item['id']];
                foreach ($groups as $group) {
                    if (empty($group['is_folder'])) {
                        $group_desc = $group['proposal_standard_item_group_description'] ? '-' . $group['proposal_standard_item_group_description'] : '';
                        //                        $group_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_group_desc);
                        $group_text = "{$group['proposal_standard_item_group_number']}{$group_desc}";
                        if (array_key_exists($group['proposal_standard_item_group_number'], $updated_proposal_items)) { // item node
                            // get group children
                            $proposalitems = $updated_proposal_items[$group['proposal_standard_item_group_number']];
                            $group_children = [];
                            foreach ($proposalitems as $proposalitem) {
                                $item_desc = $proposalitem['proposal_standard_item_description'] ? '-' . $proposalitem['proposal_standard_item_description'] : '';
                                //                                $item_desc = str_replace(['"', "'", '<', '>'], ['&quot', '&apos', '&lt', '&gt'], $temp_item_desc);
                                $item_text = "{$proposalitem['proposal_standard_item_number']}{$item_desc}";

                                $group_children[] = [
                                    "id" => "proposal_item-{$proposalitem['id']}",
                                    "text" => $item_text,
                                    "type" => "child"
                                ];
                            }
                            $folder_children[] = [
                                "id" => "proposal_group-{$group['id']}",
                                "text" => $group_text,
                                "state" => $initial_tree_state,
                                "children" => $group_children
                            ];
                        } else {
                            $folder_children[] = [
                                "id" => "proposal_group-{$group['id']}",
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
     * @param int $id , @param int $proposal_id_param
     * @return \Illuminate\Http\Response
     */
    public function show($id, $proposal_id_param = '')
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
            'proposal_id' => $proposal_id_param
        );

        $company_info = User::find($this->user_id);
        $project_info = Project::find($id);

        $all_proposal_group = UserProposalGroup::where('user_id', $this->user_id)->orderBy(DB::raw('proposal_standard_item_group_number+0'))->get()->toArray();
        $all_proposal_items = UserProposalItem::where('user_id', $this->user_id)->orderBy(DB::raw('proposal_standard_item_group_number+0'))->orderBy(DB::raw('proposal_standard_item_number+0'))->get()->toArray();
        $proposal_item_tree_data = $this->buildProposalItemTree($all_proposal_group, $all_proposal_items);

        $proposal_list = UserProposals::where('user_id', $this->user_id)->where('project_id', $id)->orderBy('id', 'DESC')->get();

        $proposal_lines = [];
        $proposal_total = [];
        $proposal_texts = null;
        if ($proposal_id_param) {
            // select from proposalitem dropdown
            $proposal_lines = UserProposalDetail::where('user_id', $this->user_id)->where('project_id', $id)
                ->where('proposal_id', $proposal_id_param)->orderBy('proposal_list_order')->get();

            if (count($proposal_lines)) {
                $proposal_total_query = "SELECT SUM(proposal_contractor_cost_total) AS contractor_cost_total, SUM(proposal_markup_dollars) AS markup_total, SUM(proposal_customer_price) AS customer_price FROM user_proposal_detail WHERE user_id='" . $this->user_id . "' AND project_id='" . $id . "' AND proposal_id = '" . $proposal_id_param . "'";
                $proposal_total = DB::select($proposal_total_query);
            }

            $proposal_texts = UserProposals::find($proposal_id_param);
        } else {
            if (count($proposal_list)) {
                $proposal_id = $proposal_list[0]->id;
                $page_info['proposal_id'] = $proposal_id;

                $proposal_lines = UserProposalDetail::where('user_id', $this->user_id)->where('project_id', $id)
                    ->where('proposal_id', $proposal_id)->orderBy('proposal_list_order')->get();
                if (count($proposal_lines)) {
                    $proposal_total_query = "SELECT SUM(proposal_contractor_cost_total) AS contractor_cost_total, SUM(proposal_markup_dollars) AS markup_total, SUM(proposal_customer_price) AS customer_price FROM user_proposal_detail WHERE user_id='" . $this->user_id . "' AND project_id='" . $id . "' AND proposal_id = '" . $proposal_id . "'";
                    $proposal_total = DB::select($proposal_total_query);
                }

                $proposal_texts = UserProposals::find($proposal_id);
            }
        }

        $document_text_list = UserProposalText::where('user_id', $this->user_id)->get();

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

        return view(
            'proposals.index',
            compact(
                'projects',
                'company_info',
                'project_info',
                'page_info',
                'is_sidebar_open',
                'proposal_item_tree_data',
                'uom',
                'proposal_list',
                'proposal_lines',
                'proposal_total',
                'proposal_texts',
                'document_text_list'
            )
        );
    }


    // remove selected proposalitem lines
    public function remove_bulk_proposal_items(Request $request)
    {
        $selected_rows = $request->selectedLines;
        UserProposalDetail::whereIn('id', $selected_rows)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Proposal items are removed successfully',
        ]);
    }


    // remove single proposalitem line
    public function remove_single_proposal_line(Request $request)
    {
        $proposalId = $request->proposalId;
        UserProposalDetail::find($proposalId)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Proposal item is removed successfully',
        ]);
    }


    // remove proposalitem
    public function remove_proposal(Request $request)
    {
        $project_id = $request->project_id;
        $proposal_id = $request->proposal_id;
        UserProposalDetail::where('proposal_id', $proposal_id)->where('project_id', $project_id)->where('user_id', $this->user_id)->delete();
        UserProposals::find($proposal_id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Proposal removed successfully',
        ]);
    }

    // update lock status - published/unpublished
    public function update_lock(Request $request)
    {
        $proposal_id = $request->proposal_id;
        $proposal = UserProposals::find($proposal_id);
        $proposal->is_locked = $request->is_locked;
        $proposal->preview_content = $request->preview_content;
        $proposal->approve_name = '';
        $proposal->approve_date = '';
        $proposal->is_viewed = 0;

        if ($request->is_locked) {
            $proposal->approve_status = 'Pending Approval';
            $message = 'Proposal is published successfully';
        } else {
            $proposal->approve_status = 'Not Sent';
            $message = 'Proposal is unpublished successfully';
        }

        $proposal->save();

        return response()->json([
            'status' => 'success',
            'message' => $message,
        ]);
    }

    // update proposal status manually
    public function update_status_manually(Request $request)
    {
        $proposal_id = $request->proposal_id;
        $proposal = UserProposals::find($proposal_id);
        $proposal->is_locked = $request->is_locked;
        $proposal->approve_status = $request->approve_status;
        $proposal->approve_name = '';
        $proposal->approve_date = '';
        $proposal->is_viewed = 0;
        if ($request->approve_status === 'Approved') {
            $contractor_name = User::find($this->user_id)->display_name;
            $proposal->approve_name = $contractor_name;
            $date = Carbon::now();
            $proposal->approve_date = $date->format("Y-m-d");
        }
        $proposal->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Updated proposal status successfully',
        ]);
    }

    // get document text
    public function get_document_text(Request $request)
    {
        $id = $request->id;
        $proposal = UserProposalText::find($id);
        $text = $proposal->text;

        return response()->json([
            'status' => 'success',
            'message' => 'Document text fetched successfully!',
            'data' => $text
        ]);
    }


    // add proposalitem line by tree clicking
    public function add_proposal_line_by_tree(Request $request)
    {
        $create_new_proposal = $request->create_new_proposal;

        $proposal_item_id = $request->proposal_item_id;
        $proposal_id = $request->proposal_id;
        $project_id = $request->project_id;
        $is_new_proposal = 0;
        if ($create_new_proposal) {
            // create new proposalitem
            $is_new_proposal = 1;
            $new_proposal = new UserProposals;
            $new_proposal->user_id = $this->user_id;
            $new_proposal->project_id = $project_id;
            $new_proposal->save();
            $proposal_id = $new_proposal->id;
        }

        $proposal_item = UserProposalItem::find($proposal_item_id);
        $proposal_item_number = $proposal_item->proposal_standard_item_number;
        $proposal_line = UserProposalDetail::where('user_id', $this->user_id)->where('project_id', $project_id)->where('proposal_id', $proposal_id)->where('proposal_item_number', $proposal_item_number)->first();
        if ($proposal_line !== null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Proposal line already exist'
            ]);
        }

        $proposal = new UserProposalDetail;
        $proposal_item_description = $proposal_item->proposal_standard_item_description;
        $proposal_group_number = $proposal_item->proposal_standard_item_group_number;
        $proposal_uom = $proposal_item->proposal_standard_item_uom;
        $proposal_markup_percent = $proposal_item->proposal_standard_item_default_markup_percent ? $proposal_item->proposal_standard_item_default_markup_percent : 0;
        $proposal_text = $proposal_item->proposal_standard_item_explanatory_text ? $proposal_item->proposal_standard_item_explanatory_text : '';
        $proposal_notes = $proposal_item->proposal_standard_item_internal_notes ? $proposal_item->proposal_standard_item_internal_notes : '';

        $proposal_group = UserProposalGroup::where('user_id', $this->user_id)->where('proposal_standard_item_group_number', $proposal_group_number)->first();
        $proposal_group_desc = $proposal_group->proposal_standard_item_group_description;

        $proposal->user_id = $this->user_id;
        $proposal->project_id = $project_id;
        $proposal->proposal_id = $proposal_id;
        $proposal->proposal_item_group_number = $proposal_group_number;
        $proposal->proposal_item_group_desc = $proposal_group_desc;
        $proposal->proposal_item_number = $proposal_item_number;
        $proposal->proposal_item_description = $proposal_item_description;
        $proposal->proposal_item_uom = $proposal_uom;
        $proposal->proposal_item_markup_percent = $proposal_markup_percent;
        $proposal->proposal_customer_scope_explanation = $proposal_text;
        $proposal->proposal_internal_notes = $proposal_notes;
        $proposal->proposal_unit_price = 0;
        $proposal->proposal_markup_dollars = 0;
        $proposal->proposal_contractor_cost_total = 0;
        $proposal->proposal_customer_price = 0;
        $proposal->proposal_customer_price_per_unit = 0;

        $proposal->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Proposal line is added successfully',
            'data' => [
                'is_new_proposal' => $is_new_proposal,
                'proposal_id' => $proposal_id,
                'proposal_line_id' => $proposal->id,
                'markup_percent' => $proposal_markup_percent,
                'proposal_description' => $proposal_item_description,
                'uom' => $proposal_uom,
                'proposal_text' => $proposal_text,
                'proposal_note' => $proposal_notes,
            ]
        ]);
    }


    public function add_proposal_from_cost_items(Request $request)
    {
        try {
            $proposal_item_id = $request->proposal_item_id;
            $project_id = $request->projectId;
            $spreadSheetData = Spreadsht::where([
                'user_id' => $this->user_id,
                'project_id' => $project_id,
            ])->whereNotNull('ss_quote_or_invoice_item')->get();

            $new_proposal = new UserProposals;
            $new_proposal->user_id = $this->user_id;
            $new_proposal->project_id = $project_id;
            $new_proposal->save();
            $proposal_id = $new_proposal->id;

            foreach ($spreadSheetData as $ss_item) {
                $proposal_item_arr = explode(' ', $ss_item->ss_quote_or_invoice_item);
                $proposal_item_id = $proposal_item_arr[0];
                array_shift($proposal_item_arr);
                $proposal_item_description = implode(' ', $proposal_item_arr);
                $proposal_item = UserProposalItem::where([
                    'proposal_standard_item_number' => $proposal_item_id,
                    'user_id' => $this->user_id,
                    'proposal_standard_item_description' => $proposal_item_description
                ])->first();

                $proposal_item_number = $proposal_item->proposal_standard_item_number;

                $proposal_line = UserProposalDetail::where('user_id', $this->user_id)->where('project_id', $project_id)->where('proposal_id', $proposal_id)->where('proposal_item_number', $proposal_item_number)->first();

                if ($proposal_line !== null) {
                    continue;
                }

                $proposal = new UserProposalDetail;
                $proposal_item_description = $proposal_item->proposal_standard_item_description;

                $proposal_group_number = $proposal_item->proposal_standard_item_group_number;
                $proposal_uom = $proposal_item->proposal_standard_item_uom;
                $proposal_markup_percent = $proposal_item->proposal_standard_item_default_markup_percent ? $proposal_item->proposal_standard_item_default_markup_percent : 0;
                $proposal_text = $proposal_item->proposal_standard_item_explanatory_text ? $proposal_item->proposal_standard_item_explanatory_text : '';
                $proposal_notes = $proposal_item->proposal_standard_item_internal_notes ? $proposal_item->proposal_standard_item_internal_notes : '';

                $proposal_group = UserProposalItem::where('user_id', $this->user_id)->where('proposal_standard_item_group_number', $proposal_group_number)->first();
                $proposal_group_desc = $proposal_group->proposal_standard_item_group_description;

                $proposal->user_id = $this->user_id;
                $proposal->project_id = $project_id;
                $proposal->proposal_id = $proposal_id;
                $proposal->proposal_item_group_number = $proposal_group_number;
                $proposal->proposal_item_group_desc = $proposal_group_desc;
                $proposal->proposal_item_number = $proposal_item_number;
                $proposal->proposal_item_description = $proposal_item_description;
                $proposal->proposal_item_uom = $proposal_uom;
                $proposal->proposal_item_markup_percent = $proposal_markup_percent;
                $proposal->proposal_customer_scope_explanation = $proposal_text;
                $proposal->proposal_internal_notes = $proposal_notes;
                $proposal->proposal_unit_price = 0;
                $proposal->proposal_markup_dollars = 0;

                $spreadSheetData2 = Spreadsht::where([
                    'user_id' => $this->user_id,
                    'project_id' => $project_id,
                    'ss_quote_or_invoice_item' => $proposal_item_number . " " . $proposal_item_description
                ])->get();

                $labor_total = 0;
                $material_total = 0;
                $subcontract_total = 0;
                $other_total = 0;
                for ($i = 0; $i < count($spreadSheetData2); $i++) {
                    if ($spreadSheetData2[$i]['ss_labor_conversion_factor'] == '0') {
                        $spreadSheetData2[$i]['ss_labor_conversion_factor'] = 1;
                    }
                    if ($spreadSheetData2[$i]['ss_material_conversion_factor'] == '0') {
                        $spreadSheetData2[$i]['ss_material_conversion_factor'] = 1;
                    }
                    if ($spreadSheetData2[$i]['ss_subcontract_conversion_factor'] == '0') {
                        $spreadSheetData2[$i]['ss_subcontract_conversion_factor'] = 1;
                    }
                    if ($spreadSheetData2[$i]['ss_other_conversion_factor'] == '0') {
                        $spreadSheetData2[$i]['ss_other_conversion_factor'] = 1;
                    }
                    $labor_total += Round($spreadSheetData2[$i]['ss_item_takeoff_quantity'] / $spreadSheetData2[$i]['ss_labor_conversion_factor'] * $spreadSheetData2[$i]['ss_labor_price'], 2);

                    $material_total += Round($spreadSheetData2[$i]['ss_item_takeoff_quantity'] / $spreadSheetData2[$i]['ss_material_conversion_factor'] * $spreadSheetData2[$i]['ss_material_price'], 2);
                    $subcontract_total += Round($spreadSheetData2[$i]['ss_item_takeoff_quantity'] / $spreadSheetData2[$i]['ss_subcontract_conversion_factor'] * $spreadSheetData2[$i]['ss_subcontract_price'], 2);
                    $other_total += Round($spreadSheetData2[$i]['ss_item_takeoff_quantity'] / $spreadSheetData2[$i]['ss_other_conversion_factor'] * $spreadSheetData2[$i]['ss_other_price'], 2);
                }
                $total_line = $labor_total + $material_total + $subcontract_total + $other_total;
                $proposal->proposal_contractor_cost_total = ($total_line > 0) ? $total_line : 0;

                $proposal->proposal_customer_price = 0;
                $proposal->proposal_customer_price_per_unit = 0;

                $proposal->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Proposal line(s) has been added successfully',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage(),
            ]);
        }
    }

    // update proposalitem line order
    public function update_proposal_line_order(Request $request)
    {
        $order = $request->order;
        for ($position = 1; $position <= count($order); $position++) {
            $id = $order[$position - 1];
            $proposal_line = UserProposalDetail::find($id);
            $proposal_line->proposal_list_order = $position;
            $proposal_line->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Proposal line order is updated successfully'
        ]);
    }


    // update proposalitem line each field
    public function update_proposal_line_field(Request $request)
    {
        $proposal_id = $request->proposal_id;
        $field = $request->field;
        $value = $request->value;
        $proposal_line = UserProposalDetail::find($proposal_id);
        if ($field !== 'proposal_item_number_description') {
            $proposal_line[$field] = $value;
        }

        $proposal_contractor_cost_total = $proposal_line->proposal_contractor_cost_total;
        if ($field === 'proposal_item_billing_quantity') {
            $proposal_customer_price = $proposal_line->proposal_customer_price;
            $proposal_unit_price = round($proposal_contractor_cost_total / $value, 2);
            $proposal_customer_price_per_unit = round($proposal_customer_price / $value, 2);

            $proposal_line->proposal_unit_price = $proposal_unit_price;
            $proposal_line->proposal_customer_price_per_unit = $proposal_customer_price_per_unit;
        } else if ($field === 'proposal_item_markup_percent') {
            $qty = $proposal_line->proposal_item_billing_quantity;
            $proposal_markup_dollars = round($proposal_contractor_cost_total * $value / 100, 2);
            $proposal_customer_price = $proposal_contractor_cost_total + $proposal_markup_dollars;
            $proposal_customer_price_per_unit = round($proposal_customer_price / $qty, 2);

            $proposal_line->proposal_markup_dollars = $proposal_markup_dollars;
            $proposal_line->proposal_customer_price_per_unit = $proposal_customer_price_per_unit;
            $proposal_line->proposal_customer_price = $proposal_customer_price;
        } else if ($field === 'proposal_item_number_description') {
            $proposal_line->proposal_item_number = $value['proposal_item_number'];
            $proposal_line->proposal_item_description = $value['proposal_item_description'];
        } else if ($field === 'proposal_unit_price') {
            $qty = $proposal_line->proposal_item_billing_quantity;
            $contractorCost = round($value * $qty, 2);
            $proposal_line->proposal_contractor_cost_total = $contractorCost;

            $markupPercent = $proposal_line->proposal_item_markup_percent;
            $markupDollars = $contractorCost * $markupPercent / 100;
            $proposal_line->proposal_markup_dollars = $markupDollars;

            $customerTotalPrice = $contractorCost + $markupDollars;
            $proposal_line->proposal_customer_price = $customerTotalPrice;

            $customerPricePerUnit = round($customerTotalPrice / $qty, 2);
            $proposal_line->proposal_customer_price_per_unit = $customerPricePerUnit;
        } else if ($field === 'proposal_contractor_cost_total') {
            $qty = $proposal_line->proposal_item_billing_quantity;
            $unitPrice = round($value / $qty, 2);
            $proposal_line->proposal_unit_price = $unitPrice;

            $markupPercent = $proposal_line->proposal_item_markup_percent;
            $markupDollars = 0;
            if ($markupPercent) {
                $markupDollars = round($value * $markupPercent / 100, 2);
            }
            $proposal_line->proposal_markup_dollars = $markupDollars;

            $customerTotalPrice = $value + $markupDollars;
            $proposal_line->proposal_customer_price = $customerTotalPrice;

            $customerPricePerUnit = round($customerTotalPrice / $qty, 2);
            $proposal_line->proposal_customer_price_per_unit = $customerPricePerUnit;
        } else if ($field === 'proposal_markup_dollars') {
            $qty = $proposal_line->proposal_item_billing_quantity;
            $contractorCost = $proposal_line->proposal_contractor_cost_total;
            $markupPercent = round($value / $contractorCost * 100, 2);
            $proposal_line->proposal_item_markup_percent = $markupPercent;

            $customerPrice = $contractorCost + $value;
            $proposal_line->proposal_customer_price = $customerPrice;

            $customerPricePerUnit = round($customerPrice / $qty, 2);
            $proposal_line->proposal_customer_price_per_unit = $customerPricePerUnit;
        } else if ($field === 'proposal_customer_price') {
            $qty = $proposal_line->proposal_item_billing_quantity;
            $customerPricePerUnit = round($value / $qty, 2);
            $proposal_line->proposal_customer_price_per_unit = $customerPricePerUnit;

            $markupDollars = $value - $proposal_contractor_cost_total;
            $proposal_line->proposal_markup_dollars = $markupDollars;

            if ($proposal_contractor_cost_total !== '0.00') {
                $markupPercent = round($markupDollars / $proposal_contractor_cost_total * 100, 2);
                $proposal_line->proposal_item_markup_percent = $markupPercent;
            }
        } else if ($field === 'proposal_customer_price_per_unit') {
            $qty = $proposal_line->proposal_item_billing_quantity;
            $customerPrice = round($value * $qty, 2);
            $proposal_line->proposal_customer_price = $customerPrice;

            $markupDollars = $proposal_contractor_cost_total + $customerPrice;
            $proposal_line->proposal_markup_dollars = $markupDollars;

            if ($proposal_contractor_cost_total !== '0.00') {
                $markupPercent = round($markupDollars / $proposal_contractor_cost_total * 100, 2);
                $proposal_line->proposal_item_markup_percent = $markupPercent;
            }
        }

        $proposal_line->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Proposal line is updated successfully'
        ]);
    }


    // update proposalitem info
    public function update_proposal_info(Request $request)
    {
        $proposal_id = $request->proposal_id;
        $val = $request->val;
        $position = $request->position;

        $proposal = UserProposals::find($proposal_id);
        $proposal[$position] = $val;
        $proposal->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Proposal Info is updated successfully'
        ]);
    }


    public function update_proposal_sidebar_status(Request $request)
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


    public function send_proposal_email(Request $request)
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
            Mail::send('mails.customer-portal-proposal-mail', $data, function ($messages) use ($customer_info, $from) {
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
        $proposal_name = $request->proposal_name;

        $record = [
            'user_id' => $this->user_id,
            'project_id' => $project_id,
            'proposal_name' => $proposal_name
        ];
        $new_proposal = UserProposals::create($record);
        $proposal_id = $new_proposal->id;

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

                    $proposal_default = $sort_val;
                    $preg = explode(' ', $proposal_default, 2);
                    if ($preg[0] !== "") {
                        $proposal_item_number = $preg[0];
                        $proposal_item_description = $preg[1];
                        $proposal_item = UserProposalItem::where('user_id', $this->user_id)->where('proposal_standard_item_number', $proposal_item_number)
                            ->where('proposal_standard_item_description', $proposal_item_description)->first();
                        if (isset($proposal_item)) {
                            $proposal_group_number = $proposal_item->proposal_standard_item_group_number;
                            $proposal_uom = $proposal_item->proposal_standard_item_uom;
                            $proposal_markup_percent = $proposal_item->proposal_standard_item_default_markup_percent;
                            $proposal_text = $proposal_item->proposal_standard_item_explanatory_text;
                            $proposal_notes = $proposal_item->proposal_standard_item_internal_notes;

                            $proposal_group = UserProposalGroup::where('user_id', $this->user_id)->where('proposal_standard_item_group_number', $proposal_group_number)->first();
                            $proposal_group_desc = $proposal_group->proposal_standard_item_group_description;

                            $proposal_ = new UserProposalDetail;
                            $proposal_->user_id = $this->user_id;
                            $proposal_->project_id = $project_id;
                            $proposal_->proposal_id = $proposal_id;
                            $proposal_->proposal_item_group_number = $proposal_group_number;
                            $proposal_->proposal_item_group_desc = $proposal_group_desc;
                            $proposal_->proposal_item_number = $proposal_item_number;
                            $proposal_->proposal_item_description = $proposal_item_description;
                            $proposal_->proposal_item_uom = $proposal_uom;
                            $proposal_->proposal_item_markup_percent = $proposal_markup_percent;
                            $proposal_->proposal_customer_scope_explanation = $proposal_text;
                            $proposal_->proposal_internal_notes = $proposal_notes;

                            $proposal_->proposal_unit_price = $contractor_cost;
                            $proposal_markup_dollars = $contractor_cost * $proposal_markup_percent / 100;
                            $proposal_->proposal_markup_dollars = $proposal_markup_dollars;
                            // the total cost of all items in the SS assigned to this invoice item
                            $proposal_->proposal_contractor_cost_total = $contractor_cost;
                            // contractor_cost_total is the total total contractor cost plus markup dollars
                            $proposal_contractor_cost_total = $contractor_cost + $proposal_markup_dollars;
                            $proposal_->proposal_customer_price = $proposal_contractor_cost_total;
                            $proposal_->proposal_customer_price_per_unit = $proposal_contractor_cost_total;

                            $proposal_->save();
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
