<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transactions extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    // relasi ke user
    public function food(){
        return $this->hasOne('App\Food','food_id');
    }

    public function user(){
        return $this->hasOne('App\User','user_id');
    }

    // convert crated menjadi epoch time
    // accessor
    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->timestamp;
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->timpestamp;
    }
}
