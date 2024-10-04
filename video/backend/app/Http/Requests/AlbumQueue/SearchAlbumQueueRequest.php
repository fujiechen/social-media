<?php

namespace App\Http\Requests\AlbumQueue;

use App\Dtos\AlbumQueueSearchDto;
use App\Http\Requests\HttpRequestInterface;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SearchAlbumQueueRequest extends FormRequest implements HttpRequestInterface
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'statuses' => 'required',
        ];
    }

    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function authorize(): bool
    {
        return Auth::user()->hasRole(Role::ROLE_ADMINISTRATOR_ID);
    }

    function toDto(): AlbumQueueSearchDto
    {
        return new AlbumQueueSearchDto([
            'albumQueueIds' => $this->input('album_queue_ids', []),
            'statuses' => $this->input('statuses', []),
            'resource_id' => $this->input('resource_id'),
        ]);
    }
}
