<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Onboarding extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
        'description',
        'status'
    ];
    public function getImageAttribute($image) {
        if (!empty($image)) {
            return url(Storage::url($image));
        }
        return $image;
    }


}
