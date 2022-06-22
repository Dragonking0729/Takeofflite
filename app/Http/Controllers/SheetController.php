<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectPdfSheet;
use App\Models\ProjectSetting;
use App\Models\ProjectShare;
use App\Models\SheetObject;
use App\Models\Spreadsht;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SheetController extends Controller
{
    protected $user_id = 0;
    protected $page_name = 0;

    public function __construct()
    {
        $this->user_id = Session::get('user_id');
        $this->page_name = 'Digitizer';
    }

    private function get_feet_inch($val)
    {
        $feet = (int)($val / 12);
        $inch = round(($val - $feet * 12), 2);
        $feet_inch = $feet . "' " . $inch . '"';
        return $feet_inch;
    }

    // get stored sheet object list by id
    private function get_sheet_object_list_by_id($sheetId)
    {
        $sheet_objects = SheetObject::where('sheet_id', $sheetId)->get()->toArray();
        $tree_data = [];
        $updated_data = [];
        $measure_id = '';
        if (count($sheet_objects)) {
            foreach ($sheet_objects as $item) {
                if ($item['is_measurement']) {
                    $measure_id = $item['id'];
                    $updated_data[$measure_id][] = $item;
                } else {
                    $updated_data[$measure_id][] = $item;
                }
            }

            foreach ($sheet_objects as $item) {
                if ($item['is_measurement']) {
                    $measure_name = $item['measure_name'];

                    // get measurement children - segments
                    $segments = [];
                    $segment_ids = "";
                    $total_area = 0;
                    $total_perimeter = 0;
                    $total_count = 0;
                    $formated_total_perimeter = '';
                    $formated_total_area = '';
                    $childs = $updated_data[$item['id']];
                    foreach ($childs as $child) {
                        if (empty($child['is_measurement'])) {
                            $segment_name = $child['measure_name'];
                            $perimeter = '';
                            $area = '';
                            if (isset($child['perimeter'])) {
                                $total_perimeter += $child['perimeter'];
                                $perimeter = $this->get_feet_inch($child['perimeter']);

                                if (isset($child['area'])) {
                                    $total_area += $child['area'];
                                    $area = $this->get_feet_inch($child['area']);
                                }
                            } else { // count
                                $total_count++;
                            }

                            $segment_ids .= $child['object_id'] . ",";

                            $segments[] = [
                                "id" => "{$child['object_id']}",
                                "text" => $segment_name,
                                "perimeter" => $perimeter,
                                "perimeter_val" => $child['perimeter'],
                                "area" => $area,
                                "area_val" => $child['area']
                            ];

                        }
                    }

                    if ($total_perimeter) $formated_total_perimeter = $this->get_feet_inch($total_perimeter);
                    if ($total_area) $formated_total_area = $this->get_feet_inch($total_area);

                    $tree_data[] = [
                        "id" => "measurement_{$item['id']}",
                        "text" => $measure_name,
                        "segments" => $segments,
                        "segment_ids" => rtrim($segment_ids, ", "),
                        "total_area" => $total_area,
                        "formated_total_area" => $formated_total_area,
                        "total_perimeter" => $total_perimeter,
                        "formated_total_perimeter" => $formated_total_perimeter,
                        "total_count" => $total_count,
                    ];
                }
            }
        }
        return $tree_data;
    }

    // get stored sheet objects
    private function get_sheet_objects($sheetId)
    {
        $sheet_objects = [];
        $info = SheetObject::where('sheet_id', $sheetId)->get();
        if (count($info)) {
            foreach ($info as $item) {
                if ($item['info']) {
                    array_push($sheet_objects, json_decode($item['info'], true));
                }
            }
        }

        return $sheet_objects;
    }


    public function show($project_id, $sheet_id, $segment_id = '')
    {
        $sharedProjectId = ProjectShare::where('share_receiver_user_id', $this->user_id)->pluck('share_project_number');
        $sharedProjects = Project::whereIn('id', $sharedProjectId)->orderBy('project_name')->get(); // shared projects
        $privateProjects = Project::where('user_id', $this->user_id)->orderBy('project_name')->get(); // private
        $projects = get_project_list($privateProjects, $sharedProjects);

        $project_name = Project::find($project_id)->project_name;
        $page_info = array(
            'project_id' => $project_id,
            'project_name' => $project_name,
            'name' => 'Digitizer',
            'sheet_id' => $sheet_id
        );
        $sheet = ProjectPdfSheet::find($sheet_id);
        // drawing objects
        $sheet_objects = $this->get_sheet_objects($sheet_id);
        // left sheet sidebar tree data
        $sheet_object_list = $this->get_sheet_object_list_by_id($sheet_id);
        $measurement_name_list = SheetObject::where('sheet_id', $sheet_id)->where('user_id', $this->user_id)->pluck('measure_name')->toArray();
        // page setting
        $is_sidebar_open = 1;
        $ss_setting_count = ProjectSetting::where('user_id', $this->user_id)->where('project_id', $project_id)->where('page_name', $this->page_name)->get();
        if (count($ss_setting_count)) {
            $is_sidebar_open = $ss_setting_count[0]->is_sidebar_open;
        }
        return view('sheet.index', compact('projects', 'page_info', 'sheet',
            'sheet_objects', 'sheet_object_list', 'measurement_name_list', 'segment_id', 'is_sidebar_open'));

    }

    // create new measurement
    public function create_measurement(Request $request)
    {
        if ($request->ajax()) {
            $sheet_id = $request->sheet_id;
            // dd($sheet_id);
            $name = $request->data['name'];
            $color = $request->data['color'];

            $measurement = new SheetObject;
            $measurement->user_id = $this->user_id;
            $measurement->sheet_id = $sheet_id;
            $measurement->measure_name = $name;
            $measurement->color = $color;
            $measurement->is_measurement = 1;
            $measurement->save();

            return response()->json([
                'id' => $measurement->id,
                'status' => 'success',
                'message' => 'Measurement created successfully'
            ]);
        }
    }


    // add new point
    public function add_point(Request $request)
    {
        if ($request->ajax()) {
            $sheet_id = $request->sheet_id;
            $name = $request->data['name'];
            $color = $request->data['color'];
            $object_id = $request->data['object_id'];
            $info = $request->data['info'];

            $measurement = new SheetObject;
            $measurement->user_id = $this->user_id;
            $measurement->sheet_id = $sheet_id;
            $measurement->measure_name = $name;
            $measurement->object_id = $object_id;
            $measurement->color = $color;
            $measurement->is_measurement = 0;
            $measurement->info = $info;
            $measurement->save();

            return response()->json([
                'id' => $measurement->object_id,
                'status' => 'success',
                'message' => 'Point created successfully'
            ]);
        }
    }


    // add area
    public function add_area(Request $request)
    {
        if ($request->ajax()) {
            $sheet_id = $request->sheet_id;
            $name = $request->data['name'];
            $color = $request->data['color'];
            $object_id = $request->data['object_id'];
            $perimeter = $request->data['perimeter'];
            $area = $request->data['area'];
            $info = $request->data['info'];

            $measurement = new SheetObject;
            $measurement->user_id = $this->user_id;
            $measurement->sheet_id = $sheet_id;
            $measurement->measure_name = $name;
            $measurement->object_id = $object_id;
            $measurement->color = $color;
            $measurement->is_measurement = 0;
            $measurement->info = $info;
            $measurement->perimeter = $perimeter;
            $measurement->area = $area;
            $measurement->save();

            return response()->json([
                'id' => $measurement->object_id,
                'status' => 'success',
                'message' => 'Area created successfully'
            ]);
        }
    }


    // add polyline
    public function add_polyline(Request $request)
    {
        if ($request->ajax()) {
            $sheet_id = $request->sheet_id;
            $name = $request->data['name'];
            $color = $request->data['color'];
            $object_id = $request->data['object_id'];
            $perimeter = $request->data['perimeter'];
            $info = $request->data['info'];

            $measurement = new SheetObject;
            $measurement->user_id = $this->user_id;
            $measurement->sheet_id = $sheet_id;
            $measurement->measure_name = $name;
            $measurement->object_id = $object_id;
            $measurement->color = $color;
            $measurement->is_measurement = 0;
            $measurement->info = $info;
            $measurement->perimeter = $perimeter;
            $measurement->save();

            return response()->json([
                'id' => $measurement->object_id,
                'status' => 'success',
                'message' => 'Polyline created successfully'
            ]);
        }
    }


    // remove object
    public function remove_object(Request $request)
    {
        if ($request->ajax()) {
            $ids = $request->data['ids'];
            foreach ($ids as $item) {
                $id = $item['id'];
                if ($item['segment']) {
                    // remove segment object
                    SheetObject::where('object_id', $id)->delete();
                } else {
                    // remove measurement object
                    SheetObject::where('id', $id)->delete();
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Removed successfully'
            ]);
        }
    }


    // remove orphaned measurement
    public function remove_orphaned_measurement(Request $request)
    {
        $id = $request->measurement_id;
        SheetObject::find($id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Removed successfully'
        ]);
    }


    // move point, area, line object
    public function move_object(Request $request)
    {
        if ($request->ajax()) {
            // move sheet
            if (isset($request->data['type'])) { // move_sheet
                $figures = $request->data['figures_data'];
                foreach ($figures as $figure) {
                    $figure_id = $figure['figure_id'];
                    $x = floatval($figure['x']);
                    $y = floatval($figure['y']);
                    if ($figure['figure_type'] === 'draw2d_shape_basic_Image') {
                        // update sheet position
                        $sheet = ProjectPdfSheet::find($figure_id);
                        $sheet->x = $x;
                        $sheet->y = $y;
                        $sheet->save();
                    } else {
                        // update figures position
                        $object = SheetObject::where('object_id', $figure_id)->first();
                        $old_info = json_decode($object->info);
                        $old_info->x = $x;
                        $old_info->y = $y;

                        // move area
                        if (isset($figure['verticles'])) {
                            $updated_vertices = [];
                            foreach ($figure['verticles'] as $item) {
                                $temp['x'] = floatval($item['x']);
                                $temp['y'] = floatval($item['y']);
                                array_push($updated_vertices, $temp);
                            }
                            $old_info->vertices = $updated_vertices;
                        }

                        // move line
                        if (isset($figure['vertex'])) {
                            $updated_vertex = [];
                            foreach ($figure['vertex'] as $item) {
                                $temp['x'] = floatval($item['x']);
                                $temp['y'] = floatval($item['y']);
                                array_push($updated_vertex, $temp);
                            }
                            $old_info->vertex = $updated_vertex;
                        }

                        $updated_info = $old_info;
                        $object->info = json_encode($updated_info);

                        $object->save();
                    }
                }
            } else { // move object
                $object_id = $request->data['object_id'];
                $new_x = $request->data['newX'];
                $new_y = $request->data['newY'];

                $object = SheetObject::where('object_id', $object_id)->first();
                $old_info = json_decode($object->info);
                $old_info->x = floatval($new_x);
                $old_info->y = floatval($new_y);

                // move area
                if (isset($request->data['verticles'])) {
                    $updated_vertices = [];
                    foreach ($request->data['verticles'] as $item) {
                        $temp['x'] = floatval($item['x']);
                        $temp['y'] = floatval($item['y']);
                        array_push($updated_vertices, $temp);
                    }
                    $old_info->vertices = $updated_vertices;
                }

                // move line
                if (isset($request->data['vertex'])) {
                    $updated_vertex = [];
                    foreach ($request->data['vertex'] as $item) {
                        $temp['x'] = floatval($item['x']);
                        $temp['y'] = floatval($item['y']);
                        array_push($updated_vertex, $temp);
                    }
                    $old_info->vertex = $updated_vertex;
                }

                $updated_info = $old_info;
                $object->info = json_encode($updated_info);

                $object->save();
            }


            return response()->json([
                'status' => 'success',
                'message' => 'Object moved successfully'
            ]);
        }
    }


    // add new point
    public function add_line(Request $request)
    {
        if ($request->ajax()) {
            $sheet_id = $request->sheet_id;
            $name = $request->data['name'];
            $color = $request->data['color'];
            $object_id = $request->data['object_id'];
            $perimeter = $request->data['perimeter'];
            $info = $request->data['info'];

            $measurement = new SheetObject;
            $measurement->user_id = $this->user_id;
            $measurement->sheet_id = $sheet_id;
            $measurement->measure_name = $name;
            $measurement->object_id = $object_id;
            $measurement->perimeter = $perimeter;
            $measurement->color = $color;
            $measurement->is_measurement = 0;
            $measurement->info = $info;
            $measurement->save();

            return response()->json([
                'id' => $measurement->object_id,
                'status' => 'success',
                'message' => 'Line created successfully'
            ]);
        }
    }


    // update line (perimeter, vertex)
    public function update_line(Request $request)
    {
        if ($request->ajax()) {
            $object_id = $request->data['object_id'];
            $new_x = $request->data['newX'];
            $new_y = $request->data['newY'];
            $perimeter = $request->data['perimeter'];
            $perimeterTxt = $request->data['perimeterTxt'];

            $object = SheetObject::where('object_id', $object_id)->first();
            $old_info = json_decode($object->info);
            $old_info->x = floatval($new_x);
            $old_info->y = floatval($new_y);
            $object->perimeter = floatval($perimeter);

            if (isset($request->data['vertex'])) {
                $updated_vertex = [];
                foreach ($request->data['vertex'] as $item) {
                    $temp['x'] = floatval($item['x']);
                    $temp['y'] = floatval($item['y']);
                    array_push($updated_vertex, $temp);
                }
                $old_info->vertex = $updated_vertex;
                $old_info->userData->perimeter = $perimeter;
                $old_info->userData->perimeterTxt = $perimeterTxt;
            }

            $updated_info = $old_info;
            $object->info = json_encode($updated_info);

            $object->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Line updated successfully'
            ]);
        }
    }


    // update area (perimeter, vertex, area)
    public function update_area(Request $request)
    {
        if ($request->ajax()) {
            $object_id = $request->data['object_id'];
            $perimeter = $request->data['perimeter'];
            $perimeterTxt = $request->data['perimeterTxt'];

            $object = SheetObject::where('object_id', $object_id)->first();
            $old_info = json_decode($object->info);
            $object->perimeter = floatval($perimeter);

            $area = null;
            $areaTxt = null;
            if (isset($request->data['area'])) {
                $area = $request->data['area'];
                $areaTxt = $request->data['areaTxt'];
                $object->area = floatval($area);
            }

            if (isset($request->data['vertex'])) {
                $updated_vertex = [];
                foreach ($request->data['vertex'] as $item) {
                    $temp['x'] = floatval($item['x']);
                    $temp['y'] = floatval($item['y']);
                    array_push($updated_vertex, $temp);
                }

                $old_info->userData->perimeter = $perimeter;
                $old_info->userData->perimeterTxt = $perimeterTxt;
                if (isset($request->data['area'])) {
                    $old_info->vertices = $updated_vertex;
                    $old_info->userData->area = $area;
                    $old_info->userData->areaTxt = $areaTxt;
                } else {
                    $old_info->vertex = $updated_vertex;
                }
            }

            $updated_info = $old_info;
            $object->info = json_encode($updated_info);

            $object->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Area updated successfully'
            ]);
        }
    }


    // update measurement object
    public function update_measurement(Request $request)
    {
        if ($request->ajax()) {
            $object_id = $request->data['object_id'];
            $updated_area = $request->data['area'];
            $updated_perimeter = $request->data['perimeter'];
            $updated_info = $request->data['info'];

            $object = SheetObject::where('object_id', $object_id)->first();
            $object->info = $updated_info;
            $object->area = $updated_area;
            $object->perimeter = $updated_perimeter;

            $object->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Object updated successfully'
            ]);
        }
    }


    // get measurement and update TOQ
    public function get_measurement_TOQ(Request $request)
    {
        if ($request->ajax()) {
            $TOQ = $request->data['TOQ'];
            $ss_item_id = $request->data['SSItemId'];

            $ss = Spreadsht::find($ss_item_id);
            $ss->ss_item_takeoff_quantity = $TOQ;
            $ss->save();

            return response()->json([
                'status' => 'success',
                'message' => 'TOQ updated successfully'
            ]);
        }
    }


    // update zoom
    public function update_zoom(Request $request)
    {
        if ($request->ajax()) {
            $zoom = $request->zoom;
            $sheet_id = $request->sheet_id;
            $sheet = ProjectPdfSheet::find($sheet_id);
            $sheet->zoom = $zoom;
//            $sheet->x = $request->x;
//            $sheet->y = $request->y;
            $sheet->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Zoom updated successfully'
            ]);
        }
    }


    // set scale
    public function set_scale(Request $request)
    {
        $sheet_id = $request->sheet_id;
        $feet = $request->data['feet'];
        $inch = $request->data['inch'];
        $scale = $request->data['scale'];

        $sheet = ProjectPdfSheet::find($sheet_id);
        $sheet->feet = $feet;
        $sheet->inch = $inch;
        $sheet->scale = $scale;
        $sheet->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Scale updated successfully'
        ]);
    }


    // remove all the measurement objects
    public function remove_all_segments(Request $request)
    {
        $sheet_id = $request->sheet_id;
        SheetObject::where('sheet_id', $sheet_id)->delete();
        return back()->with('success', 'Removed successfully');
    }

    public function update_sheet_sidebar_status(Request $request)
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
}
