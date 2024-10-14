<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Models\UserPurchasedPackage;
use App\Models\Package;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RegisterController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data) {
        return Validator::make($data, [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return User
     */
    protected function create(array $data) {
        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
    public function registerWithPackage(Request $request)
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
            'package_id' => ['required', 'exists:packages,id'],
        ]);

        // Return validation errors if any
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        try {
            // Create a new user
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Find the selected package
            $package = Package::findOrFail($request->package_id);

            // Calculate start and end dates based on package duration
            $startDate = Carbon::now();
            $endDate = $package->duration !== 'unlimited' ? $startDate->copy()->addDays($package->duration) : null;

            // Create a record in user_purchased_packages table
            UserPurchasedPackage::create([
                'user_id'     => $user->id,
                'package_id'  => $package->id,
                'start_date'  => $startDate,
                'end_date'    => $endDate,
                'total_limit' => $package->item_limit !== 'unlimited' ? $package->item_limit : null,
                'used_limit'  => 0,
            ]);

            // Return success response
            return response()->json(['message' => 'User registered with package successfully'], 200);

        } catch (\Throwable $th) {
            // Log the error for debugging purposes
            \Log::error("Error registering user with package: {$th->getMessage()}");

            // Return a generic error response
            return response()->json(['error' => 'Failed to register user with package'], 500);
        }
    }
}
