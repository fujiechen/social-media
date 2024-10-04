<?php
namespace App\Http\Requests\User;

use App\Dtos\UserShareDto;
use App\Http\Requests\HttpRequestInterface;
use App\Models\UserShare;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateUserShareRequest extends FormRequest implements HttpRequestInterface
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'type' => 'nullable',
            'shareable_id' => 'integer|nullable',
            'url' => 'required|string'
        ];
    }

    function toDto(): UserShareDto
    {
        return new UserShareDto([
            'userId' => Auth::user()->id,
            'shareableType' => $this->input('type') ? UserShare::toShareableType($this->input('type')) : null,
            'shareableId' => $this->input('shareable_id'),
            'url' => $this->input('url'),
        ]);
    }
}
