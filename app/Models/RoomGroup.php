<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomGroup extends Model
{
    use HasFactory;

    protected $table = 'room_groups';

    public function rooms() {
        return $this->belongsTo(Room::class, 'room_id');
    } 

    public function faculties() {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    } 
}
