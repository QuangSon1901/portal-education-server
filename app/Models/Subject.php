<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    public function type_subjects() {
        return $this->belongsTo(TypeSubject::class, 'type_subject_id');
    }

    public function class_subjects() {
        return $this->hasMany(ClassSubject::class);
    }

    public function subject_group() {
        return $this->belongsTo(SubjectGroup::class, 'subject_group_id');
    }
}
