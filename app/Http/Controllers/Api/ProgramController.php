<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function getAll(Request $request) {
        $programs = Program::get();
        $response = [
            'status' => 201,
            'success' => 'success',
            'programs' => $programs,
        ];

        return response($response, 201);
    }

    public function store(Request $request) {
        
        Program::create([
            'name' => $request->name,
            'ac_name' => $request->ac_name
        ]);

        $response = [
            'status' => 201,
            'success' => 'success',
            'programs' => Program::get()
        ];

        return response($response, 201);
    }

    public function delete($program_id) {
        
        $program = Program::find($program_id);
        if (!$program) return response(['status' => 'danger'], 403);

        $program->delete();

        $response = [
            'status' => 201,
            'success' => 'success',
            'programs' => Program::get()
        ];

        return response($response, 201);
    }
}
