<?php

namespace App\Http\Requests\AlbumQueue;

use App\Dtos\AlbumQueueDto;
use App\Dtos\BucketFileDto;
use App\Dtos\MetaDto;
use App\Dtos\ResourceActorDto;
use App\Dtos\ResourceAlbumDto;
use App\Dtos\ResourceCategoryDto;
use App\Dtos\ResourceTagDto;
use App\Http\Requests\HttpRequestInterface;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CompleteAlbumQueueRequest extends FormRequest implements HttpRequestInterface
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
            'id' => 'required|exists:App\Models\AlbumQueue,id',
            'name' => 'required|string',
            'description' => 'nullable',
            'images.*.bucket_type' => 'required|in:private,public',
            'images.*.bucket_name' => 'required|string',
            'images.*.bucket_path' => 'required|string',
            'tags.*.name' => 'required',
            'categories.*.name' => 'required',
        ];
    }

    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function authorize(): bool
    {
        return Auth::user()->hasRole(Role::ROLE_ADMINISTRATOR_ID);
    }

    function toDto(): AlbumQueueDto
    {
        $resourceAlbumFileDtos = [];
        foreach ($this->input('resource_album_files', []) as $resourceAlbumFile) {
            $resourceAlbumFileDtos[] = new BucketFileDto([
                'fileId' => 0,
                'bucketType' => $resourceAlbumFile['bucket_type'],
                'bucketName' => $resourceAlbumFile['bucket_name'],
                'bucketFileName' => basename($resourceAlbumFile['bucket_path'],),
                'bucketFilePath' => $resourceAlbumFile['bucket_path'],
            ]);
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

        $metaDtos = [];
        foreach ($this->input('metas', []) as $meta) {
            $metaDtos[] = new MetaDto([
                'meta_key' => $meta['meta_key'],
                'meta_value' => $meta['meta_value'],
            ]);
        }

        return new AlbumQueueDto([
            'albumQueueId' => $this->route('id'),
            'resourceAlbumDto' => new ResourceAlbumDto([
                'resourceId' => 0,
                'resourceAlbumId' => 0,
                'name' => $this->input('name'),
                'description' => $this->input('description'),
                'resourceAlbumFileDtos' => $resourceAlbumFileDtos,
                'downloadFileDto' => $downloadFileDto,
                'resourceTagDtos' => $resourceTagsDtos,
                'resourceActorDtos' => $resourceActorDtos,
                'resourceCategoryDtos' => $resourceCategoryDtos,
                'metaJson' => $metaDtos,
            ]),
        ]);
    }
}
