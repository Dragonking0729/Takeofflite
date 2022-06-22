<?php

namespace App\Http\Controllers;

use App\Models\AssemblyItem;
use App\Models\Project;
use App\Models\ProjectShare;
use App\Models\Uom;
use App\Models\UserInvoiceDetail;
use App\Models\UserInvoiceGroup;
use App\Models\UserInvoiceItem;
use App\Models\UserFormula;
use App\Models\UserQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class InvoiceItemController extends Controller
{
    protected $user_id = 0;

    public function __construct()
    {
        $this->user_id = Session::get('user_id');
    }

    public function index()
    {
        $page_info = ['name' => 'Invoice Items'];
        $sharedProjectId = ProjectShare::where('share_receiver_user_id', $this->user_id)->pluck('share_project_number');
        $sharedProjects = Project::whereIn('id', $sharedProjectId)->orderBy('project_name')->get(); // shared projects
        $privateProjects = Project::where('user_id', $this->user_id)->orderBy('project_name')->get(); // private
        $projects = get_project_list($privateProjects, $sharedProjects);
        // get invoice item data
        $invoice_item = UserInvoiceItem::where('user_id', $this->user_id)
            ->orderBy(DB::raw('invoice_standard_item_group_number+0'))
            ->orderBy(DB::raw('invoice_standard_item_number+0'))
            ->paginate(1);
        // get group desc
        $group_desc = '';
        if ($invoice_item->count()) {
            $group_number = $invoice_item[0]->invoice_standard_item_group_number;
            $invoice_group = UserInvoiceGroup::where('user_id', $this->user_id)->where('invoice_standard_item_group_number', $group_number)->first();
            $group_desc = $invoice_group->invoice_standard_item_group_description;
        }

        // get invoice group, item tree data
        $all_invoicegroup = UserInvoiceGroup::where('user_id', $this->user_id)->orderBy(DB::raw('invoice_standard_item_group_number+0'))->get()->toArray();
        $all_invoiceitems = UserInvoiceItem::where('user_id', $this->user_id)->orderBy(DB::raw('invoice_standard_item_group_number+0'))->orderBy(DB::raw('invoice_standard_item_number+0'))->get()->toArray();
        $invoicegroup_tree = get_invoice_group_tree($all_invoicegroup);
        $invoicegroup_tree = json_encode($invoicegroup_tree);
        $invoiceitem_tree = get_invoice_item_tree($all_invoicegroup, $all_invoiceitems);
        $invoiceitem_tree = json_encode($invoiceitem_tree);

        $uom = Uom::orderBy('uom_name')->get();

        return view('invoiceitem.show-invoiceitem', compact(
            'projects',
            'invoice_item',
            'group_desc',
            'invoiceitem_tree',
            'uom',
            'invoicegroup_tree',
            'page_info'
        ));
    }

    // get data by next/prev
    public function fetch(Request $request)
    {
        $invoice_item = UserInvoiceItem::where('user_id', $this->user_id)
            ->orderBy(DB::raw('invoice_standard_item_group_number+0'))
            ->orderBy(DB::raw('invoice_standard_item_number+0'))
            ->paginate(1);

        $group_number = $invoice_item[0]->invoice_standard_item_group_number;
        $invoice_group = UserInvoiceGroup::where('user_id', $this->user_id)->where('invoice_standard_item_group_number', $group_number)->first();
        $group_desc = $invoice_group->invoice_standard_item_group_description;

        $uom = Uom::orderBy('uom_name')->get();

        $view_data = view('invoiceitem.default-invoiceitem', compact(
            'invoice_item',
            'group_desc',
            'uom'
        ))->render();

        return response()->json([
            'status' => 'success',
            'message' => 'Data fetched successfully!',
            'data' => [
                'view_data' => $view_data
            ]
        ]);
    }

    public function store(Request $request)
    {
        $check_exist_invoice_standard_item_number = UserInvoiceItem::where('user_id', $this->user_id)
            ->where('invoice_standard_item_group_number', $request->ainvoicegroup)->where('invoice_standard_item_number', $request->aitem_number)->count();
        if ($check_exist_invoice_standard_item_number) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invoice item number already exist'
            ]);
        } else {
            UserInvoiceItem::updateOrCreate(
                [
                    'id' => $request->id
                ],
                [
                    'user_id' => $this->user_id,
                    'invoice_standard_item_group_number' => $request->ainvoicegroup,
                    'invoice_standard_item_number' => $request->aitem_number,
                    'invoice_standard_item_description' => $request->aitem_desc,
                    'invoice_standard_item_uom' => $request->atakeoff_uom,
                    'invoice_standard_item_default_markup_percent' => $request->amarkup_percent,
                    'invoice_standard_item_explanatory_text' => $request->aexplanatory_text,
                    'invoice_standard_item_internal_notes' => $request->ainternal_notes
                ]
            );
            $all_invoicegroup = UserInvoiceGroup::where('user_id', $this->user_id)->orderBy(DB::raw('invoice_standard_item_group_number+0'))->get()->toArray();
            $all_invoiceitems = UserInvoiceItem::where('user_id', $this->user_id)->orderBy(DB::raw('invoice_standard_item_group_number+0'))->orderBy(DB::raw('invoice_standard_item_number+0'))->get()->toArray();
            $invoiceitem_tree = get_invoice_item_tree($all_invoicegroup, $all_invoiceitems);
            $invoiceitem_tree = json_encode($invoiceitem_tree);

            return response()->json([
                'status' => 'success',
                'message' => 'Data inserted successfully',
                'tree_data' => $invoiceitem_tree
            ]);
        }
    }


    // renumbering
    public function renumbering(Request $request)
    {
        $is_exist_invoicegroup = UserInvoiceGroup::where('user_id', $this->user_id)
            ->where('invoice_standard_item_group_number', $request->updated_invoicegroup_number)->count();
        if ($is_exist_invoicegroup) {
            $is_exist_invoice_standard_item_number = UserInvoiceItem::where('user_id', $this->user_id)
                ->where('invoice_standard_item_group_number', $request->updated_invoicegroup_number)
                ->where('invoice_standard_item_number', $request->updated_invoiceitem_number)->count();

            if ($is_exist_invoice_standard_item_number) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Item number already exist'
                ]);
            } else {
                //  update invoice item number
                $updated_invoicegroup_number = $request->updated_invoicegroup_number;
                $updated_invoice_standard_item_number = $request->updated_invoiceitem_number;

                $invoice_item = UserInvoiceItem::find($request->id);
                UserInvoiceDetail::where('user_id', $this->user_id)
                    ->where('invoice_item_group_number', $invoice_item->invoice_standard_item_group_number)
                    ->where('invoice_item_number', $invoice_item->invoice_standard_item_number)
                    ->update(array('invoice_item_group_number' => $updated_invoicegroup_number, 'invoice_item_number' => $updated_invoice_standard_item_number));

                $invoice_item->invoice_standard_item_group_number = $updated_invoicegroup_number;
                $invoice_item->invoice_standard_item_number = $updated_invoice_standard_item_number;
                $invoice_item->save();

                $all_invoicegroup = UserInvoiceGroup::where('user_id', $this->user_id)->orderBy(DB::raw('invoice_standard_item_group_number+0'))->get()->toArray();
                $all_invoiceitems = UserInvoiceItem::where('user_id', $this->user_id)->orderBy(DB::raw('invoice_standard_item_group_number+0'))->orderBy(DB::raw('invoice_standard_item_number+0'))->get()->toArray();
                $invoiceitem_tree = get_invoice_item_tree($all_invoicegroup, $all_invoiceitems);
                $invoiceitem_tree = json_encode($invoiceitem_tree);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Item number updated successfully',
                    'tree_data' => $invoiceitem_tree
                ]);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid invoice group number'
            ]);
        }
    }


    public function destroy($id)
    {
        UserInvoiceItem::find($id)->delete();
        $invoice_item = UserInvoiceItem::where('user_id', $this->user_id)
            ->orderBy(DB::raw('invoice_standard_item_group_number+0'))
            ->orderBy(DB::raw('invoice_standard_item_number+0'))
            ->paginate(1);
        $group_number = $invoice_item[0]->invoice_standard_item_group_number;
        $invoice_group = UserInvoiceGroup::where('user_id', $this->user_id)->where('invoice_standard_item_group_number', $group_number)->first();
        $group_desc = $invoice_group->invoice_standard_item_group_description;
        $uom = Uom::orderBy('uom_name')->get();

        $view_data = view('invoiceitem.default-invoiceitem', compact(
            'invoice_item',
            'group_desc',
            'uom'
        ))->render();

        $all_invoicegroup = UserInvoiceGroup::where('user_id', $this->user_id)->orderBy(DB::raw('invoice_standard_item_group_number+0'))->get()->toArray();
        $all_invoiceitems = UserInvoiceItem::where('user_id', $this->user_id)->orderBy(DB::raw('invoice_standard_item_group_number+0'))->orderBy(DB::raw('invoice_standard_item_number+0'))->get()->toArray();
        $invoiceitem_tree = get_invoice_item_tree($all_invoicegroup, $all_invoiceitems);
        $invoiceitem_tree = json_encode($invoiceitem_tree);

        return response()->json([
            'status' => 'success',
            'message' => 'Data deleted successfully!',
            'data' => [
                'view_data' => $view_data,
                'tree_data' => $invoiceitem_tree
            ]
        ]);
    }


    // get invoice item by id
    public function get_invoiceitem_by_id(Request $request)
    {
        $invoice_item = UserInvoiceItem::where('user_id', $this->user_id)
            ->orderBy(DB::raw('invoice_standard_item_group_number+0'))
            ->orderBy(DB::raw('invoice_standard_item_number+0'))
            ->paginate(1);

        $group_number = $invoice_item[0]->invoice_standard_item_group_number;
        $invoice_group = UserInvoiceGroup::where('user_id', $this->user_id)->where('invoice_standard_item_group_number', $group_number)->first();
        $group_desc = $invoice_group->invoice_standard_item_group_description;

        $uom = Uom::orderBy('uom_name')->get();

        $view_data = view('invoiceitem.default-invoiceitem', compact(
            'invoice_item',
            'group_desc',
            'uom'
        ))->render();

        return response()->json([
            'status' => 'success',
            'message' => 'Data fetched successfully!',
            'data' => [
                'view_data' => $view_data
            ]
        ]);
    }


    // update invoice item
    public function update_invoiceitem(Request $request)
    {
        $invoice_item = UserInvoiceItem::find($request->data['id']);
        $invoice_item->invoice_standard_item_description = $request->data['item_desc'];
        $invoice_item->invoice_standard_item_uom = $request->data['takeoff_uom'];
        $invoice_item->invoice_standard_item_default_markup_percent = $request->data['markup_percent'];
        $invoice_item->invoice_standard_item_explanatory_text = $request->data['explanatory_text'];
        $invoice_item->invoice_standard_item_internal_notes = $request->data['internal_notes'];
        $invoice_item->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully'
        ]);
    }
}
