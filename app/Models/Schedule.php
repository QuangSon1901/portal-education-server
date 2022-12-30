<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'lesson_end',
        'lesson_start',
        'class_subject_id',
        'room_id',
        'teacher_id',
        'day_on_week',
    ];
}
