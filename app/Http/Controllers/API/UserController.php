<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Auth;


class UserController extends Controller
{
    /**
     * login User
     */
    public function loginUser(Request $request): Response
    {
        $input = $request->all();

        Auth::attempt($input);

        $user  = Auth::user();
        $token = $user->createToken('example',)->accessToken; 

        return Response(['status' => 200,'token' => $token],200);
    }

    /**
     * get detail user login
     */
    public function getUserDetail(Request $request): Response
    {
        if(Auth::guard('api')->check()){
            $user = Auth::guard('api')->user();
            return Response(['data' => $user], 200);
        }else{
            return Response(['data' => 'Unauthorized false'], 401);
        }

 
    }

    /**
     * Logout user
     */
    public function userLogout(User $user)
    {
        if(Auth::guard('api')->check()){

            $accessToken = Auth::guard('api')->user()->token();

            \DB::table('oauth_refresh_tokens')
                ->where('access_token_id',$accessToken->id)
                ->update([
                    'revoked'=> true
                ]);
                $accessToken->revoke();
            
            return Response(['data' => 'Unauthorized','message' => 'User Logout Successfully.'], 200);

        }else{
            return Response(['data' => 'Unauthorized false'], 401);
        }
    }

    /**
     * Create user
     */
    public function userCreate(Request $request): Response
    {
        $input    = $request->all();
        $password = bcrypt($input['password']);
        $name     = $input['name'];
        $email    = $input['email'];
        $now      = date('Y-m-d H:i');

        $getCheck = DB::table('users')->select('name')
        ->where('email', $email)
        ->get();

        if(count($getCheck) > 0){
            return Response(['data' => 'user already exists'], 401);
        }else{
            $insert =  DB::table('users')->insert([
                'name'              => $name,
                'email'             => $email,
                'password'          => $password,
                'email_verified_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now
            ]);

            return Response(['data' => [
                'email' => $email,
                'name'  => $name
            ],  'message' => 'create Successfully.'], 200);
        }

    }

    /**
     * Update user
     */
    public function update(Request $request): Response
    {
        $input    = $request->all();
        $password = bcrypt($input['password']);
        $id       = $input['id'];
        $name     = $input['name'];
        $email    = $input['email'];
        $now      = date('Y-m-d H:i');

        $getCheckEmail = DB::table('users')->select('name')
        ->where('email', $email)
        ->get();

        $getCheckData = DB::table('users')->select('name')
        ->where('id', $id)
        ->get();

        if(count($getCheckEmail) > 0){
            return Response(['data' => 'user email already exists'], 401);
        }

        if(count($getCheckData) == 0){
            return Response(['data' => 'user update Not Found '], 401);
        }else{
            $update = DB::table('users')
              ->where('id', $id)
              ->update([
                    'name'              => $name,
                    'email'             => $email,
                    'password'          => $password,
                    'email_verified_at' => $now,
                    'updated_at'        => $now
                ]);

            return Response(['data' => [
                'email' => $email,
                'name'  => $name
            ],  'message' => 'Update Successfully.'], 200);
        }


    }

    /**
     * List User
    */
    public function list(Request $request): Response
    {
        $dataUser = DB::table('users')->select('*')->get();
        return Response(['data' => $dataUser,  'message' => 'list Successfully.'], 200);
    }

     /**
     * Delete User
    */
    public function delete(Request $request, $id): Response
    {
        $getCheckData = DB::table('users')->select('name')
        ->where('id', $id)
        ->get();

        if(count($getCheckData) == 0){
            return Response(['data' => 'User delete Not Found '], 401);
        }else{
            $deleted = DB::table('users')->where('id',$id)->delete();
            return Response(['data' => 'Delete Successfully.'], 200);
        }


    }


}
