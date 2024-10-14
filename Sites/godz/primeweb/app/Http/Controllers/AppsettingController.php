<?php
namespace App\Http\Controllers;
use App\Models\Setting;
use App\Services\CachingService;
use App\Services\FileService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Throwable;
use Validator;
class AppsettingController extends Controller
{
    private string $uploadFolder;
    protected $helperService;
    public function __construct() {
        $this->uploadFolder = 'settings';
    }
    public function index()
    {
        $appsettings = getSettings();
        return view('app_settings.create',compact('appsettings'));
    }
    public function create()
    {
    }
    public function store(Request $request)
    {
        try {
            $inputs = $request->input();
            unset($inputs['_token']);
            $data = [];
            foreach ($inputs as $key => $input) {
                $data[] = [
                    'name'  => $key,
                    'value' => $input,
                    'type'  => 'string'
                ];
            }
            if ($request->hasFile('service_file')) {
                $validator = Validator::make($request->all(), [
                    'service_file' => 'required|mimes:json',
                ]);
                if ($validator->fails()) {
                    ResponseService::validationError($validator->errors()->first());
                }
            }
            $oldSettingFiles = Setting::whereIn('name', collect($request->files)->keys())->get();
            foreach ($request->files as $key => $file) {
                $data[] = [
                    'name'  => $key,
                    'value' => $request->file($key)->store($this->uploadFolder, 'public'),
                    'type'  => 'file'
                ];
                $oldFile = $oldSettingFiles->first(function ($old) use ($key) {
                    return $old->name == $key;
                });
                if (!empty($oldFile)) {
                    FileService::delete($oldFile->getRawOriginal('value'));
                }
            }
            Setting::upsert($data, 'name', ['value']);
            CachingService::removeCache(config('constants.CACHE.SETTINGS'));
            ResponseService::successResponse('App setup update Successfully');
        } catch (Throwable $th) {
            ResponseService::logErrorResponse($th, "Appsetting Controller -> store");
            ResponseService::errorResponse('Something Went Wrong');
        }
    }
    public function show(Request $request)
    {
    }
    public function edit(string $id)
    {
    }
    public function draweritemupdate(Request $request)
    {
    }
    public function draweritemdestroy($id)
    {
    }
    public function splashscreenindex(){
        $appsettings = getSettings();
        return view('app_settings.splashscreen',compact('appsettings'));
    }
    public function adsbindex(){
        $appsettings = getSettings();
        return view('app_settings.admob',compact('appsettings'));
    }
    public function onboardingstyleindex(){
        $appsettings = getSettings();
        return view('app_settings.onboardingstyle',compact('appsettings'));
    }
}
