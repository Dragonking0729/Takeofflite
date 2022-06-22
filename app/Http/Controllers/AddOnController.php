<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectShare;
use App\Models\UserAddons;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AddOnController extends Controller
{
    protected $user_id = 0;

    public function __construct()
    {
        $this->user_id = Session::get('user_id');
    }


    public function index()
    {
        $page_info = ['name' => 'Add Ons'];

        $sharedProjectId = ProjectShare::where('share_receiver_user_id', $this->user_id)->pluck('share_project_number');
        $sharedProjects = Project::whereIn('id', $sharedProjectId)->orderBy('project_name')->get(); // shared projects
        $privateProjects = Project::where('user_id', $this->user_id)->orderBy('project_name')->get(); // private
        $projects = get_project_list($privateProjects, $sharedProjects);

        $add_ons = UserAddons::where('user_id', $this->user_id)->orderBy('addon_name')->paginate(1);

        return view('add_on.show-add-on', compact('add_ons', 'projects', 'page_info'));
    }

    // get data after add/update/delete
    public function getdata(Request $request)
    {
        $add_ons = UserAddons::where('user_id', $this->user_id)->orderBy('addon_name')->paginate(1);
        return view('add_on.pagination', compact('add_ons'))->render();
    }


    // get data by next/prev
    public function fetch(Request $request)
    {
        $add_ons = UserAddons::where('user_id', $this->user_id)->orderBy('addon_name')->paginate(1);
        return view('add_on.pagination', compact('add_ons'))->render();
    }

    // get by id
    public function get_addon_by_id(Request $request)
    {
        $add_ons = UserAddons::where('user_id', $this->user_id)->orderBy('addon_name')->paginate(1);
        return view('add_on.pagination', compact('add_ons'))->render();
    }

    // get tree data
    public function get_addon_tree(Request $request)
    {
        $add_ons = UserAddons::where('user_id', $this->user_id)->orderBy('addon_name')->get()->toArray();
        $tree_data = get_addon_tree($add_ons);
        return response()->json($tree_data);
    }

    public function store(Request $request)
    {
        $check_exist = UserAddons::where('user_id', $this->user_id)->where('addon_name', $request->aadd_on_name)->count();
        if ($check_exist) {
            return response()->json([
                'status' => 'error',
                'message' => 'Already exist'
            ]);
        } else {
            UserAddons::updateOrCreate(
                [
                    'id' => $request->id
                ],
                [
                    'user_id' => $this->user_id,
                    'addon_name' => $request->aadd_on_name,
                    'addon_method' => $request->aadd_on_calc_method,
                    'addon_category' => $request->aadd_on_calc_category,
                    'addon_value' => $request->aadd_on_calc_value
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Add-on is created successfully'
        ]);
    }

    // update
    public function update(Request $request)
    {
        $addons = UserAddons::find($request->id);
        if ($addons->addon_name === $request->addon_name) {
            $addons->addon_method = $request->addon_method;
            $addons->addon_category = $request->addon_category;
            $addons->addon_value = $request->addon_value;
            $addons->save();
        } else {
            $check_exist = UserAddons::where('user_id', $this->user_id)->where('addon_name', $request->addon_name)->count();
            if ($check_exist) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Add-ons already exist'
                ]);
            } else {
                $addons->addon_name = $request->addon_name;
                $addons->addon_method = $request->addon_method;
                $addons->addon_category = $request->addon_category;
                $addons->addon_value = $request->addon_value;
                $addons->save();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully'
        ]);
    }

    public function destroy($id)
    {
        UserAddons::find($id)->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Deleted successfully!'
        ]);
    }
}
