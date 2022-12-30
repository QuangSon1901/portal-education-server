<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeacherController extends Controller
{
    public function get_teachers_by_faculty($faculty_id) {
        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);
        
        $teachers = Teacher::get();
        if ($faculty_id != 0)
            $teachers = Teacher::where('faculty_id', $faculty_id)->get();

        foreach ($teachers as $item) {
            $result[] = [
                'id' => $item->id,
                'code' => $item->code,
                'name' => $item->name,
                'busy' => $item->busy,
                'faculty' => $item->faculties ? $item->faculties->ac_name : "Trống",
            ];
        }

        $response = [
            'status' => 201,
            'success' => 'success',
            'teachers' => $result,
        ];

        return response($response, 201);
    }
}
