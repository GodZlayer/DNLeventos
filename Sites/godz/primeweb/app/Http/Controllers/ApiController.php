<?php
namespace App\Http\Controllers;
use App\Models\Draweritem;
use App\Models\Fcm;
use App\Models\Onboarding;
use App\Models\Setting;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Storage;
use Throwable;
use Validator;
class ApiController extends Controller
{
    private string $uploadFolder;
    public function __construct()
    {
        $this->uploadFolder = 'item_images';
        if (array_key_exists('HTTP_AUTHORIZATION', $_SERVER)) {
            $this->middleware('auth:sanctum');
        }
    }
    public function getSystemSettings(Request $request)
    {
       try {
    $settings = Setting::select(['name', 'value']);
    if (!empty($request->type)) {
        $settings->where('name', $request->type);
    }
    $settings = $settings->get();
    $tempRow = [];
    
    // Define URL transform keys
    $urlTransformKeys = [
        'admin_logo', 'favicon', 'background_image', 'login_image',
        'placeholder_image', 'web_logo', 'app_logo', 'company_logo',
        'favicon_icon', 'applogo'
    ];
    
    // Define boolean keys
    $booleanKeys = [
        'pull_to_refresh', 'onboarding_screen', 'exit_popup_screen',
        'app_drawer', 'show_bottom_navigation',
        'banner_ad_status', 'interstitial_ad_status', 'admob_ad_status' // Added to merge boolean keys
    ];
    
    foreach ($settings as $row) {
        $value = $row->value;
        
        // Handle URL transformation
        if (in_array($row->name, $urlTransformKeys)) {
            $value = url(Storage::url($value));
        }
        
        // Handle boolean values
        if (in_array($row->name, $booleanKeys)) {
            $tempRow[$row->name] = $value == "deactive" || $value == "false" ? false : true;
        } else {
            $tempRow[$row->name] = $value;
        }
    }
    
    // Define unset values
    $unset_values = [
        'admin_logo', 'favicon', 'background_image', 'login_image', 
        'web_theme_color', 'firebase_project_id', 'placeholder_image', 
        'web_logo', 'theme_color', 'backgroundcolor', 'color_code', 
        'project_id'
    ];
    
    foreach ($unset_values as $value) {
        if (isset($tempRow[$value])) {
            unset($tempRow[$value]);
        }
    }
    
    // Add demo mode
    $tempRow['demo_mode'] = config('app.demo_mode');
    
    return ResponseService::successResponse("Data Fetched Successfully", $tempRow);
} catch (Throwable $th) {
    ResponseService::logErrorResponse($th, "API Controller -> getSystemSettings");
    return ResponseService::errorResponse();
}

    }
    public function getDrawerItems(Request $request)
    {
        try {
            $sort = 'id';
            $order = 'DESC';
            $sql = Draweritem::query();
            if ($request->id) {
                $sql->where('id', $request->id);
            }
            if ($request->sort) {
                $sort = $request->sort;
            }
            if ($request->order) {
                $order = $request->order;
            }
            if ($request->search) {
                $search = $request->search;
                $sql->where('id', 'LIKE', "%$search%")->orwhere('title', 'LIKE', "%$search%");
            }
            $data = $sql->orderBy($sort, $order)->paginate();
            ResponseService::successResponse("Data Fetched Successfully", $data);
        } catch (\Throwable $th) {
            ResponseService::logErrorResponse($th, "API Controller -> getDrawerItems");
            ResponseService::errorResponse();
        }
    }
    public function  getOnboardingList(Request $request)
    {
        try {
            $sort = 'id';
            $order = 'DESC';
            $sql = Onboarding::query();
            if ($request->id) {
                $sql->where('id', $request->id);
            }

             $sql->where('status', 1);

            if ($request->sort) {
                $sort = $request->sort;
            }
            if ($request->order) {
                $order = $request->order;
            }
            if ($request->search) {
                $search = $request->search;
                $sql->where('id', 'LIKE', "%$search%")->orwhere('title', 'LIKE', "%$search%")->orWhere('description', 'LIKE', "%$search%");
            }
            $data = $sql->orderBy($sort, $order)->paginate();
            ResponseService::successResponse("Data Fetched Successfully", $data);
        } catch (\Throwable $th) {
            ResponseService::logErrorResponse($th, "API Controller -> getOnboardingList");
            ResponseService::errorResponse();
        }
    }
    public function  getOnboardingSt(Request $request)
    {
        try {
            $onboarding = Onboarding::paginate();
            ResponseService::successResponse("Data Fetched Successfully", $onboarding);
        } catch (\Throwable $th) {
            ResponseService::logErrorResponse($th, "API Controller -> getOnboardingList");
            ResponseService::errorResponse();
        }
    }
    public function  addFcm(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fcm' => 'required'
            ]);
            if ($validator->fails()) {
                ResponseService::validationError($validator->errors()->first());
            }
            $fcm = new Fcm();
            $fcm->fcm = $request->fcm;
            $fcm->save();
            ResponseService::successResponse("Data Stored Successfully", $request->fcm);
        } catch (\Throwable $th) {
            ResponseService::logErrorResponse($th, "API Controller -> addFcm");
            ResponseService::errorResponse();
        }
    }
}
