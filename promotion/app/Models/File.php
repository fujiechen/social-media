<?php

namespace App\Models;

use App\Events\FileSavedEvent;
use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $upload_path
 * @property string $url
 * @property string $bucket_type
 * @property string $bucket_name
 * @property string $bucket_file_name
 * @property string $bucket_file_path
 * @property string $created_at
 * @property string $updated_at
 */
class File extends Model
{
    use HasCreatedAt;
    use HasUpdatedAt;

    protected $fillable = [
        'name',
        'upload_path',
        'bucket_type',
        'bucket_name',
        'bucket_file_name',
        'bucket_file_path',
        'created_at',
        'updated_at'
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
        'url'
    ];

    protected $dispatchesEvents = [
        'saved' => FileSavedEvent::class,
    ];

    const TYPE_PRIVATE_BUCKET = 'private';
    const TYPE_PUBLIC_BUCKET = 'public';
    const TYPE_LOCAL_BUCKET = 'local';

    public function getUrlAttribute(): string {
        if ($this->upload_path) {
            return Storage::disk('admin')->url($this->upload_path);
        } else {
            if ($this->bucket_type == self::TYPE_PUBLIC_BUCKET) {
                $url = Storage::disk('s3-public')->url($this->bucket_file_path);
                $nonCdnEndpoint = config('filesystems.disks.s3-public.endpoint');
                $cdnEndpoint = config('filesystems.disks.s3-public.endpoint_cdn');
                return Str::replace($nonCdnEndpoint, $cdnEndpoint, $url);
            } else if (($this->bucket_type == self::TYPE_PRIVATE_BUCKET)) {
                $url = Storage::disk('s3')->temporaryUrl($this->bucket_file_path, now()->addMinutes(60));
                $nonCdnEndpoint = config('filesystems.disks.s3.endpoint');
                $cdnEndpoint = config('filesystems.disks.s3.endpoint_cdn');
                return Str::replace($nonCdnEndpoint, $cdnEndpoint, $url);
            } else {
                return Storage::disk('admin')->url($this->upload_path);
            }
        }
    }
}
