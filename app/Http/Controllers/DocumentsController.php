<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectPdfSheet;
use App\Models\ProjectSetting;
use App\Models\ProjectShare;
use App\Models\SheetObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Org_Heigl\Ghostscript\Ghostscript;
use Spatie\PdfToImage\Pdf;

class DocumentsController extends Controller
{

    protected $user_id = 0;
    protected $page_name = 'Documents';

    public function __construct()
    {
        $this->user_id = Session::get('user_id');
        $this->page_name = 'Documents';
    }


    // get stored sheet object list
    private function get_sheet_object_list($projectId)
    {
        $sheet_objects = SheetObject::get()->toArray();
        $tree_data = [];
        $updated_sheet_objects = [];
        if (count($sheet_objects)) {
            foreach ($sheet_objects as $item) {
                $updated_sheet_objects[$item['sheet_id']][] = $item;
            }

            foreach ($updated_sheet_objects as $sheets) {
                $sheet = ProjectPdfSheet::find($sheets[0]['sheet_id']);
                if ($sheet && $sheet->project_id == $projectId) {
                    $sheet_name = $sheet->sheet_name;
                    $updated_data = [];
                    $measurement_data = [];
                    $measure_id = '';
                    foreach ($sheets as $item) {
                        if ($item['is_measurement']) {
                            $measure_id = $item['id'];
                            $updated_data[$measure_id][] = $item;
                        } else {
                            $updated_data[$measure_id][] = $item;
                        }
                    }

                    foreach ($sheets as $item) {
                        if ($item['is_measurement']) {
                            $measure_name = $item['measure_name'];

                            // get measurement children - segments
                            $segments = [];
                            $childs = $updated_data[$item['id']];
                            foreach ($childs as $child) {
                                if (empty($child['is_measurement'])) {
                                    $segment_name = $child['measure_name'];
                                    $perimeter = '';
                                    $area = '';
                                    if (isset($child['perimeter'])) {
                                        $perimeter_inch = $child['perimeter'] % 12;
                                        $perimeter_feet = (int)($child['perimeter'] / 12);
                                        $perimeter = $perimeter_feet . "' " . $perimeter_inch . '"';

                                        if (isset($child['area'])) {
                                            $area_inch = $child['area'] % 12;
                                            $area_feet = (int)($child['area'] / 12);
                                            $area = $area_feet . "' " . $area_inch . '"';
                                        }
                                    }

                                    $segments[] = [
                                        "id" => "{$child['object_id']}",
                                        "text" => $segment_name,
                                        "sheet_id" => $sheet->id,
                                        "perimeter" => $perimeter,
                                        "area" => $area
                                    ];

                                }
                            }

                            $measurement_data[] = [
                                "id" => "measurement_{$item['id']}",
                                "text" => $measure_name,
                                "segments" => $segments
                            ];
                        }
                    }
                    $tree_data[] = [
                        "id" => "sheet_{$sheet->id}",
                        "text" => $sheet_name,
                        "measurements" => $measurement_data
                    ];
                }
            }

        }
        return $tree_data;
    }


    private function get_sheets($project_id, $category)
    {
        $sheets = ProjectPdfSheet::where('user_id', $this->user_id)
            ->where('project_id', $project_id)
            ->where('category', $category)
            ->orderBy('sheet_order')
            ->orderBy('id')
            ->get();
        return $sheets;
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
     * @param int $id
     * @param string $category
     * @return \Illuminate\Http\Response
     */
    public function show($id, $category = 'plan')
    {
        $sharedProjectId = ProjectShare::where('share_receiver_user_id', $this->user_id)->pluck('share_project_number');
        $sharedProjects = Project::whereIn('id', $sharedProjectId)->orderBy('project_name')->get(); // shared projects
        $privateProjects = Project::where('user_id', $this->user_id)->orderBy('project_name')->get(); // private
        $projects = get_project_list($privateProjects, $sharedProjects);

        $project_name = Project::find($id)->project_name;
        $page_info = array(
            'project_id' => $id,
            'project_name' => $project_name,
            'name' => $this->page_name
        );
        $sheet_object_list = $this->get_sheet_object_list($id);

        // get sheets
        $sheets = $this->get_sheets($id, $category);

        $is_sidebar_open = 1;
        $ss_setting_count = ProjectSetting::where('user_id', $this->user_id)->where('project_id', $id)->where('page_name', $this->page_name)->get();
        if (count($ss_setting_count)) {
            $is_sidebar_open = $ss_setting_count[0]->is_sidebar_open;
        }

        return view('documents.index', compact('projects', 'project_name', 'page_info', 'sheets',
            'sheet_object_list', 'is_sidebar_open', 'category'));
    }


    // file upload
    public function file_upload(Request $request)
    {
        // file upload
        $MAX_UPLOAD_FILE_SIZE = 1048576 * 10; // 10 MB
        $project_id = $request->project_id;
        $category = $request->category;
        $exist_max_sheet_order = ProjectPdfSheet::where('user_id', $this->user_id)->where('project_id', $project_id)->where('category', $category)->max('sheet_order');
        if ($category == 'plan') {
            $fileSize = $request->file('pdf')->getSize();
            if ($fileSize > $MAX_UPLOAD_FILE_SIZE) {
                return back()->with('error', "We're sorry but we only allow file sizes of 10MB or less. Please either split you file or use an optimizer tool to shrink it");
            }
            Ghostscript::setGsPath(env('GS_PATH', 'C:\Program Files\gs\gs9.52\bin\gswin64c.exe'));
            set_time_limit($MAX_UPLOAD_FILE_SIZE);
            // upload pdf
            $file_type = $request->pdf->extension();
            if ($file_type == 'pdf') {
                $rand_time = time();
                // upload pdf
                $pdfName = $rand_time . '__' . $request->pdf->getClientOriginalName();
                $path = public_path('pdf/' . $this->user_id);
                $request->pdf->move($path, $pdfName);
                $pdf_path = $path . '/' . $pdfName;

                // convert pdf to png
                $pdf = new Pdf($pdf_path);
                $sheet_order = $exist_max_sheet_order;
                foreach (range(1, $pdf->getNumberOfPages()) as $pageNumber) {
                    $imageName = $rand_time . '__' . $pageNumber;
                    $path_to_images = '/pdf/' . $this->user_id . '/' . $imageName . '.png';
                    $output_images = public_path() . $path_to_images;
                    $pdf->setPage($pageNumber)
                        ->setOutputFormat('png')
                        ->saveImage($output_images);

                    $image_data = $pdf->getImageData($output_images);
                    $image_width_height = $image_data->getImageGeometry();

                    $image_width = $image_width_height['width'] / 2;
                    $image_height = $image_width_height['height'] / 2;
                    if ($image_width_height['width'] > 1600 && $image_width_height['width'] <= 2600) {
                        $image_width = round($image_width_height['width'] / 3, 2);
                        $image_height = round($image_width_height['height'] / 3, 2);
                    } else if ($image_width_height['width'] > 2600 && $image_width_height['width'] <= 4000) {
                        $image_width = round($image_width_height['width'] / 4, 2);
                        $image_height = round($image_width_height['height'] / 4, 2);
                    } else if ($image_width_height['width'] > 4000) {
                        $image_width = round($image_width_height['width'] / 5, 2);
                        $image_height = round($image_width_height['height'] / 5, 2);
                    }

                    $sheet_order++;
                    $sheet_image = array(
                        'user_id' => $this->user_id,
                        'project_id' => $project_id,
                        'sheet_name' => $imageName,
                        'pdf_path' => '/pdf/' . $this->user_id . '/' . $pdfName,
                        'file' => $path_to_images,
                        'sheet_order' => $sheet_order,
                        'x' => 2,
                        'y' => 2,
                        'width' => $image_width,
                        'height' => $image_height,
                        'category' => $category
                    );
                    ProjectPdfSheet::create($sheet_image);
                }

                return back()
                    ->with('success', 'File uploaded successfully');
            } else {
                return back()
                    ->with('error', 'File type is incorrect.');
            }
        } else if ($category == 'picture') {
            // upload picture
            $fileSize = $request->file('picture')->getSize();
            if ($fileSize > $MAX_UPLOAD_FILE_SIZE) {
                return back()->with('error', "We're sorry but we only allow file sizes of 10MB or less. Please either split you file or use an optimizer tool to shrink it");
            }
            $file_type = $request->picture->extension();
            if ($file_type == 'jpeg' || $file_type == 'jpg' || $file_type == 'png' || $file_type == 'gif' || $file_type == 'tif' || $file_type == 'tiff' || $file_type == 'bmp') {
                $name = time() . '__' . $request->picture->getClientOriginalName();
                $dir_path = public_path('document_picture/' . $this->user_id);
                $request->picture->move($dir_path, $name);
                $exist_max_sheet_order++;
                $file = array(
                    'user_id' => $this->user_id,
                    'project_id' => $project_id,
                    'sheet_name' => $name,
                    'pdf_path' => '/document_picture/' . $this->user_id . '/' . $name,
                    'file' => '/document_picture/' . $this->user_id . '/' . $name,
                    'sheet_order' => $exist_max_sheet_order,
                    'x' => 2,
                    'y' => 2,
                    'width' => 0,
                    'height' => 0,
                    'category' => $category
                );
                ProjectPdfSheet::create($file);
                return back()
                    ->with('success', 'Pictures are uploaded successfully');
            } else {
                return back()
                    ->with('error', 'File type is not supported.');
            }
        } else if ($category == 'video') {
            // upload video
            $fileSize = $request->file('video')->getSize();
            if ($fileSize > $MAX_UPLOAD_FILE_SIZE) {
                return back()->with('error', "We're sorry but we only allow file sizes of 10MB or less. Please either split you file or use an optimizer tool to shrink it");
            }
            $name = time() . '__' . $request->video->getClientOriginalName();
            $dir_path = public_path('document_video/' . $this->user_id);
            $request->video->move($dir_path, $name);
            $exist_max_sheet_order++;
            $file = array(
                'user_id' => $this->user_id,
                'project_id' => $project_id,
                'sheet_name' => $name,
                'pdf_path' => '/document_video/' . $this->user_id . '/' . $name,
                'file' => '/document_video/' . $this->user_id . '/' . $name,
                'sheet_order' => $exist_max_sheet_order,
                'x' => 2,
                'y' => 2,
                'width' => 0,
                'height' => 0,
                'category' => $category
            );
            ProjectPdfSheet::create($file);
            return back()
                ->with('success', 'Videos are uploaded successfully');
        } else {
            // upload other files
            $fileSize = $request->file('other')->getSize();
            if ($fileSize > $MAX_UPLOAD_FILE_SIZE) {
                return back()->with('error', "We're sorry but we only allow file sizes of 10MB or less. Please either split you file or use an optimizer tool to shrink it");
            }
            $name = time() . '__' . $request->other->getClientOriginalName();
            $dir_path = public_path('document_other/' . $this->user_id);
            $request->other->move($dir_path, $name);
            $exist_max_sheet_order++;
            $file = array(
                'user_id' => $this->user_id,
                'project_id' => $project_id,
                'sheet_name' => $name,
                'pdf_path' => '/document_other/' . $this->user_id . '/' . $name,
                'file' => '/document_other/' . $this->user_id . '/' . $name,
                'sheet_order' => $exist_max_sheet_order,
                'x' => 2,
                'y' => 2,
                'width' => 0,
                'height' => 0,
                'category' => $category
            );
            ProjectPdfSheet::create($file);
            return back()
                ->with('success', 'Files are uploaded successfully');
        }
    }


    // remove sheet
    public function remove_sheet(Request $request)
    {
        $sheet = ProjectPdfSheet::find($request->sheet_id);
        $project_id = $sheet->project_id;

        // delete pdf
        if ($sheet->pdf_path) {
            $pdf_path = public_path() . $sheet->pdf_path;
            if (file_exists($pdf_path)) {
                unlink($pdf_path);
            }
        }

        // delete image
        if ($sheet->file) {
            $image_path = public_path() . $sheet->file;
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        ProjectPdfSheet::where('id', $request->sheet_id)->delete();
        SheetObject::where('sheet_id', $request->sheet_id)->delete();
        return back()->with('success', 'Deleted successfully');
    }


    // remove multiple sheets
    public function remove_multiple_sheets(Request $request)
    {
        $ids = $request->ids;
        foreach ($ids as $id) {
            $sheet = ProjectPdfSheet::find($id);

            // delete pdf
            if ($sheet->pdf_path) {
                $pdf_path = public_path() . $sheet->pdf_path;
                if (file_exists($pdf_path)) {
                    unlink($pdf_path);
                }
            }

            // delete image
            if ($sheet->file) {
                $image_path = public_path() . $sheet->file;
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }

            ProjectPdfSheet::where('id', $id)->delete();
            SheetObject::where('sheet_id', $id)->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Selection deleted'
        ]);
    }


    // update sheet name
    public function update_sheet_name(Request $request)
    {
        $sheet_id = $request->sheet_id;
        $sheet_name = $request->sheet_name;

        $sheet = ProjectPdfSheet::find($sheet_id);
        $sheet->sheet_name = $sheet_name;
        $sheet->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Sheet name is updated successfully'
        ]);
    }


    // update sheet position by draggable
    public function update_sheet_order(Request $request)
    {
        $order = $request->order;
        for ($position = 1; $position <= count($order); $position++) {
            $sheet_id = $order[$position - 1];
            $sheet = ProjectPdfSheet::find($sheet_id);
            $sheet->sheet_order = $position;
            $sheet->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Sheet order is updated successfully'
        ]);
    }


    public function update_documents_sidebar_status(Request $request)
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
        //
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
