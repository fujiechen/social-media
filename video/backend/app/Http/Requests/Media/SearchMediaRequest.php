<?php

namespace App\Http\Requests\Media;

use App\Dtos\MediaSearchDto;
use App\Http\Requests\HttpRequestInterface;
use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SearchMediaRequest extends FormRequest implements HttpRequestInterface
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules(): array
    {
        return [

        ];
    }

    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function authorize(): bool
    {
        return true;
    }

    function toDto(): MediaSearchDto
    {
        $user = null;
        if (auth('api')->check()) {
            $user = auth('api')->user();
        }

        $types = $this->input('types', []);
        $mediaableTypes = [];
        foreach ($types as $type) {
            $mediaableTypes[] =  Media::toMediaableType($type);
        }
        return new MediaSearchDto([
            'userId' => $user?->id,
            'mediaUserId' => $this->input('media_user_id'),
            'mediaSearchText' => $this->input('media_search_text'),
            'actorId' => $this->input('actor_id'),
            'actorName' => $this->input('actor_name'),
            'tagIds' => $this->input('tag_ids', []),
            'tagNames' => $this->input('tag_names', []),
            'categoryId' => $this->input('category_id'),
            'categoryName' => $this->input('category_name'),
            'nickName' => $this->input('nickname'),
            'mediaableTypes' => $mediaableTypes,
            'orderBys' => $this->input('order_bys')
        ]);
    }
}
