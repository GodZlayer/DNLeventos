<?php
namespace App\Http\Controllers;
use App\Models\Setting;
use App\Services\CachingService;
use App\Services\FileService;
use App\Services\ResponseService;
use Throwable;
use Illuminate\Http\Request;
class SettingController extends Controller
{
    private string $uploadFolder;
    protected $helperService;
    public function __construct()
    {
        $this->uploadFolder = 'settings';
    }
    public function index()
    {
        $settings = getadminSettings();
        return view('setting.index', compact('settings'));
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
            ResponseService::successResponse('setting update Successfully');
        } catch (Throwable $th) {
            ResponseService::logErrorResponse($th, "Setting Controller -> store");
            ResponseService::errorResponse('Something Went Wrong');
        }
    }
    public function show(string $id)
    {
    }
    public function edit(string $id)
    {
    }
    public function update(Request $request, string $id)
    {
    }
    public function destroy(string $id)
    {
    }
    public function about_us()
    {
        $settings = getadminSettings('about_us');
        return view('setting.about_us', compact('settings'));
    }
    public function contact_us()
    {
        $settings = getadminSettings('contact_us');
        return view('setting.contact_us', compact('settings'));
    }
    public function terms_and_conditions()
    {
        $settings = getadminSettings('terms_and_condition');
        return view('setting.terms_and_conditions', compact('settings'));
    }
    public function privacy_policy()
    {
        $settings = getadminSettings('privacy_policy');
        return view('setting.privacy_policy', compact('settings'));
    }
    public function app_settings()
    {
        $settings = getadminSettings();
        return view('setting.app_settings',compact('settings'));
    }
}
