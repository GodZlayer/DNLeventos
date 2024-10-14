<?php
namespace App\Http\Controllers;
use App\Models\Draweritem;
use App\Services\BootstrapTableService;
use App\Services\FileService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;
class DrawerController extends Controller
{
    private string $uploadFolder;
    public function __construct() {
        $this->uploadFolder = "draweritem";
    }
    public function index()
    {
        return view('draweritem.index');
    }
    public function create()
    {
    }
    public function store(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
           'title'       => 'required|string|max:255',
           'image'       => 'required|nullable|image|mimes:jpeg,png,jpg',
           'url'         => 'required|url',
           'status'      => 'boolean',
        ]);
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }
        $data = $request->all();
        $data['status'] = $request->has('status') ? 1 : 0;
        if ($request->hasFile('image')) {
            $data['image'] = FileService::compressAndUpload($request->file('image'), $this->uploadFolder);
        }
        Draweritem::create($data);
        ResponseService::successResponse("Draweritem Added Successfully");
    } catch (Throwable $th) {
        ResponseService::logErrorResponse($th, "Draweritem Controller -> store");
        return redirect()->back()->with('error', 'Something went wrong.');
    }
}
    public function show(Request $request)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'ASC');
        $sql = Draweritem::where('id', '!=', 0);
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")->orwhere('title', 'LIKE', "%$search%");
        }
        $total = $sql->count();
        $sql->skip($offset)->take($limit);
        $result = $sql->get();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $no = 1;
        foreach ($result as $row) {
            $operate = '';
            $operate .= BootstrapTableService::editButton(route('draweritem.update', $row->id), true, '#editModal', 'draweritemEvents', $row->id);
            $operate .= BootstrapTableService::deleteButton(route('draweritem.destroy', $row->id));
            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            $tempRow['status'] = $row->status;
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }
    public function edit(string $id)
    {
        $appsettings = Draweritem::findOrFail($id);
        return response()->json($appsettings);
    }
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title'       => 'required|string|max:255',
                'image'       => 'required|nullable|image|mimes:jpeg,png,jpg',
                'url'         => 'required|url',
                'status'      => 'boolean',
             ]);
             if ($validator->fails()) {
                 ResponseService::validationError($validator->errors()->first());
             }
            $appsettings = Draweritem::findOrFail($request->edit_id);
            $data = $request->all();
            $data['status'] = $request->status ? 1 : 0;
            if ($request->hasFile('image')) {
                $data['image'] = FileService::compressAndUpload($request->file('image'), $this->uploadFolder);
            }
            $appsettings->update($data);
            ResponseService::successResponse('Draweritem Updated Successfully');
        } catch (Throwable $th) {
            ResponseService::logErrorResponse($th, "Draweritem Controller -> update");
            ResponseService::errorResponse();
        }
    }
    public function destroy(string $id)
    {
        try {
            $appsettings = Draweritem::findOrFail($id);
            $appsettings->delete();
            ResponseService::successResponse('draweritem delete successfully');
        } catch (Throwable $th) {
            ResponseService::logErrorResponse($th, "Appsetting Controller -> destroy");
            ResponseService::errorResponse('Something Went Wrong');
        }
    }
}
