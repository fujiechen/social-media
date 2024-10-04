<?php

namespace App\Http\Requests\PlaylistQueue;

use App\Dtos\PlaylistQueueSearchDto;
use App\Http\Requests\HttpRequestInterface;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SearchPlaylistQueueRequest extends FormRequest implements HttpRequestInterface
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

    function toDto(): PlaylistQueueSearchDto
    {
        return new PlaylistQueueSearchDto([
            'playlistQueueIds' => $this->input('playlist_queue_ids', []),
            'statuses' => $this->input('statuses', []),
            'resource_id' => $this->input('resource_id'),
        ]);
    }
}
