<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    public function programs() {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function facultys() {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    }

    public function years() {
        return $this->belongsTo(Year::class, 'year_id');
    }

    public function students() {
        return $this->hasMany(Student::class);
    }
}
