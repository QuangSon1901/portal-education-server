<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function getAll(Request $request) {
        $teams = Team::get();
        foreach ($teams as $team) {
            $result[] = [
                'id' => $team->id,
                'code' => $team->code,
                'program' => $team->programs->name,
                'year_start' => $team->years->year_start,
                'year_end' => $team->years->year_end,
                'faculty' => $team->facultys->ac_name,
                'quantity_student' => $team->students->count(),
                'status' => $team->status,
            ];
        }
        $response = [
            'status' => 201,
            'success' => 'success',
            'teams' => $result,
        ];

        return response($response, 201);
    }

    
}
