<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    public function type_rooms() {
        return $this->belongsTo(TypeRoom::class, 'type_room_id');
    } 
}
