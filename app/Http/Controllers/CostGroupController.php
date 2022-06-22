<?php

namespace App\Http\Controllers;

use App\Models\AssemblyItem;
use App\Models\ProjectShare;
use App\Models\UserCostGroup;
use App\Models\Project;
use App\Models\UserCostItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CostGroupController extends Controller
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

        $cost_group = UserCostGroup::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->paginate(1);
        $page_info = ['name' => 'Cost Groups'];

        return view('costgroup.show-costgroup', compact('cost_group', 'projects', 'page_info'));
    }

    // get data after add/update/delete
    public function getdata(Request $request)
    {
        $cost_group = UserCostGroup::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->paginate(1);
        return view('costgroup.pagination', compact('cost_group'))->render();
    }

    // get data by next/prev
    public function fetch(Request $request)
    {
        $cost_group = UserCostGroup::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->paginate(1);
        return view('costgroup.pagination', compact('cost_group'))->render();
    }

    // get costgroup by id
    public function get_costgroup_by_id(Request $request)
    {
        $cost_group = UserCostGroup::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->paginate(1);
        return view('costgroup.pagination', compact('cost_group'))->render();
    }

    // get tree data
    public function get_costgroup_tree(Request $request)
    {
        $data = UserCostGroup::where('user_id', $this->user_id)->orderBy(DB::raw('cost_group_number+0'))->get()->toArray();
        $tree_data = get_cost_group_tree($data);
        return response()->json($tree_data);
    }

    public function store(Request $request)
    {
        $check_exist_costgroup = UserCostGroup::where('user_id', $this->user_id)->where('cost_group_number', $request->acostgroup)->count();
        if ($check_exist_costgroup) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cost group number already exist'
            ]);
        } else {
            UserCostGroup::updateOrCreate(
                [
                    'id' => $request->id
                ],
                [
                    'user_id' => $this->user_id,
                    'cost_group_number' => $request->acostgroup,
                    'cost_group_desc' => $request->adesc,
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
        $cost_group = UserCostGroup::find($request->id);
        $cost_group->cost_group_desc = $request->adesc;
        $cost_group->is_folder = $request->afolder;
        $cost_group->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully'
        ]);
    }

    // renumbering
    public function renumbering(Request $request)
    {
        $updated_costgroup_number = $request->updated_costgroup_number;
        $check_duplicated = UserCostGroup::where('user_id', $this->user_id)->where('cost_group_number', $updated_costgroup_number)->count();
        if ($check_duplicated) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cost group number already exist'
            ]);
        } else {
            $cost_group = UserCostGroup::find($request->id);

            AssemblyItem::where('user_id', $this->user_id)
                ->where('item_cost_group_number', $cost_group->cost_group_number)
                ->update(array('item_cost_group_number' => $updated_costgroup_number));

            UserCostItem::where('user_id', $this->user_id)->where('cost_group_number', $cost_group->cost_group_number)
                ->update(array('cost_group_number' => $updated_costgroup_number));

            $cost_group->cost_group_number = $updated_costgroup_number;
            $cost_group->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Cost group number updated successfully'
            ]);
        }
    }

    public function destroy($id)
    {
        $cost_group = UserCostGroup::find($id);
        UserCostItem::where('user_id', $this->user_id)->where('cost_group_number', $cost_group->cost_group_number)->delete();
        $cost_group->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Data deleted successfully!'
        ]);
    }
}
