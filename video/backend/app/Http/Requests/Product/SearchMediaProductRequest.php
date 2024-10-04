<?php

namespace App\Http\Requests\Product;

use App\Dtos\MediaProductSearchDto;
use App\Http\Requests\HttpRequestInterface;
use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchMediaProductRequest extends FormRequest implements HttpRequestInterface
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules(): array
    {
        $this->merge(['media_id' => $this->route('mediaId')]);
        return [
            'media_id' => [
                'required',
                Rule::exists('medias', 'id')->whereNull('deleted_at')
            ],
        ];
    }

    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function authorize(): bool
    {
        return true;
    }

    function toDto(): MediaProductSearchDto
    {
        return new MediaProductSearchDto([
            'mediaId' => $this->input('media_id'),
        ]);
    }
}
