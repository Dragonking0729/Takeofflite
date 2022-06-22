<?php

namespace App\Http\Controllers;

use App\Mail\FCLinkShareEmail;
use App\Models\UserAppInfo;
use App\Models\Assembly;
use App\Models\AssemblyItem;
use App\Models\FlipModel;
use App\Models\Project;
use App\Models\ProjectPdfSheet;
use App\Models\ProjectSetting;
use App\Models\ProjectShare;
use App\Models\SheetObject;
use App\Models\Spreadsht;
use App\Models\Uom;
use App\Models\User;
use App\Models\UserCostGroup;
use App\Models\UserCostItem;
use App\Models\UserInvoiceDetail;
use App\Models\UserInvoiceGroup;
use App\Models\UserInvoiceItem;
use App\Models\UserInvoices;
use App\Models\UserPreferences;
use App\Models\UserProposalDetail;
use App\Models\UserProposalGroup;
use App\Models\UserProposalItem;
use App\Models\UserProposals;
use App\Models\UserQuestion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\AtomApi;
use Exception;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;

class APIController extends Controller
{
    // get full address
    private function get_full_address($project)
    {
        $address_1 = $project->street_address_1 ? $project->street_address_1 . ' ' : '';
        $address_2 = $project->street_address_2 ? $project->street_address_2 . ' ' : '';
        $city = $project->city ? $project->city . ' ' : '';
        $state = $project->state ? $project->state . ' ' : '';
        $address = trim($address_1 . $address_2 . $city . $state);

        return $address;
    }

    // get address
    private function get_address($address_1, $address_2, $city, $state)
    {
        $address_1 = $address_1 ? $address_1 . ' ' : '';
        $address_2 = $address_2 ? $address_2 . ' ' : '';
        $city = $city ? $city . ' ' : '';
        $state = $state ? $state . ' ' : '';
        $address = trim($address_1 . $address_2 . $city . $state);

        return $address;
    }

    // get spreadsheet total value
    private function get_ss_total($user_id, $projectId)
    {
        //        $data = Spreadsht::where('user_id', $user_id)->where('project_id', $projectId)->get()->toArray();
        //        $total_labor = 0;
        //        $total_material = 0;
        //        $total_subcontract = 0;
        //        $total_other = 0;
        //        foreach ($data as $row) {
        //            $use_labor = $row['ss_use_labor'];
        //            $use_material = $row['ss_use_material'];
        //            $use_sub = $row['ss_use_sub'];
        //
        //            if ($use_labor)
        //                $total_labor += Round($row['ss_item_takeoff_quantity'] / $row['ss_labor_conversion_factor'] * $row['ss_labor_price'], 2);
        //            if ($use_material)
        //                $total_material += Round($row['ss_item_takeoff_quantity'] / $row['ss_material_conversion_factor'] * $row['ss_material_price'], 2);
        //            if ($use_sub)
        //                $total_subcontract += Round($row['ss_item_takeoff_quantity'] / $row['ss_subcontract_conversion_factor'] * $row['ss_subcontract_price'], 2);
        //            // $total_other += Round($row['ss_item_takeoff_quantity'] / $row['ss_other_conversion_factor'] * $row['ss_other_price'], 2);
        //        }
        //        $total_estimate = $total_labor + $total_material + $total_subcontract + $total_other;
        //
        //        return round($total_estimate, 2);

        $total_budget = 0;
        $data = Spreadsht::where('user_id', $user_id)->where('project_id', $projectId)->where('ss_is_qv', 1)->get()->toArray();
        foreach ($data as $row) {
            if ($row['ss_labor_conversion_factor'] == '0') {
                $row['ss_labor_conversion_factor'] = 1;
            }
            if ($row['ss_material_conversion_factor'] == '0') {
                $row['ss_material_conversion_factor'] = 1;
            }
            if ($row['ss_subcontract_conversion_factor'] == '0') {
                $row['ss_subcontract_conversion_factor'] = 1;
            }
            if ($row['ss_other_conversion_factor'] == '0') {
                $row['ss_other_conversion_factor'] = 1;
            }
            $labor_total = Round($row['ss_item_takeoff_quantity'] / $row['ss_labor_conversion_factor'] * $row['ss_labor_price'], 2);
            $material_total = Round($row['ss_item_takeoff_quantity'] / $row['ss_material_conversion_factor'] * $row['ss_material_price'], 2);
            $subcontract_total = Round($row['ss_item_takeoff_quantity'] / $row['ss_subcontract_conversion_factor'] * $row['ss_subcontract_price'], 2);
            $other_total = Round($row['ss_item_takeoff_quantity'] / $row['ss_other_conversion_factor'] * $row['ss_other_price'], 2);
            $total = $labor_total + $material_total + $subcontract_total + $other_total;
            $total_budget += $total;
        }

        return round($total_budget, 2);
    }

    // get sum of six value
    private function get_six_value($flip_info, $repair_cost)
    {
        $purchase_price = $flip_info->purchase_price ? $flip_info->purchase_price : 0;
        $acquisition_costs = $flip_info->acquisition_costs ? $flip_info->acquisition_costs : 0;
        $holding_costs = $flip_info->holding_costs ? $flip_info->holding_costs : 0;
        $selling_costs = $flip_info->selling_costs ? $flip_info->selling_costs : 0;
        $financing_costs = $flip_info->financing_costs ? $flip_info->financing_costs : 0;

        return $purchase_price + $acquisition_costs + $holding_costs + $selling_costs + $financing_costs + $repair_cost;
    }

    // calculate profit
    private function get_profit($flip_info, $six_value)
    {
        $arv = $flip_info->arv ? $flip_info->arv : 0;
        return $arv - $six_value;
    }

    // calculate min profit limit
    private function get_min_profit_limit($flip_info, $profit, $minAcceptableProfit)
    {
        $purchase_price = $flip_info->purchase_price ? $flip_info->purchase_price : 0;
        $minProfitLimit = $profit + $purchase_price - $minAcceptableProfit;
        $minProfitLimit = round($minProfitLimit, 2);
        return $minProfitLimit;
    }

    // check value exists in array object
    private function checkValueExistInArray($array, $value)
    {
        foreach ($array as $element) {
            if ($element->id == $value) {
                return $element;
            }
        }
        return false;
    }

    // update project ATOM property
    private function updateAtomProperty($projectId, $data)
    {
        $project = Project::find($projectId);

        $result = [
            'acreage' => isset($data['lot']['lotsize1']) ? $data['lot']['lotsize1'] : '',
            'lot_sf' => isset($data['lot']['lotsize2']) ? $data['lot']['lotsize2'] : '',
            'pool' => isset($data['lot']['pooltype']) ? $data['lot']['pooltype'] : '',
            'occupied' => isset($data['summary']['absenteeInd']) ? $data['summary']['absenteeInd'] : '',
            'property_class' => isset($data['summary']['propclass']) ? $data['summary']['propclass'] : '',
            'year_built' => isset($data['summary']['yearbuilt']) ? $data['summary']['yearbuilt'] : '',
            'heat_fuel_type' => isset($data['utilities']['heatingfuel']) ? $data['utilities']['heatingfuel'] : '',
            'heated_sf' => isset($data['building']['size']['bldgsize']) ? $data['building']['size']['bldgsize'] : '',
            'total_sf' => isset($data['building']['size']['livingsize']) ? $data['building']['size']['livingsize'] : '',
            'full_baths' => isset($data['building']['rooms']['bathsfull']) ? $data['building']['rooms']['bathsfull'] : '',
            'total_baths' => isset($data['building']['rooms']['bathstotal']) ? $data['building']['rooms']['bathstotal'] : '',
            'bath_fixtures' => isset($data['building']['rooms']['bathfixtures']) ? $data['building']['rooms']['bathfixtures'] : '',
            'bedrooms' => isset($data['building']['rooms']['beds']) ? $data['building']['rooms']['beds'] : '',
            'total_rooms' => isset($data['building']['rooms']['roomsTotal']) ? $data['building']['rooms']['roomsTotal'] : '',
            'basement_sf' => isset($data['building']['interior']['bsmtsize']) ? $data['building']['interior']['bsmtsize'] : '',
            'basement_finish' => isset($data['building']['interior']['bsmttype']) ? $data['building']['interior']['bsmttype'] : '',
            'property_condition' => isset($data['building']['construction']['condition']) ? $data['building']['construction']['condition'] : '',
            'exterior' => isset($data['building']['construction']['wallType']) ? $data['building']['construction']['wallType'] : '',
            'parking_sf_available' => isset($data['building']['parking']['prkgSize']) ? $data['building']['parking']['prkgSize'] : '',
            'parking_spaces' => isset($data['building']['parking']['prkgSpaces']) ? $data['building']['parking']['prkgSpaces'] : '',
            'architectural_style' => isset($data['building']['parking']['prkgSpaces']) ? $data['building']['parking']['prkgSpaces'] : '',
            'levels_stories' => isset($data['building']['summary']['levels']) ? $data['building']['summary']['levels'] : '',
            'year_built_last_modified' => isset($data['building']['summary']['yearbuilteffective']) ? $data['building']['summary']['yearbuilteffective'] : '',
        ];

        $project->update($result);

        return $result;
    }

    // get location
    private function get_geo_location($address)
    {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&key=AIzaSyAvd8WHv_GoEG5eaOCwPLku8JeQzNno7dA';
        $response = Http::get($url);
        $response = $response->json();
        $location = '';
        if ($response['status'] === "OK") {
            $location = $response['results'][0]['geometry']['location'];
        }
        return json_encode($location);
    }

    // populate user db when register
    private function populate_user_db($origin_user_id, $user_id)
    {
        Assembly::where('user_id', $user_id)->delete();
        AssemblyItem::where('user_id', $user_id)->delete();
        UserCostGroup::where('user_id', $user_id)->delete();
        UserCostItem::where('user_id', $user_id)->delete();
        UserQuestion::where('user_id', $user_id)->delete();
        Project::where('user_id', $user_id)->delete();
        ProjectPdfSheet::where('user_id', $user_id)->delete();
        UserProposalItem::where('user_id', $user_id)->delete();
        UserProposalGroup::where('user_id', $user_id)->delete();
        UserInvoiceGroup::where('user_id', $user_id)->delete();
        UserInvoiceItem::where('user_id', $user_id)->delete();
        FlipModel::where('user_id', $user_id)->delete();
        Spreadsht::where('user_id', $user_id)->delete();
        //        UserPreferences::where('user_id', $user_id)->delete();

        // insert Assembly data
        $data = Assembly::where('user_id', $origin_user_id)->get()->toArray();
        foreach ($data as $item) {
            $item['user_id'] = $user_id;
            Assembly::create($item);
        }

        // insert AssemblyItem data
        $data = AssemblyItem::where('user_id', $origin_user_id)->get()->toArray();
        foreach ($data as $item) {
            $item['user_id'] = $user_id;
            AssemblyItem::create($item);
        }

        // insert UserCostGroup data
        $data = UserCostGroup::where('user_id', $origin_user_id)->get()->toArray();
        foreach ($data as $item) {
            $item['user_id'] = $user_id;
            UserCostGroup::create($item);
        }

        // insert UserCostItem data
        $data = UserCostItem::where('user_id', $origin_user_id)->get()->toArray();
        foreach ($data as $item) {
            $item['user_id'] = $user_id;
            UserCostItem::create($item);
        }

        // insert UserQuestion data
        $data = UserQuestion::where('user_id', $origin_user_id)->get()->toArray();
        foreach ($data as $item) {
            $item['user_id'] = $user_id;
            UserQuestion::create($item);
        }

        // create sample project
        $data = Project::where('user_id', $origin_user_id)->get()->toArray();
        foreach ($data as $item) {
            $old_project_id = $item['id'];
            $item['user_id'] = $user_id;

            $created_project = Project::create($item);

            // insert ProjectPdfSheet data
            $project_id = $created_project->id;
            //            $sheets = ProjectPdfSheet::where('user_id', $origin_user_id)->get()->toArray();
            //            foreach ($sheets as $sheet) {
            //                $sheet['user_id'] = $user_id;
            //                $sheet['project_id'] = $project_id;
            //
            //                // copy image
            //                $origin_file_path = $sheet['file'];
            //                $new_file_path = str_replace('/' . $origin_user_id . '/', $user_id, '/' . $origin_file_path . '/');
            //                File::copy(public_path($origin_file_path), public_path($new_file_path));
            //                $sheet['file'] = $new_file_path;
            //
            //                // copy pdf
            //                $origin_pdf_path = $sheet['pdf_path'];
            //                $new_pdf_path = str_replace('/' . $origin_user_id . '/', $user_id, '/' . $origin_pdf_path . '/');
            //                $sheet['pdf_path'] = $new_pdf_path;
            //                File::copy(public_path($origin_pdf_path), public_path($new_pdf_path));
            //
            //                ProjectPdfSheet::create($sheet);
            //            }

            // insert Flip data
            $flips = FlipModel::where('user_id', $origin_user_id)->where('project_id', $old_project_id)->get()->toArray();
            foreach ($flips as $flip) {
                $flip['user_id'] = $user_id;
                $flip['project_id'] = $project_id;
                FlipModel::create($flip);
            }

            // insert Spreadsheet data
            $ss = Spreadsht::where('user_id', $origin_user_id)->where('project_id', $old_project_id)->get()->toArray();
            foreach ($ss as $s) {
                $s['user_id'] = $user_id;
                $s['project_id'] = $project_id;
                Spreadsht::create($s);
            }
        }

        // insert UserProposalGroup data
        $data = UserProposalGroup::where('user_id', $origin_user_id)->get()->toArray();
        foreach ($data as $item) {
            $item['user_id'] = $user_id;
            UserProposalGroup::create($item);
        }

        // insert ProposalItem data
        $data = UserProposalItem::where('user_id', $origin_user_id)->get()->toArray();
        foreach ($data as $item) {
            $item['user_id'] = $user_id;
            UserProposalItem::create($item);
        }

        // insert UserInvoiceGroup data
        $data = UserInvoiceGroup::where('user_id', $origin_user_id)->get()->toArray();
        foreach ($data as $item) {
            $item['user_id'] = $user_id;
            UserInvoiceGroup::create($item);
        }

        // insert UserInvoiceItem data
        $data = UserInvoiceItem::where('user_id', $origin_user_id)->get()->toArray();
        foreach ($data as $item) {
            $item['user_id'] = $user_id;
            UserInvoiceItem::create($item);
        }

        // insert UserPreferences data
        //        $data = UserPreferences::where('user_id', $origin_user_id)->get()->toArray();
        //        foreach ($data as $item) {
        //            $item['user_id'] = $user_id;
        //            UserPreferences::create($item);
        //        }
    }


    /**
     * Login & Register & Reset password API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $email = $request->email;
            $password = $request->password;

            $user = User::where('user_email', $email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'EMAIL NOT FOUND',
                ]);
            } else {
                if (Hash::check($password, $user->user_pass)) {
                    $data = [
                        'id' => $user->id,
                        'user_email' => $user->user_email,
                        'userStatus' => $user->user_status,
                    ];

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Login successful',
                        'data' => $data
                    ]);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Password is incorrect',
                    ]);
                }
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function register(Request $request)
    {
        try {
            $email = $request->email;
            $password = $request->password;
            $first_name = $request->firstName;
            $last_name = $request->lastName;
            $company = $request->company;
            $phone_number = $request->phoneNumber ? $request->phoneNumber : '';
            $display_name = trim($first_name . ' ' . $last_name);
            $plan = 'none';

            $userInfo = User::where('user_email', $email)->first();

            if ($userInfo) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email already existing',
                ]);
            } else {
                // create user to db
                $user_data = [
                    'user_email' => $email,
                    'user_pass' => Hash::make($password),
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'display_name' => $display_name,
                    'user_status' => 1, // trial
                    'company_name' => $company,
                    'phone' => $phone_number,
                    'stripe_id' => '',
                    'subscription_id' => '',
                    'plan' => $plan,
                    'billing_end_date' => '',
                    'app_only' => 1,
                ];

                $user = User::create($user_data);

                // populate user db
                $source_user_id = env('POPULATE_APP_USER_SOURCE_ID');
                $this->populate_user_db($source_user_id, $user->id);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Register successful',
                    'data' => $user->id
                ]);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage() . '.....' . $e->getLine());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function reset_password(Request $request)
    {
        try {
            $email = $request->email;
            $password = $request->password;

            $userInfo = User::where('user_email', $email)->first();

            if (!$userInfo) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid email',
                ]);
            } else {
                $userInfo->user_pass = Hash::make($password);
                $userInfo->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Password is updated successfully',
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * get project map & street view data
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_project_map_street(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $projectId = $request->jobId;

            $project = Project::find($projectId);
            $address = $this->get_full_address($project);
            $data = [
                'jobId' => $project->id,
                'jobName' => $project->project_name,
                'jobAddress' => $address,
                'jobLocation' => json_decode($project->geo_location),
                'postalCode' => $project->postal_code,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Project information',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Default price APIs
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    // get default price data
    public function get_price(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $uom = Uom::get()->pluck('uom_name');

            $query = "SELECT cost_items.`id`, cost_items.`item_desc`, cost_items.`subcontract_uom`, cost_items.`subcontract_price`
                FROM user_cost_items cost_items, (
                    SELECT item_cost_group_number, item_number
                        FROM user_assembly_item
                        WHERE user_id = '" . $user_id . "' AND assembly_number = (
                            SELECT assembly_number
                            FROM user_assembly
                            WHERE user_id = '" . $user_id . "' AND is_qv = '1')
                ) assem
                WHERE cost_items.user_id = '" . $user_id . "' AND assem.item_cost_group_number = cost_items.`cost_group_number` AND assem.item_number = cost_items.`item_number`";
            $data = DB::select(DB::raw($query));

            return response()->json([
                'status' => 'success',
                'message' => 'Default price data',
                'data' => [
                    'costItems' => $data,
                    'uom' => $uom
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // update price uom
    public function update_uom(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $id = $request->rowId; // id of user_cost_items
            $value = $request->value;

            $costItem = UserCostItem::find($id);
            $costItem->subcontract_uom = $value;
            $costItem->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Cost item subcontract UOM is updated successfully',
                'data' => []
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // update price
    public function update_price(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $id = $request->rowId; // id of user_cost_items
            $value = $request->value;

            $costItem = UserCostItem::find($id);
            $costItem->subcontract_price = $value;
            $costItem->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Cost item subcontract price is updated successfully',
                'data' => []
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get project list APIs
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_project(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $queryParam = trim($request->queryParam);

            //            $sharedProjectId = ProjectShare::where('share_receiver_user_id', $user_id)->pluck('share_project_number');
            $projectShare = ProjectShare::select('share_project_number', 'allow_edit')->where('share_receiver_user_id', $user_id)->get();

            $sharedProjectId = [];
            $permissionInfo = null;
            foreach ($projectShare as $item) {
                $sharedProjectId[] = $item->share_project_number;
                $permissionInfo[$item->share_project_number] = $item->allow_edit;
            }

            if ($queryParam) {
                $sharedProjects = Project::whereIn('id', $sharedProjectId)->where('project_name', 'like', '%' . $queryParam . '%')->orderBy('project_name')->get(); // shared projects
                $privateProjects = Project::where('user_id', $user_id)->where('project_name', 'like', '%' . $queryParam . '%')->orderBy('id', 'DESC')->get(); // private
            } else {
                $sharedProjects = Project::whereIn('id', $sharedProjectId)->orderBy('project_name')->get(); // shared projects
                $privateProjects = Project::where('user_id', $user_id)->orderBy('id', 'DESC')->get(); // private
            }

            $privateJobs = [];
            foreach ($privateProjects as $project) {
                $address = $this->get_full_address($project);
                $privateJobs[] = [
                    'jobId' => $project->id,
                    'jobName' => $project->project_name,
                    'jobAddress' => $address,
                    'jobLocation' => json_decode($project->geo_location),
                    'isShared' => 0,
                    'allowEdit' => 1,
                    'streetAddress1' => $project->street_address_1,
                    'streetAddress2' => $project->street_address_2,
                    'city' => $project->city,
                    'state' => $project->state,
                    'postalCode' => $project->postal_code,
                    'customerName' => $project->customer_name,
                    'customerPhone' => $project->customer_phone,
                ];
            }
            $sharedJobs = [];
            foreach ($sharedProjects as $project) {
                $address = $this->get_full_address($project);
                $sharedJobs[] = [
                    'jobId' => $project->id,
                    'jobName' => $project->project_name,
                    'jobAddress' => $address,
                    'jobLocation' => json_decode($project->geo_location),
                    'isShared' => 1,
                    'allowEdit' => $permissionInfo[$project->id],
                    'streetAddress1' => $project->street_address_1,
                    'streetAddress2' => $project->street_address_2,
                    'city' => $project->city,
                    'state' => $project->state,
                    'postalCode' => $project->postal_code,
                    'customerName' => $project->customer_name,
                    'customerPhone' => $project->customer_phone,
                ];
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Project list',
                'data' => [
                    'privateJobs' => $privateJobs,
                    'sharedJobs' => $sharedJobs,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function add_project(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $jobFormData = json_decode($request->formData);

            $address_1 = '';
            $address_2 = '';
            $city = '';
            $state = '';

            $formData = [];
            foreach ($jobFormData as $item) {
                if ($item->key === 'street_address_1') {
                    $address_1 = $item->value;
                } else if ($item->key === 'street_address_2') {
                    $address_2 = $item->value;
                } else if ($item->key === 'city') {
                    $city = $item->value;
                } else if ($item->key === 'state') {
                    $state = $item->value;
                }
                $formData[$item->key] = $item->value;
            }

            $address = $this->get_address($address_1, $address_2, $city, $state);
            if ($address) {
                $geo_location = $this->get_geo_location($address);
                $formData['geo_location'] = $geo_location;
            }
            $formData['user_id'] = $user_id;

            $project = Project::create($formData);
            $project = Project::find($project->id);

            return response()->json([
                'status' => 'success',
                'message' => 'Created successfully',
                'data' => [
                    'jobId' => $project->id,
                    'jobName' => $project->project_name,
                    'jobAddress' => $address,
                    'jobLocation' => json_decode($project->geo_location),
                    'allowEdit' => 1,
                    'isShared' => 0,
                    'streetAddress1' => $project->street_address_1,
                    'streetAddress2' => $project->street_address_2,
                    'city' => $project->city,
                    'state' => $project->state,
                    'postalCode' => $project->postal_code,
                    'customerName' => $project->customer_name,
                    'customerPhone' => $project->customer_phone,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update_project(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $jobFormData = json_decode($request->formData);
            $projectId = $request->jobId;
            $project = Project::find($projectId);

            $address_1 = '';
            $address_2 = '';
            $city = '';
            $state = '';

            foreach ($jobFormData as $item) {
                if ($item->key === 'street_address_1') {
                    $address_1 = $item->value;
                } else if ($item->key === 'street_address_2') {
                    $address_2 = $item->value;
                } else if ($item->key === 'city') {
                    $city = $item->value;
                } else if ($item->key === 'state') {
                    $state = $item->value;
                }
                $project[$item->key] = $item->value;
            }

            $address = $this->get_address($address_1, $address_2, $city, $state);
            if ($address) {
                $geo_location = $this->get_geo_location($address);
                $project['geo_location'] = $geo_location;
            }

            $project->save();
            $project = Project::find($projectId);

            return response()->json([
                'status' => 'success',
                'message' => 'Updated successfully',
                'data' => [
                    'jobId' => $project->id,
                    'jobName' => $project->project_name,
                    'jobAddress' => $address,
                    'jobLocation' => json_decode($project->geo_location),
                    'isShared' => 0,
                    'streetAddress1' => $project->street_address_1,
                    'streetAddress2' => $project->street_address_2,
                    'city' => $project->city,
                    'state' => $project->state,
                    'postalCode' => $project->postal_code,
                    'customerName' => $project->customer_name,
                    'customerPhone' => $project->customer_phone,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function remove_project(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $id = $request->jobId;

            Project::find($id)->delete();
            Spreadsht::where('project_id', $id)->delete();
            ProjectSetting::where('project_id', $id)->delete();
            $sheet_ids = ProjectPdfSheet::where('project_id', $id)->pluck('id');
            SheetObject::whereIn('sheet_id', $sheet_ids)->delete();
            ProjectPdfSheet::where('user_id', $user_id)->where('project_id', $id)->delete();
            UserInvoiceDetail::where('user_id', $user_id)->where('project_id', $id)->delete();
            UserInvoices::where('user_id', $user_id)->where('project_id', $id)->delete();
            UserProposalDetail::where('user_id', $user_id)->where('project_id', $id)->delete();
            UserProposals::where('user_id', $user_id)->where('project_id', $id)->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Removed successfully',
                'data' => []
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * get project details
     * @param Request $request
     * @return Illuminate\Http\JsonResponse
     */
    public function get_project_details(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $projectId = $request->jobId;

            $data = [];

            if (!$projectId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Job id is missing error',
                    'data' => $data
                ]);
            }

            // get job info
            $project = Project::find($projectId);
            $address = $this->get_full_address($project);
            $data = [
                'jobId' => $project->id,
                'jobName' => $project->project_name,
                'jobAddress' => $address,
                'jobLocation' => json_decode($project->geo_location),
                'useAtomAPI' => $project->use_atom_api,
                'detail' => [],
            ];

            // get job detail property
            $data['detail'][] = [
                'label' => 'Square Footage',
                'value' => $project->square_footage ? $project->square_footage : "0",
                'key' => 'square_footage'
            ];
            $data['detail'][] = [
                'label' => 'Acreage',
                'value' => $project->acreage,
                'key' => 'acreage'
            ];
            $data['detail'][] = [
                'label' => 'Lot SF',
                'value' => $project->lot_sf,
                'key' => 'lot_sf'
            ];
            $data['detail'][] = [
                'label' => 'Pool',
                'value' => $project->pool,
                'key' => 'pool'
            ];
            $data['detail'][] = [
                'label' => 'Occupied',
                'value' => $project->occupied,
                'key' => 'occupied'
            ];
            $data['detail'][] = [
                'label' => 'Property Class',
                'value' => $project->property_class,
                'key' => 'property_class'
            ];
            $data['detail'][] = [
                'label' => 'Year Built',
                'value' => $project->year_built,
                'key' => 'year_built'
            ];
            $data['detail'][] = [
                'label' => 'Heat fuel type',
                'value' => $project->heat_fuel_type,
                'key' => 'heat_fuel_type'
            ];
            $data['detail'][] = [
                'label' => 'Heated SF',
                'value' => $project->heated_sf,
                'key' => 'heated_sf'
            ];
            $data['detail'][] = [
                'label' => 'Total SF',
                'value' => $project->total_sf,
                'key' => 'total_sf'
            ];
            $data['detail'][] = [
                'label' => 'Full Baths',
                'value' => $project->full_baths,
                'key' => 'full_baths'
            ];
            $data['detail'][] = [
                'label' => 'Total Baths',
                'value' => $project->total_baths,
                'key' => 'total_baths'
            ];
            $data['detail'][] = [
                'label' => 'Bath Fixtures',
                'value' => $project->bath_fixtures,
                'key' => 'bath_fixtures'
            ];
            $data['detail'][] = [
                'label' => 'Bedrooms',
                'value' => $project->bedrooms,
                'key' => 'bedrooms'
            ];
            $data['detail'][] = [
                'label' => 'Total Rooms',
                'value' => $project->total_rooms,
                'key' => 'total_rooms'
            ];
            $data['detail'][] = [
                'label' => 'Basement SF',
                'value' => $project->basement_sf,
                'key' => 'basement_sf'
            ];
            $data['detail'][] = [
                'label' => 'Basement Finish',
                'value' => $project->basement_finish,
                'key' => 'basement_finish'
            ];
            $data['detail'][] = [
                'label' => 'Property Condition',
                'value' => $project->property_condition,
                'key' => 'property_condition'
            ];
            $data['detail'][] = [
                'label' => 'Exterior',
                'value' => $project->exterior,
                'key' => 'exterior'
            ];
            $data['detail'][] = [
                'label' => 'Parking SF Available',
                'value' => $project->parking_sf_available,
                'key' => 'parking_sf_available'
            ];
            $data['detail'][] = [
                'label' => 'Parking Spaces',
                'value' => $project->parking_spaces,
                'key' => 'parking_spaces'
            ];
            $data['detail'][] = [
                'label' => 'Architectural Style',
                'value' => $project->architectural_style,
                'key' => 'architectural_style'
            ];
            $data['detail'][] = [
                'label' => 'Levels/Stories',
                'value' => $project->levels_stories,
                'key' => 'levels_stories'
            ];
            $data['detail'][] = [
                'label' => 'Year Built/Last Modified',
                'value' => $project->year_built_last_modified,
                'key' => 'year_built_last_modified'
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Project detail data from custom',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update_use_atom(Request $request)
    {
        try {
            $data = [];
            $user_id = $request->bearerToken();
            $projectId = $request->jobId;
            $useATOMAPI = $request->useATOMAPI;

            $project = Project::find($projectId);

            $project->use_atom_api = $useATOMAPI;
            $project->save();
            if ($useATOMAPI) {
                $data[] = [
                    'label' => 'Square Footage',
                    'value' => 0,
                    'key' => 'square_footage'
                ];
                $data[] = [
                    'label' => 'Acreage',
                    'value' => 0,
                    'key' => 'acreage'
                ];
                $data[] = [
                    'label' => 'Lot SF',
                    'value' => 0,
                    'key' => 'lot_sf'
                ];
                $data[] = [
                    'label' => 'Pool',
                    'value' => 0,
                    'key' => 'pool'
                ];
                $data[] = [
                    'label' => 'Occupied',
                    'value' => 0,
                    'key' => 'occupied'
                ];
                $data[] = [
                    'label' => 'Property Class',
                    'value' => 0,
                    'key' => 'property_class'
                ];
                $data[] = [
                    'label' => 'Year Built',
                    'value' => 0,
                    'key' => 'year_built'
                ];
                $data[] = [
                    'label' => 'Heat fuel type',
                    'value' => 0,
                    'key' => 'heat_fuel_type'
                ];
                $data[] = [
                    'label' => 'Heated SF',
                    'value' => 0,
                    'key' => 'heated_sf'
                ];
                $data[] = [
                    'label' => 'Total SF',
                    'value' => 0,
                    'key' => 'total_sf'
                ];
                $data[] = [
                    'label' => 'Full Baths',
                    'value' => 0,
                    'key' => 'full_baths'
                ];
                $data[] = [
                    'label' => 'Total Baths',
                    'value' => 0,
                    'key' => 'total_baths'
                ];
                $data[] = [
                    'label' => 'Bath Fixtures',
                    'value' => 0,
                    'key' => 'bath_fixtures'
                ];
                $data[] = [
                    'label' => 'Bedrooms',
                    'value' => 0,
                    'key' => 'bedrooms'
                ];
                $data[] = [
                    'label' => 'Total Rooms',
                    'value' => 0,
                    'key' => 'total_rooms'
                ];
                $data[] = [
                    'label' => 'Basement SF',
                    'value' => 0,
                    'key' => 'basement_sf'
                ];
                $data[] = [
                    'label' => 'Basement Finish',
                    'value' => 0,
                    'key' => 'basement_finish'
                ];
                $data[] = [
                    'label' => 'Property Condition',
                    'value' => 0,
                    'key' => 'property_condition'
                ];
                $data[] = [
                    'label' => 'Exterior',
                    'value' => 0,
                    'key' => 'exterior'
                ];
                $data[] = [
                    'label' => 'Parking SF Available',
                    'value' => 0,
                    'key' => 'parking_sf_available'
                ];
                $data[] = [
                    'label' => 'Parking Spaces',
                    'value' => 0,
                    'key' => 'parking_spaces'
                ];
                $data[] = [
                    'label' => 'Architectural Style',
                    'value' => 0,
                    'key' => 'architectural_style'
                ];
                $data[] = [
                    'label' => 'Levels/Stories',
                    'value' => 0,
                    'key' => 'levels_stories'
                ];
                $data[] = [
                    'label' => 'Year Built/Last Modified',
                    'value' => 0,
                    'key' => 'year_built_last_modified'
                ];


                // get ATOM API property
                $atomAPI = new AtomApi();
                $address_1 = $project->street_address_1 ? $project->street_address_1 : '';
                $city = $project->city ? $project->city . ' ' : '';
                $state = $project->state ? $project->state . ' ' : '';
                $address_2 = $city . $state;

                $atomResponse = $atomAPI->fetchAtomProperty($address_1, $address_2);
                if ($atomResponse['status']['code'] === 0) {
                    $updatedATOMProperty = $this->updateAtomProperty($projectId, $atomResponse['property'][0]);
                    $updatedDetail = [];
                    $updatedDetail[] = [
                        'label' => 'Square Footage',
                        'value' => $project->square_footage ? $project->square_footage : 0,
                        'key' => 'square_footage'
                    ];
                    for ($i = 1; $i < count($data); $i++) {
                        $detail = $data[$i];
                        $temp = [
                            'label' => $detail['label'],
                            'value' => $updatedATOMProperty[$detail['key']],
                            'key' => $detail['key'],
                        ];
                        $updatedDetail[] = $temp;
                    }
                    $data = $updatedDetail;
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Project detail data from ATOM',
                        'data' => $data
                    ]);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => $atomResponse['status']['msg'],
                        'data' => $data
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Update status successfully',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update_project_detail_item(Request $request)
    {
        try {
            $data = [];
            $user_id = $request->bearerToken();

            $projectId = $request->jobId;
            $field = $request->field;
            $value = $request->value;

            $project = Project::find($projectId);
            $project[$field] = $value;
            $project->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Updated project detail item successfully',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function un_share_project_with_team_member(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $projectId = $request->jobId;
            $shareSenderUserId = $user_id;
            $shareReceiverUserId = $request->shareReceiverUserId;
            $share = ProjectShare::where('share_sender_user_id', $shareSenderUserId)
                ->where('share_receiver_user_id', $shareReceiverUserId)->where('share_project_number', $projectId)->first();

            if (isset($share)) {
                $share->delete();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Removed sharing successfully',
                'data' => []
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function share_project_with_team_member(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $projectId = $request->jobId;
            $shareSenderUserId = $user_id;
            $shareReceiverUserId = $request->shareReceiverUserId;
            $share = ProjectShare::where('share_sender_user_id', $shareSenderUserId)
                ->where('share_receiver_user_id', $shareReceiverUserId)->where('share_project_number', $projectId)->first();

            if (isset($share)) {
                $share->delete();
            }

            ProjectShare::create([
                'share_sender_user_id' => $shareSenderUserId,
                'share_receiver_user_id' => $shareReceiverUserId,
                'share_project_number' => $projectId,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Shared project successfully',
                'data' => []
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update_shared_job_edit_permission(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $projectId = $request->jobId;
            $shareSenderUserId = $user_id;
            $shareReceiverUserId = $request->shareReceiverUserId;
            $value = $request->value;

            $share = ProjectShare::where('share_sender_user_id', $shareSenderUserId)
                ->where('share_receiver_user_id', $shareReceiverUserId)->where('share_project_number', $projectId)->first();

            if (isset($share)) {
                $share['allow_edit'] = $value;
                $share->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Updated permission successfully',
                'data' => []
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function add_non_team_member(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $projectId = $request->jobId;
            $shareSenderUserId = $user_id;
            $shareReceiverUserId = $request->shareCode;

            $user = User::find($shareReceiverUserId);
            if (!isset($user) || $shareSenderUserId == $shareReceiverUserId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid share code',
                    'data' => []
                ]);
            }

            $isTeamMemberId = UserPreferences::orWhere('quick_pick_share_user_id_1', $shareReceiverUserId)
                ->orWhere('quick_pick_share_user_id_1', $shareReceiverUserId)
                ->orWhere('quick_pick_share_user_id_2', $shareReceiverUserId)
                ->orWhere('quick_pick_share_user_id_3', $shareReceiverUserId)
                ->orWhere('quick_pick_share_user_id_4', $shareReceiverUserId)
                ->orWhere('quick_pick_share_user_id_5', $shareReceiverUserId)
                ->orWhere('quick_pick_share_user_id_6', $shareReceiverUserId)->first();
            if (isset($isTeamMemberId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Typed share code is team member's one",
                    'data' => []
                ]);
            }

            $share = ProjectShare::where('share_sender_user_id', $shareSenderUserId)
                ->where('share_receiver_user_id', $shareReceiverUserId)->where('share_project_number', $projectId)->first();
            if (isset($share)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Already existing code',
                    'data' => []
                ]);
            }

            ProjectShare::create([
                'share_sender_user_id' => $shareSenderUserId,
                'share_receiver_user_id' => $shareReceiverUserId,
                'share_project_number' => $projectId,
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Shared successfully',
                'data' => [
                    'name' => $user->display_name,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * update project analysis entry
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_analysis(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $project_id = $request->projectId;
            $request_field = $request->field;
            $value = $request->value;

            $field = '';
            switch ($request_field) {
                case 'squareFootage':
                    $field = 'square_footage';
                    break;
                case 'purchasePrice':
                    $field = 'purchase_price';
                    break;
                case 'arv':
                    $field = 'arv';
                    break;
                case 'repairCost':
                    $field = 'repair_cost';
                    break;
                case 'acquisitionCost':
                    $field = 'acquisition_costs';
                    break;
                case 'holdingCost':
                    $field = 'holding_costs';
                    break;
                case 'sellingCost':
                    $field = 'selling_costs';
                    break;
                case 'financingCost':
                    $field = 'financing_costs';
                    break;
                default:
                    break;
            }

            if (!$field) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid field',
                    'data' => []
                ]);
            } else if ($field === 'square_footage') {
                $project = Project::find($project_id);
                if ($project) {
                    $project[$field] = $value;
                    $project->save();
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No project found',
                    ], 500);
                }
            } else {
                $flip = FlipModel::where('user_id', $user_id)->where('project_id', $project_id)->first();
                if ($flip) {
                    $flip[$field] = $value;
                    $flip->save();
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Flip analysis not found',
                    ], 500);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Updated analysis data successfully',
                'data' => []
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * update project analysis detail
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_analysis_detail(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $project_id = $request->projectId;
            $field = $request->field;
            $data = $request->data;

            $flip = FlipModel::where('user_id', $user_id)->where('project_id', $project_id)->first();
            $flip[$field] = $data;
            $flip->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Updated detail info successfully',
                'data' => []
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * get comparable sales API using ATTOM
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_comparable_sales(Request $request)
    {
        try {
            $user_id = $request->bearerToken();

            $param = '';
            $param .= $request->postalCode;
            $param .= $request->propertyType ? '&propertyType=' . urlencode(trim($request->propertyType)) : '';
            $param .= $request->minBed ? '&minBeds=' . urlencode(trim($request->minBed)) : '';
            $param .= $request->maxBed ? '&maxBeds=' . urlencode(trim($request->maxBed)) : '';
            $param .= $request->minBath ? '&minBathsTotal=' . urlencode(trim($request->minBath)) : '';
            $param .= $request->maxBath ? '&maxBathsTotal=' . urlencode(trim($request->maxBath)) : '';
            $param .= $request->minSold ? '&startSaleSearchDate=' . urlencode(trim($request->minSold)) : '';
            $param .= $request->maxSold ? '&endSaleSearchDate=' . urlencode(trim($request->maxSold)) : '';
            $param .= $request->radius ? '&radius=' . urlencode(trim($request->radius)) : '';
            $param .= '&pageSize=20';

            $atomAPI = new AtomApi();
            $atomResponse = $atomAPI->comparableSales($param);
            if ($atomResponse['status']['code'] === 0) {
                $data = [];
                foreach ($atomResponse['property'] as $item) {
                    $temp = [];
                    $temp['address'] = $item['address']['oneLine'];
                    $temp['location'] = $item['location']; // longitude, latitude
                    $temp['rooms'] = $item['building']['rooms']; // bathstotal, bathsfull
                    $temp['sale'] = $item['sale']['amount']; // saleamt, salerecdate
                    $data[] = $temp;
                }
                return response()->json([
                    'status' => 'success',
                    'message' => 'Comparable sales data',
                    'data' => $data
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Comparable sales data',
                    'data' => $atomResponse['status']['msg']
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * get project deal analysis data
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_analysis(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $projectId = $request->jobId;

            $data = [];
            if (!$projectId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Job id is missing error',
                    'data' => $data
                ]);
            }

            // get min profit preference
            $preference = UserPreferences::where('user_id', $user_id)->first();
            $minAcceptableProfit = isset($preference) && $preference->minimum_acceptable_profit_dollars ? $preference->minimum_acceptable_profit_dollars : 0;

            $project = Project::find($projectId);
            $repair_cost = $this->get_ss_total($user_id, $projectId);
            $address = $this->get_full_address($project);
            $detailed_acquisition_costs = [];
            $detailed_holding_costs = [];
            $detailed_financing_costs = [];
            $detailed_selling_costs = [];
            $total_acquisition_costs = 0;
            $total_holding_costs = 0;
            $total_financing_costs = 0;
            $total_selling_costs = 0;
            $data = [
                'jobName' => $project->project_name,
                'jobAddress' => $address,
                'jobLocation' => json_decode($project->geo_location),
                'purchasePrice' => 0,
                'arv' => 0,
                'repairCost' => $repair_cost,
                'acquisitionCost' => 0,
                'acquisitionCostTotal' => $total_acquisition_costs,
                'acquisitionCostDetails' => $detailed_acquisition_costs,
                'holdingCost' => 0,
                'holdingCostTotal' => $total_holding_costs,
                'holdingCostDetails' => $detailed_holding_costs,
                'sellingCost' => 0,
                'sellingCostTotal' => $total_selling_costs,
                'sellingCostDetails' => $detailed_selling_costs,
                'financingCost' => 0,
                'financingCostTotal' => $total_financing_costs,
                'financingCostDetails' => $detailed_financing_costs,
                'profit' => 0,
                'maxPurchase' => 0,
                'maxRepairCost' => 0,
                'minProfitLimit' => 0,
                'minAcceptableProfit' => $minAcceptableProfit,
                'teamMembers' => [],
                'nonTeamMembers' => [],
                'sixValues' => 0,
            ];


            // get shared team members id
            $query = "SELECT share_receiver_user_id AS id, allow_edit
                    FROM user_project_shares
                    WHERE share_project_number='" . $projectId . "' AND share_sender_user_id='" . $user_id . "'";
            $sharedIds = DB::select($query);
            // get team members
            $preference = UserPreferences::where('user_id', $user_id)->first();
            $teamMemberIds = [];
            $teamMembers = [];
            if (isset($preference)) {
                if (isset($preference->quick_pick_share_user_id_1)) {
                    $id = $preference->quick_pick_share_user_id_1;
                    $temp = [
                        'shareCode' => $id,
                        'name' => $preference->quick_pick_share_user_name_1,
                        'shared' => false,
                        'allowEdit' => 0,
                    ];
                    $teamMemberIds[] = $id;
                    $isShared = $this->checkValueExistInArray($sharedIds, $id);
                    if ($isShared) {
                        $temp['shared'] = true;
                        $temp['allowEdit'] = $isShared->allow_edit;
                    }
                    $teamMembers[] = $temp;
                }
                if (isset($preference->quick_pick_share_user_id_2)) {
                    $id = $preference->quick_pick_share_user_id_2;
                    $temp = [
                        'shareCode' => $id,
                        'name' => $preference->quick_pick_share_user_name_2,
                        'shared' => false,
                        'allowEdit' => 0,
                    ];
                    $teamMemberIds[] = $id;
                    $isShared = $this->checkValueExistInArray($sharedIds, $id);
                    if ($isShared) {
                        $temp['shared'] = true;
                        $temp['allowEdit'] = $isShared->allow_edit;
                    }
                    $teamMembers[] = $temp;
                }
                if (isset($preference->quick_pick_share_user_id_3)) {
                    $id = $preference->quick_pick_share_user_id_3;
                    $temp = [
                        'shareCode' => $id,
                        'name' => $preference->quick_pick_share_user_name_3,
                        'shared' => false,
                        'allowEdit' => 0,
                    ];
                    $teamMemberIds[] = $id;
                    $isShared = $this->checkValueExistInArray($sharedIds, $id);
                    if ($isShared) {
                        $temp['shared'] = true;
                        $temp['allowEdit'] = $isShared->allow_edit;
                    }
                    $teamMembers[] = $temp;
                }
                if (isset($preference->quick_pick_share_user_id_4)) {
                    $id = $preference->quick_pick_share_user_id_4;
                    $temp = [
                        'shareCode' => $id,
                        'name' => $preference->quick_pick_share_user_name_4,
                        'shared' => false,
                        'allowEdit' => 0,
                    ];
                    $teamMemberIds[] = $id;
                    $isShared = $this->checkValueExistInArray($sharedIds, $id);
                    if ($isShared) {
                        $temp['shared'] = true;
                        $temp['allowEdit'] = $isShared->allow_edit;
                    }
                    $teamMembers[] = $temp;
                }
                if (isset($preference->quick_pick_share_user_id_5)) {
                    $id = $preference->quick_pick_share_user_id_5;
                    $temp = [
                        'shareCode' => $id,
                        'name' => $preference->quick_pick_share_user_name_5,
                        'shared' => false,
                        'allowEdit' => 0,
                    ];
                    $teamMemberIds[] = $id;
                    $isShared = $this->checkValueExistInArray($sharedIds, $id);
                    if ($isShared) {
                        $temp['shared'] = true;
                        $temp['allowEdit'] = $isShared->allow_edit;
                    }
                    $teamMembers[] = $temp;
                }
                if (isset($preference->quick_pick_share_user_id_6)) {
                    $id = $preference->quick_pick_share_user_id_6;
                    $temp = [
                        'shareCode' => $id,
                        'name' => $preference->quick_pick_share_user_name_6,
                        'shared' => false,
                        'allowEdit' => 0,
                    ];
                    $teamMemberIds[] = $id;
                    $isShared = $this->checkValueExistInArray($sharedIds, $id);
                    if ($isShared) {
                        $temp['shared'] = true;
                        $temp['allowEdit'] = $isShared->allow_edit;
                    }
                    $teamMembers[] = $temp;
                }
            }
            $data['teamMembers'] = $teamMembers;

            // get non team members that shared with this project
            $nonTeamMembers = [];
            foreach ($sharedIds as $item) {
                if (!in_array($item->id, $teamMemberIds)) {
                    $user = User::find($item->id);
                    if ($user) {
                        $nonTeamMembers[] = [
                            'shareCode' => $item->id,
                            'name' => $user->display_name,
                            'shared' => true,
                            'allowEdit' => $item->allow_edit,
                        ];
                    } else {
                        Log::info('No exist user id___' . $item->id);
                    }
                }
            }
            $data['nonTeamMembers'] = $nonTeamMembers;


            $flip_info = FlipModel::where('user_id', $user_id)->where('project_id', $projectId)->first();
            if (isset($flip_info)) {
                // get six value
                $six_value = $this->get_six_value($flip_info, $repair_cost);
                // profit = arv - 6 other fields
                $profit = $this->get_profit($flip_info, $six_value);
                // max purchase = minAcceptableProfit + six value
                $max_purchase = $minAcceptableProfit + $six_value;
                // max repair cost = A-B-D-E-F-G-MP = profit+c-MP
                $maxRepairCost = $profit + $repair_cost - $minAcceptableProfit;
                // min profit limit = profit + purchase price - minAcceptableProfit
                $minProfitLimit = $this->get_min_profit_limit($flip_info, $profit, $minAcceptableProfit);

                // detailed acquisition costs
                $detailed_costs = json_decode($flip_info->detailed_acquisition_costs);
                if ($detailed_costs && count($detailed_costs)) {
                    foreach ($detailed_costs as $item) {
                        $detailed_acquisition_costs[] = [
                            'item' => $item->item,
                            'amount' => $item->amount
                        ];
                        $total_acquisition_costs += (float)$item->amount;
                    }
                }

                // detailed holding costs
                $detailed_costs = json_decode($flip_info->detailed_holding_costs);
                if ($detailed_costs && count($detailed_costs)) {
                    foreach ($detailed_costs as $item) {
                        $detailed_holding_costs[] = [
                            'item' => $item->item,
                            'amount' => $item->amount
                        ];
                        $total_holding_costs += (float)$item->amount;
                    }
                }

                // detailed selling costs
                $detailed_costs = json_decode($flip_info->detailed_selling_costs);
                if ($detailed_costs && count($detailed_costs)) {
                    foreach ($detailed_costs as $item) {
                        $detailed_selling_costs[] = [
                            'item' => $item->item,
                            'amount' => $item->amount
                        ];
                        $total_selling_costs += (float)$item->amount;
                    }
                }

                // detailed financing costs
                $detailed_costs = json_decode($flip_info->detailed_financing_costs);
                if ($detailed_costs && count($detailed_costs)) {
                    foreach ($detailed_costs as $item) {
                        $detailed_financing_costs[] = [
                            'item' => $item->item,
                            'amount' => $item->amount
                        ];
                        $total_financing_costs += (float)$item->amount;
                    }
                }

                $data = [
                    'jobName' => $project->project_name,
                    'jobAddress' => $address,
                    'jobLocation' => json_decode($project->geo_location),
                    'purchasePrice' => $flip_info->purchase_price ? $flip_info->purchase_price : 0,
                    'arv' => $flip_info->arv ? $flip_info->arv : 0,
                    'repairCost' => $repair_cost,
                    'acquisitionCost' => $flip_info->acquisition_costs ? $flip_info->acquisition_costs : 0,
                    'acquisitionCostTotal' => $total_acquisition_costs,
                    'acquisitionCostDetails' => $detailed_acquisition_costs,
                    'holdingCost' => $flip_info->holding_costs ? $flip_info->holding_costs : 0,
                    'holdingCostTotal' => $total_holding_costs,
                    'holdingCostDetails' => $detailed_holding_costs,
                    'sellingCost' => $flip_info->selling_costs ? $flip_info->selling_costs : 0,
                    'sellingCostTotal' => $total_selling_costs,
                    'sellingCostDetails' => $detailed_selling_costs,
                    'financingCost' => $flip_info->financing_costs ? $flip_info->financing_costs : 0,
                    'financingCostTotal' => $total_financing_costs,
                    'financingCostDetails' => $detailed_financing_costs,
                    'profit' => round($profit, 2),
                    'maxPurchase' => round($max_purchase, 2),
                    'minProfitLimit' => round($minProfitLimit, 2),
                    'maxRepairCost' => round($maxRepairCost, 2),
                    'minAcceptableProfit' => $minAcceptableProfit,
                    'teamMembers' => $teamMembers,
                    'nonTeamMembers' => $nonTeamMembers,
                    'sixValues' => $six_value,
                ];
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Project analysis data',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * get formula for cost info screen
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_cost_info(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $data = [];
            $assemblies = Assembly::where('user_id', $user_id)->where('is_qv', 1)->first();
            if (!isset($assemblies)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Fetch formula',
                    'data' => $data
                ]);
            }
            $assembly_number = $assemblies->assembly_number;
            $items = AssemblyItem::where('user_id', $user_id)
                ->where('assembly_number', $assembly_number)->orderBy('item_order')->get()->toArray();
            foreach ($items as $item) {
                $formula_params = null;
                $costitem = UserCostItem::where('user_id', $user_id)->where('cost_group_number', $item['item_cost_group_number'])
                    ->where('item_number', $item['item_number'])->first();
                if ($item['formula_params']) {
                    $formula_params = $item['formula_params'];
                } else {
                    if ($costitem) {
                        $formula_params = $costitem->formula_params;
                    }
                }
                if ($formula_params) {
                    $temp_result = json_decode($formula_params, true);
                    if ($temp_result) {
                        $updated_temp_result = [];
                        foreach ($temp_result as $el) {
                            if ($el['type'] === 'variable') {
                                $temp_question = UserQuestion::where('user_id', $user_id)->where('question', $el['val'])->first();
                                $question_note = isset($temp_question->notes) ? $temp_question->notes : '';
                                $el['help'] = $question_note;
                            }
                            $updated_temp_result[] = $el;
                        }

                        $temp = [];
                        $temp['costItemId'] = $costitem->id;
                        $temp['formula'] = $updated_temp_result;
                        $data[] = $temp;
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Fetch formula',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * generate cost info to budget
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add_cost_info(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $project_id = $request->projectId;
            $items = $request->items;
            $is_qv = 1;

            Spreadsht::where('user_id', $user_id)->where('project_id', $project_id)->where('ss_is_qv', 1)->delete();

            foreach ($items as $item) {
                $costitem = UserCostItem::find($item['costItemId']);
                $cost_group = UserCostGroup::where('user_id', $user_id)
                    ->where('cost_group_number', $costitem->cost_group_number)->first();
                $group_desc = $cost_group->cost_group_desc;

                $ss = new Spreadsht;
                $ss->user_id = $user_id;
                $ss->project_id = $project_id;
                $ss->ss_item_cost_group_number = $costitem->cost_group_number;
                $ss->ss_item_cost_group_desc = $group_desc;
                $ss->ss_item_number = $costitem->item_number;
                $ss->ss_item_description = $costitem->item_desc;
                $ss->ss_notes = $costitem->notes;
                $ss->ss_item_takeoff_uom = $costitem->takeoff_uom;
                $ss->ss_labor_conversion_factor = $costitem->labor_conversion_factor;
                $ss->ss_labor_uom = $costitem->labor_uom;
                $ss->ss_labor_price = $costitem->labor_price;
                $ss->ss_material_conversion_factor = $costitem->material_conversion_factor;
                $ss->ss_material_uom = $costitem->material_uom;
                $ss->ss_material_price = $costitem->material_price;
                $ss->ss_subcontract_conversion_factor = $costitem->subcontract_conversion_factor;
                $ss->ss_subcontract_uom = $costitem->subcontract_uom;
                $ss->ss_subcontract_price = $costitem->subcontract_price;
                $ss->ss_other_conversion_factor = $costitem->other_conversion_factor;
                $ss->ss_other_uom = $costitem->other_uom;
                $ss->ss_other_price = $costitem->other_price;
                $ss->ss_home_depot_sku = $costitem->home_depot_sku;
                $ss->ss_home_depot_price = $costitem->home_depot_price;
                $ss->ss_lowes_sku = $costitem->lowes_sku;
                $ss->ss_lowes_price = $costitem->lowes_price;
                $ss->ss_whitecap_sku = $costitem->whitecap_sku;
                $ss->ss_whitecap_price = $costitem->whitecap_price;
                $ss->ss_bls_number = $costitem->bls_number;
                $ss->ss_bls_price = $costitem->bls_price;
                $ss->ss_grainger_number = $costitem->grainger_number;
                $ss->ss_grainger_price = $costitem->grainger_price;
                $ss->ss_wcyw_number = $costitem->wcyw_number;
                $ss->ss_wcyw_price = $costitem->wcyw_price;
                $ss->ss_quote_or_invoice_item = $costitem->invoice_item_default;
                $ss->ss_selected_vendor = $costitem->selected_vendor;
                $ss->ss_use_labor = $costitem->use_labor;
                $ss->ss_use_material = $costitem->use_material;
                $ss->ss_use_sub = $costitem->use_sub;
                $ss->ss_is_qv = $is_qv;

                if ($item['DSQType'] === 'total') {
                    $ss->ss_subcontract_price = $item['val'];
                    $ss->ss_subcontract_conversion_factor = '1.0000';
                    $ss->ss_subcontract_uom = 'lump sum';
                    $ss->ss_item_takeoff_uom = 'lump sum';
                    $ss->ss_item_takeoff_quantity = '1';
                    $ss->ss_subcontract_order_quantity = '1';
                    $ss->save();
                } else if ($item['DSQType'] === 'category') {
                    $ss->ss_subcontract_price = $item['val']['categoryLab'];
                    $ss->ss_material_price = $item['val']['categoryMat'];
                    $ss->ss_subcontract_conversion_factor = '1.0000';
                    $ss->ss_material_conversion_factor = '1.0000';
                    $ss->ss_item_takeoff_uom = 'lump sum';
                    $ss->ss_subcontract_uom = 'lump sum';
                    $ss->ss_material_uom = 'lump sum';
                    $ss->ss_item_takeoff_quantity = '1';
                    $ss->ss_subcontract_order_quantity = '1';
                    $ss->ss_material_order_quantity = '1';
                    $ss->save();
                } else if ($item['DSQType'] === 'tricky') {
                    $ss->ss_item_takeoff_quantity = $item['val']['trickyOfUnites'];
                    $ss->ss_subcontract_price = $item['val']['trickyPerUnit'];
                    $ss->ss_subcontract_conversion_factor = '1.0000';
                    $ss->ss_subcontract_uom = 'lump sum';
                    $ss->ss_item_takeoff_uom = 'lump sum';
                    $ss->save();
                } else {
                    if ($item['val'] && $item['val'] !== '0') {
                        $ss->ss_item_takeoff_quantity = $item['val'];
                        $ss->save();
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Budget is generated successfully',
                'data' => []
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * get budget screen data like spreadsheet
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_budget(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $projectId = $request->projectId;
            $data = Spreadsht::where('user_id', $user_id)->where('project_id', $projectId)->where('ss_is_qv', 1)
                ->orderBy('ss_item_cost_group_number')->orderBy('ss_item_number')->get()->toArray();

            $ss_data = [];
            $total_budget = 0;
            foreach ($data as $row) {
                if ($row['ss_labor_conversion_factor'] == '0') {
                    $row['ss_labor_conversion_factor'] = 1;
                }
                if ($row['ss_material_conversion_factor'] == '0') {
                    $row['ss_material_conversion_factor'] = 1;
                }
                if ($row['ss_subcontract_conversion_factor'] == '0') {
                    $row['ss_subcontract_conversion_factor'] = 1;
                }
                if ($row['ss_other_conversion_factor'] == '0') {
                    $row['ss_other_conversion_factor'] = 1;
                }
                $labor_total = Round($row['ss_item_takeoff_quantity'] / $row['ss_labor_conversion_factor'] * $row['ss_labor_price'], 2);
                $material_total = Round($row['ss_item_takeoff_quantity'] / $row['ss_material_conversion_factor'] * $row['ss_material_price'], 2);
                $subcontract_total = Round($row['ss_item_takeoff_quantity'] / $row['ss_subcontract_conversion_factor'] * $row['ss_subcontract_price'], 2);
                $other_total = Round($row['ss_item_takeoff_quantity'] / $row['ss_other_conversion_factor'] * $row['ss_other_price'], 2);
                $total = $labor_total + $material_total + $subcontract_total + $other_total;
                $total_budget += $total;

                $ss_data[] = [
                    'id' => $row['id'],
                    'desc' => $row['ss_item_description'],
                    'qty' => $row['ss_item_takeoff_quantity'],
                    'uom' => $row['ss_item_takeoff_uom'],
                    'price' => $row['ss_subcontract_price'],
                    'amount' => $total
                ];
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Fetch budget data',
                'data' => [
                    'ssData' => $ss_data,
                    'totalBudget' => $total_budget
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * update budget Qty, Price
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_budget(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $id = $request->id;
            $ssRow = Spreadsht::find($id);
            $ssRow->ss_item_takeoff_quantity = $request->qty;
            $ssRow->ss_line_total = $request->amount;
            $ssRow->ss_subcontract_price = $request->price;

            $ssRow->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Budget updated successfully',
                'data' => $ssRow
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * get project pictures
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_pictures(Request $request)
    {
        try {

            $user_id = $request->bearerToken();
            $projectId = $request->projectId;
            $pictures = ProjectPdfSheet::where('user_id', $user_id)->where('project_id', $projectId)->where('category', 'picture')->get();

            $data = [];
            foreach ($pictures as $picture) {
                $data[] = [
                    'imageURL' => $picture->file
                ];
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Picture list',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function upload_picture(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $project_id = $request->projectId;

            $name = time() . '__' . $request->picture->getClientOriginalName();
            $dir_path = public_path('document_picture/' . $user_id);
            $request->picture->move($dir_path, $name);

            $category = 'picture';
            $exist_max_sheet_order = ProjectPdfSheet::where('user_id', $user_id)->where('project_id', $project_id)->where('category', $category)->max('sheet_order');

            $exist_max_sheet_order++;
            $filePath = '/document_picture/' . $user_id . '/' . $name;
            $file = array(
                'user_id' => $user_id,
                'project_id' => $project_id,
                'sheet_name' => $name,
                'pdf_path' => $filePath,
                'file' => $filePath,
                'sheet_order' => $exist_max_sheet_order,
                'x' => 2,
                'y' => 2,
                'width' => 0,
                'height' => 0,
                'category' => $category
            );
            ProjectPdfSheet::create($file);

            $data = [
                'imageURL' => $filePath
            ];
            return response()->json([
                'status' => 'success',
                'message' => 'Uploaded successfully',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * get preference screen API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_preference(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $data = [
                'minAcceptableProfit' => "0",
                'percentOfArv' => "0",
                'teamMembers' => [],
                'useDollarsOrPercent' => "0",
            ];

            $preferences = UserPreferences::where('user_id', $user_id)->first();
            if ($preferences) {
                $data['minAcceptableProfit'] = isset($preferences->minimum_acceptable_profit_dollars) ? $preferences->minimum_acceptable_profit_dollars : "0";
                $data['percentOfArv'] = isset($preferences->minimum_acceptable_profit_percent) ? $preferences->minimum_acceptable_profit_percent : "0";
                $data['useDollarsOrPercent'] = isset($preferences->use_dollars_or_percent) ? $preferences->use_dollars_or_percent : "0";

                $teamMembers = [];
                if (isset($preferences->quick_pick_share_user_id_1)) {
                    $teamMembers[] = [
                        'shareCode' => $preferences->quick_pick_share_user_id_1,
                        'name' => $preferences->quick_pick_share_user_name_1,
                    ];
                }
                if (isset($preferences->quick_pick_share_user_id_2)) {
                    $teamMembers[] = [
                        'shareCode' => $preferences->quick_pick_share_user_id_2,
                        'name' => $preferences->quick_pick_share_user_name_2,
                    ];
                }
                if (isset($preferences->quick_pick_share_user_id_3)) {
                    $teamMembers[] = [
                        'shareCode' => $preferences->quick_pick_share_user_id_3,
                        'name' => $preferences->quick_pick_share_user_name_3,
                    ];
                }
                if (isset($preferences->quick_pick_share_user_id_4)) {
                    $teamMembers[] = [
                        'shareCode' => $preferences->quick_pick_share_user_id_4,
                        'name' => $preferences->quick_pick_share_user_name_4,
                    ];
                }
                if (isset($preferences->quick_pick_share_user_id_5)) {
                    $teamMembers[] = [
                        'shareCode' => $preferences->quick_pick_share_user_id_5,
                        'name' => $preferences->quick_pick_share_user_name_5,
                    ];
                }
                if (isset($preferences->quick_pick_share_user_id_6)) {
                    $teamMembers[] = [
                        'shareCode' => $preferences->quick_pick_share_user_id_6,
                        'name' => $preferences->quick_pick_share_user_name_6,
                    ];
                }
                $data['teamMembers'] = $teamMembers;
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Preference data',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update_preference(Request $request)
    {
        try {
            $data = [];
            $user_id = $request->bearerToken();
            $field = $request->field;
            $value = $request->value;

            $temp = UserPreferences::where('user_id', $user_id)->first();
            if (isset($temp->id)) {
                $preference = UserPreferences::find($temp->id);
                $preference[$field] = $value;
                $preference->save();
            } else {
                UserPreferences::create([
                    'user_id' => $user_id,
                    $field => $value
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Updated preference data successfully',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // add team member in preference screen
    public function add_team_member(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $shareCode = $request->shareCode;

            $teamMember = User::find($shareCode);
            if (!isset($teamMember)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid share code',
                    'data' => $shareCode
                ]);
            }

            if ($user_id === $shareCode) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Not allowed to put your own share code in',
                    'data' => $shareCode
                ]);
            }

            $name = $teamMember->display_name;
            $temp = UserPreferences::where('user_id', $user_id)->first();
            if (isset($temp->id)) {
                $preference = UserPreferences::find($temp->id);
                if (!$temp->quick_pick_share_user_id_1) {
                    $preference->quick_pick_share_user_id_1 = $shareCode;
                    $preference->quick_pick_share_user_name_1 = $name;
                } else if (!$temp->quick_pick_share_user_id_2) {
                    $preference->quick_pick_share_user_id_2 = $shareCode;
                    $preference->quick_pick_share_user_name_2 = $name;
                } else if (!$temp->quick_pick_share_user_id_3) {
                    $preference->quick_pick_share_user_id_3 = $shareCode;
                    $preference->quick_pick_share_user_name_3 = $name;
                } else if (!$temp->quick_pick_share_user_id_4) {
                    $preference->quick_pick_share_user_id_4 = $shareCode;
                    $preference->quick_pick_share_user_name_4 = $name;
                } else if (!$temp->quick_pick_share_user_id_5) {
                    $preference->quick_pick_share_user_id_5 = $shareCode;
                    $preference->quick_pick_share_user_name_5 = $name;
                } else if (!$temp->quick_pick_share_user_id_6) {
                    $preference->quick_pick_share_user_id_6 = $shareCode;
                    $preference->quick_pick_share_user_name_6 = $name;
                }
                $preference->save();
            } else {
                UserPreferences::create([
                    'user_id' => $user_id,
                    'quick_pick_share_user_id_1' => $shareCode,
                    'quick_pick_share_user_name_1' => $name,
                ]);
            }

            $data = [
                'shareCode' => $shareCode,
                'name' => $name,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Added team member successfully',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function remove_team_member(Request $request)
    {
        try {
            $data = [];
            $user_id = $request->bearerToken();
            $shareCode = $request->shareCode;
            $temp = UserPreferences::where('user_id', $user_id)->first();
            $preference = UserPreferences::find($temp->id);

            $which = 'none';
            if ($preference->quick_pick_share_user_id_1 == $shareCode) {
                $preference->quick_pick_share_user_id_1 = NULL;
                $preference->quick_pick_share_user_name_1 = NULL;
                $which = 'quick_pick_share_user_id_1';
            } else if ($preference->quick_pick_share_user_id_2 == $shareCode) {
                $preference->quick_pick_share_user_id_2 = NULL;
                $preference->quick_pick_share_user_name_2 = NULL;
                $which = 'quick_pick_share_user_id_2';
            } else if ($preference->quick_pick_share_user_id_3 == $shareCode) {
                $preference->quick_pick_share_user_id_3 = NULL;
                $preference->quick_pick_share_user_name_3 = NULL;
                $which = 'quick_pick_share_user_id_3';
            } else if ($preference->quick_pick_share_user_id_4 == $shareCode) {
                $preference->quick_pick_share_user_id_4 = NULL;
                $preference->quick_pick_share_user_name_4 = NULL;
                $which = 'quick_pick_share_user_id_4';
            } else if ($preference->quick_pick_share_user_id_5 == $shareCode) {
                $preference->quick_pick_share_user_id_5 = NULL;
                $preference->quick_pick_share_user_name_5 = NULL;
                $which = 'quick_pick_share_user_id_5';
            } else if ($preference->quick_pick_share_user_id_6 == $shareCode) {
                $preference->quick_pick_share_user_id_6 = NULL;
                $preference->quick_pick_share_user_name_6 = NULL;
                $which = 'quick_pick_share_user_id_6';
            }
            $data['which'] = $which;
            $preference->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Removed team member successfully',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * account screen API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_account_info(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $user = User::find($user_id);
            $trialDays = env('TRIAL_DAYS');

            $data = [
                'accountName' => $user->display_name,
                'companyName' => $user->company_name,
                'email' => $user->user_email,
                'phone' => $user->phone,
                'userStatus' => $user->user_status,
                'trialEndsAt' => '',
                'remainDays' => '',
                'price' => 'Free for 2 weeks',
                'billingEndDate' => '-'
            ];

            if ($user->user_status === 0) { // trial
                $registerDate = Carbon::createFromFormat('Y-m-d H:i:s', $user->created_at);
                $trialEndsAt = $registerDate->addDays($trialDays);
                $today = Carbon::now();

                $isValidTrialPeriod = $today->lte($trialEndsAt);
                if ($isValidTrialPeriod) {
                    $remainDays = $trialEndsAt->diffInDays($today);
                    $data['remainDays'] = $remainDays;
                    // $data['trialEndsAt'] = $trialEndsAt->format('Y-m-d');
                    $data['trialEndsAt'] = 'Free Trial - ' . $remainDays . ' days left';
                } else {
                    $data['trialEndsAt'] = 'Trial expired - click Subscribe below';
                }
            } else if ($user->user_status === 2) { // trial expired
                $data['billingEndDate'] = 'Trial expired - click Subscribe below';
            } else {
                $plan = $user->plan;
                if ($plan == 'monthly') {
                    $data['price'] = '$20 Per Month';
                } else if ($plan == 'yearly') {
                    $data['price'] = '$200 Per Year';
                } else {
                    $data['price'] = '';
                }

                if ($user->billing_end_date) {
                    $billing_end_date = Carbon::parse($user->billing_end_date)->format('m-d-Y');
                    $data['billingEndDate'] = 'Next Billing Date = ' . $billing_end_date;
                } else {
                    $data['billingEndDate'] = 'Next Billing Date = -';
                }
            }


            return response()->json([
                'status' => 'success',
                'message' => 'Account information',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * get contact API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_contact(Request $request)
    {
        try {
            $data = [
                'email' => 'support@takeofflite.com',
                'phone' => '(413) 825-3633',
                'ext' => '614',
                'supportText' => 'Need a 5 minute personal training?',
                'supportLinkText' => 'Click here to let us know when you would like to do so',
                'supportLink' => 'https://calendly.com/takeofflite/5-minute-flipcruncher-training-clone',
            ];

            $user_id = $request->bearerToken();
            $info = UserAppInfo::all()->first();
            if ($info) {
                $data = [
                    'email' => $info->email,
                    'phone' => $info->phone,
                    'ext' => $info->ext,
                    'supportText' => $info->support_text,
                    'supportLinkText' => $info->support_link_text,
                    'supportLink' => $info->support_link,
                ];
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Contact information',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * get app info API
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_about_info(Request $request)
    {
        try {
            $data = [
                'info' => 'support@takeofflite.com',
                'version' => 'Version xxx',
            ];

            $user_id = $request->bearerToken();
            $info = UserAppInfo::all()->first();
            if ($info) {
                $data = [
                    'info' => $info->copy_right_text,
                    'version' => 'Version ' . $info->app_version,
                    'policyURL' => $info->policy_url,
                    'eulaURL' => $info->eula_url,
                ];
            }

            return response()->json([
                'status' => 'success',
                'message' => 'App information',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Share QR link through email
     * @param Request $request , share_link, share_email
     * @return \Illuminate\Http\JsonResponse
     */
    public function share_link_email(Request $request)
    {
        try {
            $data = [];
            $user_id = $request->bearerToken();
            $user = User::find($user_id);

            $from = $user->user_email;

            $data['sender_first_name'] = $user->first_name;
            $data['link_to_project'] = $request->share_link;

            //            Mail::mailer('smtp')->to($request->share_email)->send(new FCLinkShareEmail($data));
            Mail::send('mails.share-qr-link-mail', $data, function ($messages) use ($request, $from) {
                $messages->to($request->share_email);
                $messages->subject('Dee from FlipCruncher');
                $messages->from('email@takeofflite.com', $from);
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Email was sent successfully',
                'data' => []
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * payment API
     */

    public function pay(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $amount = $request->amount;
            $email = $request->email;
            $name = $request->name;
            $phone = $request->phone;

            $stripe_key = env('STRIPE_TEST_KEY');
            $stripe_secret_key = env('STRIPE_TEST_SECRET');

            \Stripe\Stripe::setApiKey($stripe_secret_key);
            $customer = \Stripe\Customer::create([
                'description' => 'customer of flip cruncher app',
                'email' => $email,
                'name' => $name,
                'phone' => $phone,
            ]);

            $ephemeralKey = \Stripe\EphemeralKey::create(
                ['customer' => $customer->id],
                ['stripe_version' => '2020-08-27']
            );

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount, // 2000 - Monthly, 20000 - Yearly
                'currency' => 'usd',
                'customer' => $customer->id,
                'automatic_payment_methods' => [
                    'enabled' => 'true',
                ]
            ]);

            // save customer id
            $user = User::find($user_id);
            $user->stripe_id = $customer->id;
            $user->save();

            $data = [
                'paymentIntent' => $paymentIntent->client_secret,
                'ephemeralKey' => $ephemeralKey->secret,
                'customer' => $customer->id,
                'publishableKey' => $stripe_key
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Payment successfully',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function live_pay(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $amount = $request->amount;
            $email = $request->email;
            $name = $request->name;
            $phone = $request->phone;

            $stripe_key = env('STRIPE_KEY');
            $stripe_secret_key = env('STRIPE_SECRET');

            \Stripe\Stripe::setApiKey($stripe_secret_key);
            $customer = \Stripe\Customer::create([
                'description' => 'customer of flip cruncher app',
                'email' => $email,
                'name' => $name,
                'phone' => $phone,
            ]);

            $ephemeralKey = \Stripe\EphemeralKey::create(
                ['customer' => $customer->id],
                ['stripe_version' => '2020-08-27']
            );

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount, // 2000 - Monthly, 20000 - Yearly
                'currency' => 'usd',
                'customer' => $customer->id,
                'automatic_payment_methods' => [
                    'enabled' => 'true',
                ]
            ]);

            // save customer id
            $user = User::find($user_id);
            $user->stripe_id = $customer->id;
            $user->save();

            $data = [
                'paymentIntent' => $paymentIntent->client_secret,
                'ephemeralKey' => $ephemeralKey->secret,
                'customer' => $customer->id,
                'publishableKey' => $stripe_key
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Payment successfully',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    // create customer
    public function create_customer_test(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $email = $request->email;
            $name = $request->name ? $request->name : '';
            $phone = $request->phone ? $request->phone : '';

            $stripe_key = env('STRIPE_TEST_KEY');

            $user = User::find($user_id);
            $stripe_id = $user->stripe_id;
            if ($stripe_id) {
                $data = [
                    'publishableKey' => $stripe_key,
                    'customer_id' => $stripe_id,
                ];

                return response()->json([
                    'status' => 'success',
                    'message' => 'Retrieved customer successfully',
                    'data' => $data
                ]);
            } else {
                $stripe_key = env('STRIPE_TEST_KEY');
                $stripe_secret_key = env('STRIPE_TEST_SECRET');

                \Stripe\Stripe::setApiKey($stripe_secret_key);
                $customer = \Stripe\Customer::create([
                    'description' => 'customer of flip cruncher app',
                    'email' => $email,
                    'name' => $name,
                    'phone' => $phone,
                ]);

                // save customer id
                $user = User::find($user_id);
                $user->stripe_id = $customer->id;
                $user->save();

                $data = [
                    'publishableKey' => $stripe_key,
                    'customer_id' => $customer->id,
                ];

                return response()->json([
                    'status' => 'success',
                    'message' => 'Created customer successfully',
                    'data' => $data
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    // create subscription
    public function create_subscription_test(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $email = $request->email;
            $name = $request->name ? $request->name : '';
            $phone = $request->phone ? $request->phone : '';
            $price = $request->price;

            $stripe_secret_key = env('STRIPE_TEST_SECRET');
            \Stripe\Stripe::setApiKey($stripe_secret_key);

            // get price id && plan
            if ($price === 'MONTHLY_PRICE') {
                $price_id = env('APP_MONTHLY_PRICE_ID_TEST');
                $plan = env('PLAN_MONTHLY');
                $billing_end_date = Carbon::now()->addMonth();
            } else if ($price === 'YEARLY_PRICE') {
                $price_id = env('APP_YEARLY_PRICE_ID_TEST');
                $plan = env('PLAN_YEARLY');
                $billing_end_date = Carbon::now()->addYear();
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid price'
                ], 500);
            }

            // retrieve customer id
            $user = User::find($user_id);
            $customer_id = $user->stripe_id;
            if (!$customer_id) {
                $customer = \Stripe\Customer::create([
                    'description' => 'test customer of flip cruncher app',
                    'email' => $email,
                    'name' => $name,
                    'phone' => $phone,
                ]);
                $customer_id = $customer->id;
            }
            $user->stripe_id = $customer_id;
            $user->save();

            // create subscription
            $subscription = \Stripe\Subscription::create([
                'customer' => $customer_id,
                'items' => [
                    ['price' => $price_id],
                ],
                'payment_behavior' => 'default_incomplete',
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            $data = [
                'subscriptionId' => $subscription->id,
                'clientSecret' => $subscription->latest_invoice->payment_intent->client_secret,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Created subscription successfully',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function create_subscription(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $email = $request->email;
            $name = $request->name ? $request->name : '';
            $phone = $request->phone ? $request->phone : '';
            $price = $request->price;

            $stripe_secret_key = env('STRIPE_SECRET');
            \Stripe\Stripe::setApiKey($stripe_secret_key);

            // get price id && plan
            if ($price === 'MONTHLY_PRICE') {
                $price_id = env('APP_MONTHLY_PRICE_ID');
                $plan = env('PLAN_MONTHLY');
                $billing_end_date = Carbon::now()->addMonth();
            } else if ($price === 'YEARLY_PRICE') {
                $price_id = env('APP_YEARLY_PRICE_ID');
                $plan = env('PLAN_YEARLY');
                $billing_end_date = Carbon::now()->addYear();
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid price'
                ], 500);
            }

            // retrieve customer id
            $user = User::find($user_id);
            $customer_id = $user->stripe_id;
            if (!$customer_id) {
                $customer = \Stripe\Customer::create([
                    'description' => 'customer of flip cruncher app',
                    'email' => $email,
                    'name' => $name,
                    'phone' => $phone,
                ]);
                $customer_id = $customer->id;
            }
            $user->stripe_id = $customer_id;
            $user->save();

            // create subscription
            $subscription = \Stripe\Subscription::create([
                'customer' => $customer_id,
                'items' => [
                    ['price' => $price_id],
                ],
                'payment_behavior' => 'default_incomplete',
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            $data = [
                'subscriptionId' => $subscription->id,
                'clientSecret' => $subscription->latest_invoice->payment_intent->client_secret,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Created subscription successfully',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    // cancel subscription
    public function cancel_subscription_test(Request $request)
    {
        try {
            $user_id = $request->bearerToken();

            $stripe_secret_key = env('STRIPE_TEST_SECRET');
            \Stripe\Stripe::setApiKey($stripe_secret_key);

            // retrieve subscription id
            $user = User::find($user_id);
            $subscription_id = $user->subscription_id;
            if (!$subscription_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No exist subscription'
                ], 400);
            }

            // cancel subscription
            $subscription = \Stripe\Subscription::retrieve($subscription_id);
            $subscription->delete();

            $user->subscription_id = '';
            $user->save();

            $data = [
                'subscription' => $subscription,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Cancelled subscription successfully',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function cancel_subscription(Request $request)
    {
        try {
            $user_id = $request->bearerToken();

            $stripe_secret_key = env('STRIPE_SECRET');
            \Stripe\Stripe::setApiKey($stripe_secret_key);

            // retrieve subscription id
            $user = User::find($user_id);
            $subscription_id = $user->subscription_id;
            if (!$subscription_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No exist subscription'
                ], 400);
            }

            // cancel subscription
            $subscription = \Stripe\Subscription::retrieve($subscription_id);
            $subscription->delete();

            $user->subscription_id = '';
            $user->save();

            $data = [
                'subscription' => $subscription,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Cancelled subscription successfully',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * in-app pay success
     * update billing_end_date, has_trial, plan, subscription_id, user_status
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function inAppPaySuccess(Request $request)
    {
        try {
            $user_id = $request->bearerToken();
            $user = User::find($user_id);
            $user->has_trial = false;
            $user->user_status = 1;
            $user->plan = $request->plan;
            $user->subscription_id = $request->subscription_id;
            $user->billing_end_date = $request->billing_end_date;
            $user->save();

            $updated_user = User::find($user_id);
            $data = [
                'userStatus' => $updated_user->user_status,
                'customerId' => $updated_user->stripe_id,
                'subscriptionId' => $updated_user->subscription_id,
                'hasTrial' => $updated_user->has_trial,
                'billingEndDate' => $updated_user->billing_end_date,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Created subscription successfully',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * app web hook
     */
    public function web_hook()
    {
        $endpoint_secret = env('STRIPE_APP_WEBHOOK_SECRET');
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            Log::error($e->getMessage());
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            Log::error($e->getMessage());
            http_response_code(400);
            exit();
        }
        Log::info($event->type);

        if ($event->type == "customer.created") {
            $customer = $event->data->object;
            Log::info("APP customer created:" . $customer->id);
            http_response_code(200);
            exit();
        } else if ($event->type == "payment_intent.created") {
            $intent = $event->data->object;
            Log::info("APP payment intent created:" . $intent->id);
            http_response_code(200);
            exit();
        } else if ($event->type == "payment_intent.succeeded") {
            $intent = $event->data->object;
            Log::info("APP payment Succeeded:" . $intent->id);
            Log::info("PI Customer:" . $intent->customer);
            // update db
            http_response_code(200);
            exit();
        } else if ($event->type == "charge.succeeded") {
            $intent = $event->data->object;
            Log::info("APP charge Succeeded:" . $intent->id);
            http_response_code(200);
            exit();
        } else if ($event->type == "balance.available") {
            Log::info("APP balance available:");
            http_response_code(200);
            exit();
        } else if ($event->type == "payment_intent.processing") {
            Log::info("APP payment processing:");
            http_response_code(200);
            exit();
        } else if ($event->type == "payment_intent.payment_failed") {
            $intent = $event->data->object;
            $error_message = $intent->last_payment_error ? $intent->last_payment_error->message : "";
            Log::info("APP payment Failed:" . $intent->id, $error_message);
            http_response_code(200);
            exit();
        }
    }
}
