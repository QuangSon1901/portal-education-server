<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UmbrellaProgram extends Model
{
    use HasFactory;

    protected $table = "umbrella_programs";

    public function subjects() {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
