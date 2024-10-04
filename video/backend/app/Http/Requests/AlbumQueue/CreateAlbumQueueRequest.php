<?php

namespace App\Http\Requests\AlbumQueue;

use App\Dtos\AlbumQueueDto;
use App\Http\Requests\HttpRequestInterface;
use App\Models\AlbumQueue;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateAlbumQueueRequest extends FormRequest implements HttpRequestInterface
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'resource_id' => 'required',
            'url' => 'required',
            'playlist_queue_id' => 'nullable'
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
        return new AlbumQueueDto([
            'resourceId' => $this->input('resource_id', []),
            'resourceAlbumUrl' => $this->input('url', []),
            'status' => AlbumQueue::STATUS_PENDING,
            'playlistQueueId' => $this->input('playlist_queue_id'),
            'mediaQueueId' => $this->input('media_queue_id'),
        ]);
    }
}
