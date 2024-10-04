<?php

namespace App\Http\Requests\VideoQueue;

use App\Dtos\VideoQueueSearchDto;
use App\Http\Requests\HttpRequestInterface;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SearchVideoQueueRequest extends FormRequest implements HttpRequestInterface
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

    function toDto(): VideoQueueSearchDto
    {
        return new VideoQueueSearchDto([
            'videoQueueIds' => $this->input('video_queue_ids', []),
            'statuses' => $this->input('statuses', []),
            'resource_id' => $this->input('resource_id'),
        ]);
    }
}
