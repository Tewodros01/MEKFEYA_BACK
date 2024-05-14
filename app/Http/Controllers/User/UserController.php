<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\email;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash as FacadesHash;
class UserController extends Controller
{
    public function index()
    {
        try {
            $users = User::all();
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function indexGroupWithLocation()
    {
        try {
            $usersByLocation = User::with(['location'])->get()->groupBy('location_id');
            return response($usersByLocation);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function indexGroupWithRole()
    {
        try {
            $usersByRole = User::with(['role'])->get()->groupBy('role_id');
            return response($usersByRole);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request)
    {
        try {
            $user = User::findOrFail($request->id);
            return response()->json($user);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateUser(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = User::where('id', $request->id)->first();
            $userimage = DB::table('users')
                ->select('users.image_path')
                ->where('users.id', $user->id)
                ->pluck('image_path')->toArray();
            if ($request->image_path && $request->image_name) {
                $imagepath = $request->image_path;
                $name = $request->image_name;
            } else {
                if ($request->hasFile('image')) {
                    if ($user->imagename != " ") {
                        File::delete(public_path("userimage/" . $user->imagename));
                    }
                    $name = $request->file('image')->getClientOriginalName();
                    $request->image->move(public_path('userimage'), $name);
                    $imagepath = '../../userimage/' . $name;
                } else {
                    $imagepath = "";
                    $name = "";
                }
            }
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->phone_number = $request->phone_number;
            $user->email = $request->email;
            $user->gender = $request->gender;
            $user->location_id = $request->location_id;
            $user->image_path = $imagepath;
            $user->imagename = $name;
            $user->role_id= $request->role_id;
            $user->update();
          
            if ($user) {
                $id = $user->id;
                $userupdated = $this->GetSingleUser($id);
                DB::commit();
                return response()->json($userupdated, 201);
            } else {
                DB::rollback();
                return response()->json(['User profile not updated'], 304);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function inactiveUser($id)
    {
        DB::beginTransaction();
        try {
            $user = User::find($id)->first();
            $user->active = 0;
            $user->update();

            $user->tokens()->delete();   //log out the user deactivated from


            if ($user) {
                DB::commit();
                return response(["success" => "user successfully Deactivated"], 200);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response([
                "error" => "user failed to Deactivate something went wrong",
                "error_message" => $e->getMessage(),
            ], 500);
        }
    }
    public function activeUser($id)
    {
        DB::beginTransaction();
        try {
            $user = User::find($id)->first();
            $user->active = 1;
            $user->update();

            if ($user) {
                DB::commit();
                return response(["success" => "user successfully activated"], 200);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response([
                "error" => "user failed to activate something went wrong",
                "error_message" => $e->getMessage(),
            ], 500);
        }
    }


    public function deleteUser(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
            ]);

            // Find the user by ID
            $user = User::findOrFail($request->id);

            // Check if the requesting user has the necessary permissions (you can modify this as needed)
            // For example, you might have a method to check user permissions.
            // If the user doesn't have the required permissions, return a 403 Forbidden response.

            // Soft-delete the user
            $user->delete();

            return response()->json(['message' => 'User deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found.'], 404);
        } catch (ValidationException $validationException) {
            return response()->json([
                'error' => $validationException->validator->errors()->first(),
            ], 422); // Unprocessable Entity
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while processing the request.',
                'exception_message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSingleUser($id)
    {
        $user = User::with(['role.roleLocationPermissions', 'permissions', 'location'])->find($id);

        if ($user) {
            return $user;
        } else {
            return response()->json(['error' => 'User not found'], 404);
        }
    }

public function changePassword(Request $request){
  
         $user = User::where('id', $request->id)->first();
        $validator = Validator($request->all(), [
            'current_password' => ['required'],
            'new_password' => ['required'],
            'new_confirm_password' => ['same:new_password'],
          ]);
           
          $returnval= Hash::check($request->current_password, $user->password);
          if ($validator->fails())
           {
            $errors = $validator->errors()->getMessages();
            return response()->json($errors, 417);
          }
         elseif($returnval==false)
         {
            $errors="The current password does not match the old password.";
            return response()->json($errors, 417);
         }
          else {
         
         $user->update(['password'=> Hash::make($request->new_password)]);
            return response()->json("Password change successfully ", 200);
        }
    
}

public function forgotPassword(Request $request)
    {
        $validator = Validator($request->all(), [
          'email' => 'required|email|exists:users',
        ]);
        if ($validator->fails()) {
          return response()->json($validator->errors(), 404);
        }
        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert(
          ['email' => $request->email, 'token' => $token, 'created_at' => Carbon::now()]
        );
        $userMail = [
          'selector' => 1,
          'token' => $token,
          'email' => $request->email
        ];
        $assignemail = $request->email;
        Mail::to($assignemail)->send(new email($userMail));
        return response()->json(['We have e-mailed your password reset link!'], 200);
      }
    
      public function resetPassword(Request $request){
        
            $validator = Validator($request->all(), [
              'email' => 'required|email|exists:users',
              'password' => 'required|string|min:6|confirmed',
              'password_confirmation' => 'required'
        
            ]);
            if ($validator->fails()) {
              return response()->json($validator->errors(), 404);
            }
            $updatePassword = DB::table('password_reset_tokens')
              ->where([
                'email' => $request->email,
                'token' => $request->token
              ])
              ->first();
        
            if (!$updatePassword) {
              return response()->json(['invalid or expired reset  token!'], 403);
            }
            $user = User::where('email', $request->email)
              ->update(['password' => FacadesHash::make($request->password)]);
        
            DB::table('password_reset_tokens')->where(['email' => $request->email])->delete();
        
            return response()->json(['Your password has been changed successfully!'], 200);
          
      }

}
