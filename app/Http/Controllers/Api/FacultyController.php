<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FacultyController extends Controller
{
    public function get_faculty() {
        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);

        $faculties = Faculty::get();
        $response = [
            'status' => 201,
            'success' => 'success',
            'faculties' => $faculties,
        ];

        return response($response, 201);
    }
}
