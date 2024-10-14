<?php
namespace App\Http\Controllers;
use App\Models\Fcm;
use App\Models\Notifications;
use App\Services\BootstrapTableService;
use App\Services\FileService;
use App\Services\NotificationService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;
class NotificationController extends Controller {
    private string $uploadFolder;
    public function __construct() {
        $this->uploadFolder = "notification";
    }
    public function index() {
        return view('notification.index');
    }


    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'image' => 'image|mimes:jpeg,png,jpg|max:2048', // Added max file size of 2MB
        ]);

        if ($validator->fails()) {
            return ResponseService::validationError($validator->errors()->first());
        }

        try {
            $notificationData = [
                'title' => $request->title,
                'message' => $request->message,
            ];

            if ($request->hasFile('image')) {
                // $image = $request->file('image');
                // $imageName = time() . '.' . $image->getClientOriginalExtension();

                // // Define the storage path
                // $storagePath = public_path('notifications');

                // // Create the directory if it doesn't exist
                // if (!file_exists($storagePath)) {
                //     mkdir($storagePath, 0755, true);
                // }

                // // Move the uploaded file to the storage location
                // $image->move($storagePath, $imageName);
                $notificationData['image'] = $request->file('image')->store('notifications', 'public');

                // Save the relative path to the database
                // $notificationData['image'] = 'notifications/' . $imageName;
            }

            $notification = Notifications::create($notificationData);

            $fcm_ids = Fcm::pluck('fcm')->toArray();
            if (!empty($fcm_ids)) {
                $registrationIDs = array_filter($fcm_ids);
                NotificationService::sendFcmNotification(
                    $registrationIDs,
                    $request->title,
                    $notification->image ?? null,
                    $request->message,
                    'default',
                    [$notification->image]
                );
            }

            return ResponseService::successResponse('Notification created successfully.');
        } catch (Throwable $th) {
            throw $th;
            ResponseService::logErrorResponse($th, 'NotificationController -> store');
            return ResponseService::errorResponse('Something Went Wrong');
        }
    }
    public function destroy($id) {
        try {
            $notification = Notifications::findOrFail($id);
            $notification->delete();
            FileService::delete($notification->getRawOriginal('image'));
            ResponseService::successResponse('Notification Deleted Successfully');
        } catch (Throwable $th) {
            ResponseService::logErrorResponse($th, 'NotificationController -> destroy');
            ResponseService::errorResponse('Something Went Wrong');
        }
    }
    public function show(Request $request) {
        $offset = $request->offset ?? 0;
        $limit = $request->limit ?? 10;
        $sort = $request->sort ?? 'id';
        $order = $request->order ?? 'DESC';
        $sql = Notifications::where('id', '!=', 0)->orderBy($sort, $order);
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")->orwhere('title', 'LIKE', "%$search%")->orwhere('message', 'LIKE', "%$search%");
        }
        $total = $sql->count();
        $sql->skip($offset)->take($limit);
        $result = $sql->get();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        foreach ($result as $key => $row) {
            $tempRow = $row->toArray();
            $operate = '';
            $operate .= BootstrapTableService::deleteButton(route('notification.destroy', $row->id));
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }
    public function batchDelete(Request $request) {
        try {
            foreach (Notifications::whereIn('id', explode(',', $request->id))->get() as $row) {
                $row->delete();
                FileService::delete($row->getRawOriginal('image'));
            }
            ResponseService::successResponse("Notification Deleted Successfully");
        } catch (Throwable $th) {
            ResponseService::logErrorResponse($th, "NotificationController -> batchDelete");
            ResponseService::errorResponse('Something Went Wrong');
        }
    }
}
