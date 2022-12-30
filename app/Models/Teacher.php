<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    public function faculties() {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    } 
}
