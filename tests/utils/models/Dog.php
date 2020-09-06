<?php

namespace Revext\Repository\Utils\Models;

use Illuminate\Database\Eloquent\Model;

class Dog extends Model{

    protected $fillable = ['name', 'user_id'];

    public $timestamps = false;

    public function person(){
        return $this->belongsTo(Dog::class);
    }
}