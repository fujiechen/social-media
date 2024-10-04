<?php

namespace App\Models;

use App\Events\AlbumCategorySavedEvent;
use App\Models\Traits\HasAlbum;
use App\Models\Traits\HasCategory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $album_id
 * @property int $category_id
 * @property Album $album
 * @property Category $category
 */
class AlbumCategory extends Model
{
    use HasAlbum;
    use HasCategory;

    public $timestamps = false;

    protected $fillable = [
        'album_id',
        'category_id',
    ];

    protected $dispatchesEvents = [
        'saved' => AlbumCategorySavedEvent::class,
    ];
}
