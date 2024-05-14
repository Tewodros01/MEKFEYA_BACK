<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\User\UserController;
use Illuminate\Http\Request;
use Illuminate\support\Facades\Auth;
use Laravel\Passport\Client as OClient;
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserLoginController extends Controller
{
    public function loginUser(Request $request)
    {
        try {
           
            $login = $request->validate([
                'email' => 'required|string',
                'password' => 'required|string',
            ]);
            if (!Auth::attempt($login)) {
                return response(['message' => 'invalid Login information']);
            }

            // reference the User model
            $userID = Auth::id();
            $user = User::find($userID);
            if (Auth::check()) {
                $user = Auth::user();
                if ($user->active === 0) {
                    return response(['message' => 'your account is  inactive ']);
                }
            }
            $token = $user->createToken('Alephtav')->accessToken;
            $user->update([
                'Last_login_at' => Carbon::now()->toDateTimeString(),
                'Last_login_ip' => $request->getClientIp(),
            ]);

            $userloggedin = new UserController();
            $id = $user->id;
            $userdata = $userloggedin->getSingleUser($id);

            return response([
                'user' => $userdata,
                'token' => $token
            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'error' => 'An error occurred while processing the request.',
                'exception_message' => $th->getMessage(),
            ], 500);
        }
    }

    public function getTokenAndRefreshToken(OClient $oClient, $email, $password)
    {
        $oClient = OClient::where('password_client', 1)->first();
        dd($oClient);
        $http = new Client;
        $response = $http->request('POST', 'http://mylemp-nginx/oauth/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $oClient->id,
                'client_secret' => $oClient->secret,
                'username' => $email,
                'password' => $password,
                'scope' => '*',
            ],
        ]);
        $result = json_decode((string) $response->getBody(), true);
        return response()->json($result, 200);
    }
}
