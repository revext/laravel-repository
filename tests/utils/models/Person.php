<?php

namespace Revext\Repository\Utils\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model{
    use SoftDeletes;

    protected $fillable = ['name', 'email'];

    public $timestamps = false;

    protected $dates = ['deleted_at'];

    public function dogs(){
        return $this->hasMany(Person::class);
    }

    public function getNamailAttribute() {
        return $this->name . ' ' . $this->email;
    }
}