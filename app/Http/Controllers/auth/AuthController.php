<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Hash;
use App\Models\User;
use Auth;
class AuthController extends Controller
{
    public function register(Request $request)
    {
        /**Validate the data using validation rules
        */
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => ['required','string','min:6'],
        ]);
             
        /**Check the validation becomes fails or not
        */
        if ($validator->fails()) {
            /**Return error message
            */
            return response()->json([ 'error'=> $validator->errors() ]);
        }
    
        /**Store all values of the fields
        */
        $newuser = $request->all();
    
            /**Create an encrypted password using the hash
        */
        $newuser['password'] = Hash::make($newuser['password']);
        /**Insert a new user in the table
        */
        $user = User::create($newuser);
    
            /**Create an access token for the user
        */
        $success['token'] =  $user->createToken('super_admin', ['app:all'])->plainTextToken;
        /**Return success message with token value
        */
        return response()->json(['success'=>$success], 200);
    }
    /**************************************** */
    public function login(Request $request)
    {
        /**Read the credentials passed by the user
        */
        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];
    
        /**Check the credentials are valid or not
        */
        if( auth()->attempt($credentials) ){
            /**Store the information of authenticated user
            */
            $user = Auth::user();
            /**Create token for the authenticated user
            */
            $user->tokens()->delete();
            $success['token'] = $user->createToken('super_admin', ['app:all'])->plainTextToken;
            $success['name'] = $user->name;
    
            return response()->json(['success' => $success], 200);
        } else {
            /**Return error message
            */
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }
    /************************ */
    public function logout(Request $request)
    {
        $user = Auth::user();
        /**Create token for the authenticated user
        */
        $user->tokens()->delete();
        $success['status'] = "logged out";
        return response()->json(['success' => $success], 200);
    }
}
