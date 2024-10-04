<?php

namespace App\Http\Requests\VideoQueue;

use App\Dtos\BucketFileDto;
use App\Dtos\MetaDto;
use App\Dtos\ResourceActorDto;
use App\Dtos\ResourceCategoryDto;
use App\Dtos\ResourceTagDto;
use App\Dtos\ResourceVideoDto;
use App\Dtos\VideoQueueDto;
use App\Http\Requests\HttpRequestInterface;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CompleteVideoQueueRequest extends FormRequest implements HttpRequestInterface
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules(): array
    {
        $this->merge(['id' => $this->route('id')]);
        return [
            'id' => 'required|exists:App\Models\VideoQueue,id',
            'name' => 'required|string',
            'description' => 'nullable',
            'duration_seconds' => 'nullable',
            'video_file.bucket_type' => 'required|in:private,public',
            'video_file.bucket_name' => 'required|string',
            'video_file.bucket_path' => 'required|string',
            'tags.*.name' => 'required',
            'categories.*.name' => 'required',
            'actors.*.name' => 'required',
            'actors.*.country' => 'nullable',
        ];
    }

    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function authorize(): bool
    {
        return Auth::user()->hasRole(Role::ROLE_ADMINISTRATOR_ID);
    }

    function toDto(): VideoQueueDto
    {
        $resourceTagsDtos = null;
        foreach ($this->input('tags', []) as $resourceTag) {
            $resourceTagsDtos[] = new ResourceTagDto(['name' => $resourceTag['name']]);
        }

        $resourceActorDtos = null;
        foreach ($this->input('actors', []) as $resourceActor) {
            $resourceActorDtos[] = new ResourceActorDto([
                'name' => $resourceActor['name'],
                'country' => $resourceActor['country'] ?? ''
            ]);
        }

        $resourceCategoryDtos = null;
        foreach ($this->input('categories', []) as $resourceCategory) {
            $resourceCategoryDtos[] = new ResourceCategoryDto(['name' => $resourceCategory['name']]);
        }

        $downloadFileDto = null;
        if ($this->input('download_file')) {
            $downloadFileDto = new BucketFileDto([
                'fileId' => 0,
                'bucketType' => $this->input('download_file.bucket_type'),
                'bucketName' => $this->input('download_file.bucket_name'),
                'bucketFileName' => basename($this->input('download_file.bucket_path')),
                'bucketFilePath' => $this->input('download_file.bucket_path'),
            ]);
        }

        $thumbnailFileDto = null;
        if ($this->input('thumbnail_file')) {
            $thumbnailFileDto = new BucketFileDto([
                'fileId' => 0,
                'bucketType' => $this->input('thumbnail_file.bucket_type'),
                'bucketName' => $this->input('thumbnail_file.bucket_name'),
                'bucketFileName' => basename($this->input('thumbnail_file.bucket_path')),
                'bucketFilePath' => $this->input('thumbnail_file.bucket_path'),
            ]);
        }

        $previewFileDto = null;
        if ($this->input('preview_file')) {
            $previewFileDto = new BucketFileDto([
                'fileId' => 0,
                'bucketType' => $this->input('preview_file.bucket_type'),
                'bucketName' => $this->input('preview_file.bucket_name'),
                'bucketFileName' => basename($this->input('preview_file.bucket_path')),
                'bucketFilePath' => $this->input('preview_file.bucket_path'),
            ]);
        }

        $metaDtos = [];
        foreach ($this->input('metas', []) as $meta) {
            $metaDtos[] = new MetaDto([
                'meta_key' => $meta['meta_key'],
                'meta_value' => $meta['meta_value'],
            ]);
        }

        return new VideoQueueDto([
            'videoQueueId' => $this->route('id'),
            'resourceVideoDto' => new ResourceVideoDto([
                'resourceId' => 0,
                'resourceVideoId' => 0,
                'name' => $this->input('name', ''),
                'description' => $this->input('description'),
                'durationInSeconds' => $this->input('duration_in_seconds'),
                'thumbnailFileDto' => $thumbnailFileDto,
                'previewFileDto' => $previewFileDto,
                'videoFileDto' => new BucketFileDto([
                    'fileId' => 0,
                    'bucketType' => $this->input('video_file.bucket_type'),
                    'bucketName' => $this->input('video_file.bucket_name'),
                    'bucketFileName' => basename($this->input('video_file.bucket_path')),
                    'bucketFilePath' => $this->input('video_file.bucket_path'),
                ]),
                'downloadFileDto' => $downloadFileDto,
                'resourceTagDtos' => $resourceTagsDtos,
                'resourceActorDtos' => $resourceActorDtos,
                'resourceCategoryDtos' => $resourceCategoryDtos,
                'metaJson' => $metaDtos,
            ]),
        ]);
    }
}
