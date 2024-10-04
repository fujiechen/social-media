<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductMedia extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'media_id',
    ];
}

