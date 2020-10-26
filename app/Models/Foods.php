<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Foods extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];


    // convert crated menjadi epoch time
    // accessor
    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->timestamp;
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->timpestamp;
    }
    
    // accessor untuk nama field yang camelcase
    // karena di laravel tidak mensuipport camel case
    public function toArray(){
        $toArray = parent::toArray();
        $toArray['picturePath'] = $this->picturePath;
        return $toArray;
    }

    // accesor untuk gull picture path
    public function getPicturePathAttribute(){
        return url(). Storage::url($this->attributes['picturePath']);
    }
}
