<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'contenu',
        'image_url',
    ];

    public function commentaires()
    {
        return $this->hasMany(Commentaire::class);
    }
}
