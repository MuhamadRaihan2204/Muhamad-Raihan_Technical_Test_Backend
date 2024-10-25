<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticationController extends Controller
{
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request): JsonResponse
    {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $success['token'] =  $user->createToken('secToken')->plainTextToken;
            $success['name'] =  $user->name;

            return response()->json([
                'success' => true,
                'data' => $success,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'data' => 'Login failed',
            ], 401);
        }
    }

    public function logout(Request $request)
    {
        $user = auth('sanctum')->user();

        if ($user) {
            $user->tokens()->delete();
            return response([
                'message' => 'Successfully logged out'
            ]);
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out.',
        ], 200);
    }
}
