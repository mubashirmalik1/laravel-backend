<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\TokenRequest;
use Auth;
use Hash;
use DB;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'email' => 'required',
                'password' => 'required',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['data' => null,'meta'=> ['message' => 'Validation Error','status'=> 401,'errors'=>$validator->errors()]]);
        }
        $user=User::where('email',request('email'))->orWhere('name',request('email'))->count();
        if($user!=0)
        {
            $identity = request('email');

            if(Auth::attempt([filter_var($identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'name' => request('email'), 'password' => request('password')])){
                $user = Auth::user();
                $tokenResult = $user->createToken('Personal Access Token');
                $token = $tokenResult->token;
                if ($request->remember_me)
                    $token->expires_at = Carbon::now()->addWeeks(1);
                $token->save();
                if(isset($input['device_token'])) {
                    $user->device_token = $request->device_token;
                    $user->save();
                }
                $success=[
                    'access_token' => $tokenResult->accessToken,
                    'user'         => new UserResource($user),
                ];
                return response()->json(['data' => $success,'meta'=> ['message' => 'Successfull','status'=> 200,'errors'=>null]]);

            }
            else{
                return response()->json(['data' => null,'meta'=> ['message' => 'Validation Error','status'=> 401,'errors'=>['password' => 'Password Not Matched']]]);

            }
        }
        else
        {
            return response()->json(['data' => null,'meta'=> ['message' => 'Validation Error','status'=> 401,'errors'=>['email' => 'Email Not Found']]]);

        }
    }

    public function register(RegisterRequest $request){
        $data = $request->only([
            'name',
            'email',
            'password',
            'role',
            'app_name'
        ]);
        DB::beginTransaction();
        try {
                $user = new User();
                $user->name = $data['name'];
                $user->email = $data['email'];
                $user->password = \Illuminate\Support\Facades\Hash::make($data['password']);
                $user->save();

                $tokenResult = $user->createToken('Personal Access Token');
                $token = $tokenResult->token;
                $token->save();
                $success['access_token'] =   $tokenResult->accessToken;
                $success['user'] =  new UserResource($user);
                DB::commit();
                return response()->json(['data' => $success,'meta'=> ['message' => 'Successfull','status'=> 200,'errors'=>null]]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['data' => null,'meta'=> ['message' => 'Error','status'=> 401,'errors'=>$e->getMessage()]]);
        }
    }

    public function getUsers(){
        DB::beginTransaction();
        try {
            $user = User::where('id' ,'!=', auth()->id())->get();
            return response()->json(['data' => UserResource::collection($user),'meta'=> ['message' => 'Successfull','status'=> 200,'errors'=>null]]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['data' => null,'meta'=> ['message' => 'Error','status'=> 401,'errors'=>$e->getMessage()]]);
        }
    }

    public function user(){
        DB::beginTransaction();
        try {
            $user = User::find(auth()->id());
            return response()->json(['data' =>new UserResource($user),'meta'=> ['message' => 'Successfull','status'=> 200,'errors'=>null]]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['data' => null,'meta'=> ['message' => 'Error','status'=> 401,'errors'=>$e->getMessage()]]);
        }
    }
}
