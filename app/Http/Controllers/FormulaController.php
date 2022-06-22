<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectShare;
use App\Models\UserFormula;
use App\Models\UserQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class FormulaController extends Controller
{
    protected $user_id = 0;

    public function __construct()
    {
        $this->user_id = Session::get('user_id');
    }

    public function index()
    {
        $page_info = ['name' => 'Stored Calculations'];

        $sharedProjectId = ProjectShare::where('share_receiver_user_id', $this->user_id)->pluck('share_project_number');
        $sharedProjects = Project::whereIn('id', $sharedProjectId)->orderBy('project_name')->get(); // shared projects
        $privateProjects = Project::where('user_id', $this->user_id)->orderBy('project_name')->get(); // private
        $projects = get_project_list($privateProjects, $sharedProjects);

        $question = UserQuestion::where('user_id', $this->user_id)->orderBy('question')->get();
        $pre_defined_calculations = UserFormula::where('user_id', $this->user_id)->orderBy('calculation_name')->get();
        $calculations = UserFormula::where('user_id', $this->user_id)->orderBy('calculation_name')->paginate(1);
        $formula_params = [];
        if (isset($calculations[0]->formula_body)) {
            $formula_params = json_decode($calculations[0]->formula_body, true) ? json_decode($calculations[0]->formula_body, true) : [];
        }

        return view('formula.show-formula', compact(
            'calculations',
            'projects',
            'page_info',
            'question',
            'pre_defined_calculations',
            'formula_params'
        ));
    }

    // get data after add/update/delete
    public function get_data(Request $request)
    {
        $question = UserQuestion::where('user_id', $this->user_id)->orderBy('question')->get();
        $pre_defined_calculations = UserFormula::where('user_id', $this->user_id)->orderBy('calculation_name')->get();
        $calculations = UserFormula::where('user_id', $this->user_id)->orderBy('calculation_name')->paginate(1);
        $formula_params = [];
        if (isset($calculations[0]->formula_body)) {
            $formula_params = json_decode($calculations[0]->formula_body, true) ? json_decode($calculations[0]->formula_body, true) : [];
        }
        $view_data = view('formula.pagination', compact('calculations', 'formula_params', 'question', 'pre_defined_calculations'))->render();

        return response()->json([
            'status' => 'success',
            'message' => 'Data fetched successfully!',
            'data' => [
                'view_data' => $view_data,
                'formula_params' => $formula_params
            ]
        ]);
    }

    // get data by next/prev
    public function fetch(Request $request)
    {
        $question = UserQuestion::where('user_id', $this->user_id)->orderBy('question')->get();
        $pre_defined_calculations = UserFormula::where('user_id', $this->user_id)->orderBy('calculation_name')->get();
        $calculations = UserFormula::where('user_id', $this->user_id)->orderBy('calculation_name')->paginate(1);
        $formula_params = [];
        if (isset($calculations[0]->formula_body)) {
            $formula_params = json_decode($calculations[0]->formula_body, true) ? json_decode($calculations[0]->formula_body, true) : [];
        }
        $view_data = view('formula.pagination', compact('calculations', 'formula_params', 'question', 'pre_defined_calculations'))->render();

        return response()->json([
            'status' => 'success',
            'message' => 'Data fetched successfully!',
            'data' => [
                'view_data' => $view_data,
                'formula_params' => $formula_params
            ]
        ]);
    }

    // get formula by id
    public function get_formula_by_id(Request $request)
    {
        $question = UserQuestion::where('user_id', $this->user_id)->orderBy('question')->get();
        $pre_defined_calculations = UserFormula::where('user_id', $this->user_id)->orderBy('calculation_name')->get();
        $calculations = UserFormula::where('user_id', $this->user_id)->orderBy('calculation_name')->paginate(1);
        $formula_params = [];
        if (isset($calculations[0]->formula_body)) {
            $formula_params = json_decode($calculations[0]->formula_body, true) ? json_decode($calculations[0]->formula_body, true) : [];
        }
        $view_data = view('formula.pagination', compact('calculations', 'formula_params', 'question', 'pre_defined_calculations'))->render();

        return response()->json([
            'status' => 'success',
            'message' => 'Data fetched successfully!',
            'data' => [
                'view_data' => $view_data,
                'formula_params' => $formula_params
            ]
        ]);
    }

    // get tree data
    public function get_stored_formula_tree(Request $request)
    {
        $formulas = UserFormula::where('user_id', $this->user_id)->orderBy('calculation_name')->get()->toArray();
        $tree_data = get_stored_formula_tree($formulas);
        return response()->json($tree_data);
    }

    public function store(Request $request)
    {
        $check_exist_question = UserQuestion::where('user_id', $this->user_id)->where('question', $request->aquestion)
            ->where('type', $request->add_question_type)->count();
        if ($check_exist_question) {
            return response()->json([
                'status' => 'error',
                'message' => 'Question already exist'
            ]);
        } else {
            UserQuestion::updateOrCreate(
                [
                    'id' => $request->id
                ],
                [
                    'user_id' => $this->user_id,
                    'question' => $request->aquestion,
                    'notes' => $request->adesc,
                    'type' => $request->add_question_type
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Question is added successfully'
        ]);
    }

    // update stored formula
    public function update(Request $request)
    {
        $formula = UserFormula::find($request->id);
        if ($formula->calculation_name === $request->calculation_name) {
            $formula->formula_body = $request->formula_body;
            $formula->save();
        } else {
            $check_duplicated = UserFormula::where('user_id', $this->user_id)->where('calculation_name', $request->calculation_name)->count();
            if ($check_duplicated) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Formula name already exist'
                ]);
            } else {
                $formula->calculation_name = $request->calculation_name;
                $formula->formula_body = $request->formula_body;
                $formula->save();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Updated successfully'
        ]);
    }

    public function destroy($id)
    {
        UserFormula::find($id)->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Formula deleted successfully!'
        ]);
    }
}
