<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'code' => 'required|string',
                'password' => 'required|string',
            ],
            [
                'required' => ':attribute không được để trống',
                'string' => ':attribute phải là một chuỗi',
            ],
            [
                'code' => 'Mã sinh viên',
                'password' => 'Mật khẩu',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()->first()], 403);
        }

        $checkAccount = Account::where('code', $request->code)->first();

        if (!$checkAccount || !Hash::check($request->password, $checkAccount->password)) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Email hoặc Mật khẩu không chính xác!'
        ], 401);

        $token = $checkAccount->createToken('usertoken')->plainTextToken;

        if ($checkAccount->roles->name === 'ROLE_ADMIN') {
            $response = [
                'status' => 201,
                'success' => 'success',
                'message' => 'Đăng nhập thành công!',
                'type' => 'a',
                'token' => $token
            ];
        } else {
            $result = [
                "id" => $checkAccount->students->id,
                "name" => $checkAccount->students->name,
                "code" => $checkAccount->students->code,
                "birth" => $checkAccount->students->birth,
                "avatar" => $checkAccount->students->avatar,
                "address" => $checkAccount->students->address,
                "email" => $checkAccount->students->email,
                "email_school" => $checkAccount->students->email_school,
                "personal_no" => $checkAccount->students->personal_no,
                "phone" => $checkAccount->students->phone,
                "status" => $checkAccount->students->status,
                "team" => $checkAccount->students->teams->code,
                "program" => $checkAccount->students->teams->programs->name,
                "faculty" => $checkAccount->students->teams->facultys->name,
                "year_start" => $checkAccount->students->teams->years->year_start,
                "year_end" => $checkAccount->students->teams->years->year_end,
            ];

            $response = [
                'status' => 201,
                'success' => 'success',
                'message' => 'Đăng nhập thành công!',
                'user' => $result,
                'type' => 's',
                'token' => $token
            ];
        }

        


        return response($response, 201);
    }

    public function getInfo(Request $request) {
        $checkAccount =  auth('sanctum')->user();

        if ($checkAccount->roles->name === 'ROLE_ADMIN') {
            $response = [
                'status' => 201,
                'success' => 'success',
                'message' => 'Đăng nhập thành công!',
                'type' => 'a',
            ];
        } else { 
            $result = [
                "id" => $checkAccount->students->id,
                "name" => $checkAccount->students->name,
                "code" => $checkAccount->students->code,
                "birth" => $checkAccount->students->birth,
                "avatar" => $checkAccount->students->avatar,
                "address" => $checkAccount->students->address,
                "email" => $checkAccount->students->email,
                "email_school" => $checkAccount->students->email_school,
                "personal_no" => $checkAccount->students->personal_no,
                "phone" => $checkAccount->students->phone,
                "status" => $checkAccount->students->status,
                "team" => $checkAccount->students->teams->code,
                "program" => $checkAccount->students->teams->programs->name,
                "faculty" => $checkAccount->students->teams->facultys->name,
                "year_start" => $checkAccount->students->teams->years->year_start,
                "year_end" => $checkAccount->students->teams->years->year_end,
            ];
    
            $response = [
                'status' => 201,
                'success' => 'success',
                'user' => $result,
                'type' => 's'
            ];
        }
        

        return response($response, 201);
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        $response = [
            'status' => 201,
            'success' => 'success',
            'message' => 'Đăng xuất thành công!'
        ];


        return response($response, 201);
    }

    public function changePass(Request $request) {
        $checkAccount =  auth('sanctum')->user();

        if (!$checkAccount || !Hash::check($request->password, $checkAccount->password)) return response([
            'status' => 401,
            'success' => 'danger',
            'message' => 'Mật khẩu không chính xác!'
        ], 401);

        Account::find($checkAccount->id)->update([
            'password' => bcrypt($request->newPassword)
        ]);

        $response = [
            'status' => 201,
            'success' => 'success',
            'message' => 'Thay đổi mật khẩu thành công!'
        ];


        return response($response, 201);
    }
}
