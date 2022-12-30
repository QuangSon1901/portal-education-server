<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoomGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RoomController extends Controller
{
    public function get_rooms_by_faculty($faculty_id) {
        if (Gate::denies('role-admin')) return response(['message' => 'Xin lỗi! Bạn không có quyền thực hiện.'], 401);
        
        $roomGroups = RoomGroup::get();
        if ($faculty_id != 0)
            $roomGroups = RoomGroup::where('faculty_id', $faculty_id)->get();

        foreach ($roomGroups as $item) {
            $result[] = [
                'id' => $item->rooms->id,
                'code' => $item->rooms->code,
                'type_room' => $item->rooms->type_rooms->id,
                'type_room_name' => $item->rooms->type_rooms->name,
                'quantity' => $item->rooms->quantity,
                'faculty' => $item->faculties ? $item->faculties->ac_name : "Trống",
            ];
        }

        $response = [
            'status' => 201,
            'success' => 'success',
            'rooms' => $result,
        ];

        return response($response, 201);
    }
}
