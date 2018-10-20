<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\User;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    /**
	 * Get a JWT via given credentials.
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
    public function login(Request $request)
    {
    	$result = $this->validate($request , ['email'=>'required|string|email|max:255',
    											 'password'=>'required|string|min:6']);
    	if($result) return $result;

        $credentials = $request->only('email', 'password');

        if (! $token = auth()->attempt($credentials)) {
        	return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(['User' => auth()->user(), 'Token' => $this->respondWithToken($token)->original]);
    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
    	$result = $this->validate($request , ['name'=>'required|string|max:255|min:5',
    										  'email'=>'required|string|email|max:255|unique:users',
    										  'password'=>'required|string|min:6|confirmed']);
    	if($result) return $result;

    	$user = new User();
    	$user->name 	 	 = $request->name;
    	$user->email 	     = $request->email;
    	$user->password      = \Hash::make($request->password); // bcrypt($request->password);
    	// $user->api_token     = str_random(60);
    	$user->save();

        $credentials = $request->only('email', 'password');

        if (! $token = auth()->attempt($credentials)) {
        	return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(['User' => auth()->user(), 'Token' => $this->respondWithToken($token)->original]);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
