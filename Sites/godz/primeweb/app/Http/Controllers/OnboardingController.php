<?php
namespace App\Http\Controllers;
use App\Models\Onboarding;
use App\Services\FileService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use App\Services\BootstrapTableService;
use Illuminate\Support\Facades\Validator;
use Throwable;
class OnboardingController extends Controller
{
    private string $uploadFolder;
    public function __construct() {
        $this->uploadFolder = "onbording";
    }
    public function index()
    {
    }
    public function create()
    {
        $onboardings = Onboarding::all();
        return view('onboarding.index', compact('onboardings'));
    }
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'image' => 'required|image|mimes:jpeg,png,jpg',
                'description' => 'required|string',
                'status' => 'boolean',
            ]);
            if ($validator->fails()) {
                ResponseService::validationError($validator->errors()->first());
            }
            $data = $request->all();
            $data['status'] = $request->has('status') ? 1 : 0;
            if ($request->hasFile('image')) {
                $data['image'] = FileService::compressAndUpload($request->file('image'), $this->uploadFolder);
            }
            Onboarding::create($data);
            ResponseService::successResponse('Onboarding created successfully.');
        } catch (Throwable $th) {
            ResponseService::logErrorResponse($th, "Onboarding Controller -> store");
            ResponseService::errorResponse();
        }
    }
    public function show(Request $request) {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'ASC');
        $sql = Onboarding::where('id', '!=', 0);
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")->orwhere('title', 'LIKE', "%$search%")->orwhere('description', 'LIKE', "%$search%");
        }
        $total = $sql->count();
        $sql->skip($offset)->take($limit);
        $result = $sql->get();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $no = 1;
        foreach ($result as $key => $row) {
            $operate = '';
            $operate .= BootstrapTableService::editButton(route('onboarding.update', $row->id), true, '#editModal', 'onboardingEvents', $row->id);
            $operate .= BootstrapTableService::deleteButton(route('onboarding.destroy', $row->id));
            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            $tempRow['status'] = $row->status;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }
    public function edit($id)
    {
        $onboarding = Onboarding::findOrFail($id);
        return response()->json($onboarding);
    }
    public function update(Request $request)
    {
        try {
             $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'image' => 'image|mimes:jpeg,png,jpg',
                'description' => 'required|string',
                'status' => 'boolean',
            ]);
            if ($validator->fails()) {
                ResponseService::validationError($validator->errors()->first());
            }
            $onboarding = Onboarding::findOrFail($request->edit_id);
            $data = $request->all();
            $data['status'] = $request->status ? 1 : 0;
            if ($request->hasFile('image')) {
                $data['image'] = FileService::compressAndUpload($request->file('image'), $this->uploadFolder);
            }
            $onboarding->update($data);
            ResponseService::successResponse('Onboarding Updated Successfully');
        } catch (Throwable $th) {
            ResponseService::logErrorResponse($th, "Onboarding Controller -> update");
            ResponseService::errorResponse();
        }
    }
    public function destroy(string $id)
    {
        try {
            $onboarding = Onboarding::findOrFail($id);
            $onboarding->delete();
            ResponseService::successResponse('Onboarding delete successfully');
        } catch (Throwable $th) {
            ResponseService::logErrorResponse($th, "Onboarding Controller -> destroy");
            ResponseService::errorResponse('Something Went Wrong');
        }
    }
    public function onboardingstyle(){
        $onboardings = Onboarding::all();
        return view('onboarding.onboardingstyle',compact('onboardings'));
    }
}
