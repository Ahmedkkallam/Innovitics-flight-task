<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    protected $fillable = [
        'originCity','destinationCity','price','takeOffTime','landingTime'
    ];
    protected $hidden = [
        'id','created_at','updated_at',
    ];
}
