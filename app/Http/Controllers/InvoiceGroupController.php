<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectShare;
use App\Models\UserInvoiceDetail;
use App\Models\UserInvoiceGroup;
use App\Models\UserInvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class InvoiceGroupController extends Controller
{
    protected $user_id = 0;

    public function __construct()
    {
        $this->user_id = Session::get('user_id');
    }

    public function index()
    {
        $sharedProjectId = ProjectShare::where('share_receiver_user_id', $this->user_id)->pluck('share_project_number');
        $sharedProjects = Project::whereIn('id', $sharedProjectId)->orderBy('project_name')->get(); // shared projects
        $privateProjects = Project::where('user_id', $this->user_id)->orderBy('project_name')->get(); // private
        $projects = get_project_list($privateProjects, $sharedProjects);

        $invoice_group = UserInvoiceGroup::where('user_id', $this->user_id)->orderBy(DB::raw('invoice_standard_item_group_number+0'))->paginate(1);
        $page_info = ['name' => 'Invoice Groups'];

        return view('invoice_group.show-invoicegroup', compact('invoice_group', 'projects', 'page_info'));
    }

    // get data after add/update/delete
    public function getdata(Request $request)
    {
        $invoice_group = UserInvoiceGroup::where('user_id', $this->user_id)->orderBy(DB::raw('invoice_standard_item_group_number+0'))->paginate(1);
        return view('invoice_group.pagination', compact('invoice_group'))->render();
    }

    // get data by next/prev
    public function fetch(Request $request)
    {
        $invoice_group = UserInvoiceGroup::where('user_id', $this->user_id)->orderBy(DB::raw('invoice_standard_item_group_number+0'))->paginate(1);
        return view('invoice_group.pagination', compact('invoice_group'))->render();
    }

    // get invoicegroup by id
    public function get_invoicegroup_by_id(Request $request)
    {
        $invoice_group = UserInvoiceGroup::where('user_id', $this->user_id)->orderBy(DB::raw('invoice_standard_item_group_number+0'))->paginate(1);
        return view('invoice_group.pagination', compact('invoice_group'))->render();
    }

    // get tree data
    public function get_invoicegroup_tree(Request $request)
    {
        $data = UserInvoiceGroup::where('user_id', $this->user_id)->orderBy(DB::raw('invoice_standard_item_group_number+0'))->get()->toArray();
        $tree_data = get_invoice_group_tree($data);
        return response()->json($tree_data);
    }

    public function store(Request $request)
    {
        $check_exist_invoicegroup = UserInvoiceGroup::where('user_id', $this->user_id)->where('invoice_standard_item_group_number', $request->ainvoicegroup)->count();
        if ($check_exist_invoicegroup) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invoice group number already exist'
            ]);
        } else {
            UserInvoiceGroup::updateOrCreate(
                [
                    'id' => $request->id
                ],
                [
                    'user_id' => $this->user_id,
                    'invoice_standard_item_group_number' => $request->ainvoicegroup,
                    'invoice_standard_item_group_description' => $request->adesc,
                    'is_folder' => $request->afolder ? 1 : 0
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Data inserted successfully'
            ]);
        }
    }

    // update desc and folder
    public function update_desc_folder(Request $request)
    {
        $invoice_group = UserInvoiceGroup::find($request->id);
        $invoice_group->invoice_standard_item_group_description = $request->adesc;
        $invoice_group->is_folder = $request->afolder;
        $invoice_group->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully'
        ]);
    }

    // renumbering
    public function renumbering(Request $request)
    {
        $updated_invoicegroup_number = $request->updated_invoicegroup_number;
        $check_duplicated = UserInvoiceGroup::where('user_id', $this->user_id)->where('invoice_standard_item_group_number', $updated_invoicegroup_number)->count();
        if ($check_duplicated) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invoice group number already exist'
            ]);
        } else {
            $invoice_group = UserInvoiceGroup::find($request->id);

            UserInvoiceDetail::where('user_id', $this->user_id)
                ->where('invoice_item_group_number', $invoice_group->invoice_standard_item_group_number)
                ->update(array('invoice_item_group_number' => $updated_invoicegroup_number));

            UserInvoiceItem::where('user_id', $this->user_id)->where('invoice_standard_item_group_number', $invoice_group->invoice_standard_item_group_number)
                ->update(array('invoice_standard_item_group_number' => $updated_invoicegroup_number));

            $invoice_group->invoice_standard_item_group_number = $updated_invoicegroup_number;
            $invoice_group->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Invoice group number updated successfully'
            ]);
        }
    }

    public function destroy($id)
    {
        $invoice_group = UserInvoiceGroup::find($id);
        UserInvoiceItem::where('user_id', $this->user_id)->where('invoice_standard_item_group_number', $invoice_group->invoice_standard_item_group_number)->delete();
        $invoice_group->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Data deleted successfully!'
        ]);
    }
}
