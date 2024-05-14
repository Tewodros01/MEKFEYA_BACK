<?php

namespace app\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\User\UserController;
use App\Models\User;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class UserRegisterController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'gender' => 'required',
                'email' => 'required|email|unique:users',
                'phone_number' => 'required',
                'password' => 'required',
                'role_id' => 'required|exists:roles,id',
                'location_id' => 'required|exists:locations,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 400);
            }

            $imagePath = '';

            if ($request->hasFile('image')) {
                $name = $request->file('image')->getClientOriginalName();
                $request->image->move(public_path('userimage'), $name);
                $imagePath = 'userimage/' . $name;
            }

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'gender' => $request->gender,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => bcrypt($request->password),
                'location_id' => $request->location_id,
                'image_path' => $imagePath,
                'imagename' => $name ?? '',
                'email_verified_at' => now(),
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
                'role_id' => $request->role_id,
                'active' => 1,
                'theme_setting' => null,
            ]);

            $id = $user->id;
            $userData= new UserController();
            $userRegistered = $userData->getSingleUser($id);

            if($user){
                DB::commit();
            }

            $userMail = [
                'selector' => 2,
                'email' => $request->email,
            ];

            $first_name = $request->first_name;
            $assignEmail = $request->email;

            $roleLocationPermissions = $userRegistered->roleLocationPermissions;

            // Mail::send('emails.RegisterEmail', $userMail, function ($message) use ($assignEmail, $first_name) {
            //     $message->to($assignEmail, $first_name)
            //         ->subject('Welcome');
            // });
            return response([
                'user' => $userRegistered,
                'role_location_permissions' => $roleLocationPermissions,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'error' => 'An error occurred while processing the request.',
                'exception_message' => $th->getMessage(),
            ], 500);
        }
    }


    public function loginUser(Request $request)
    {
        try {
            $login = $request->validate([
                'email' => 'required|string',
                'password' => 'required|string'
            ]);
            if (!Auth::attempt($login)) {
                return response(['message' => 'invalid Login information']);
            };
            $userID = Auth::id();
            $user = User::find($userID);

            $token = $user->createToken('Alephtav')->accessToken;
            $user->update([
                'Last_login_at' => Carbon::now()->toDateTimeString(),
                'Last_login_ip' => $request->getClientIp()
            ]);

            $id = $user->id;
            $userregistered = new UserController();
            $userdata = $userregistered->getSingleUser($id);
            return response(['user' => $userdata, 'token' => $token], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'error' => 'An error occurred while processing the request.',
                'exception_message' => $th->getMessage(),
            ], 500);
        }
    }

}
