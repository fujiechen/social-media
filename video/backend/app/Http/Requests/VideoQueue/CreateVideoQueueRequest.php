<?php

namespace App\Http\Requests\VideoQueue;

use App\Dtos\VideoQueueDto;
use App\Http\Requests\HttpRequestInterface;
use App\Models\Role;
use App\Models\VideoQueue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateVideoQueueRequest extends FormRequest implements HttpRequestInterface
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

    function toDto(): VideoQueueDto
    {
        return new VideoQueueDto([
            'resourceId' => $this->input('resource_id', []),
            'resourceVideoUrl' => $this->input('url', []),
            'status' => VideoQueue::STATUS_PENDING,
            'playlistQueueId' => $this->input('playlist_queue_id'),
            'prefillJson' => $this->input('prefill_json'),
            'mediaQueueId' => $this->input('media_queue_id'),
        ]);
    }
}
