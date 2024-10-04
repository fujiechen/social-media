<?php

namespace App\Http\Requests\Product;

use App\Dtos\ProductSearchDto;
use App\Http\Requests\HttpRequestInterface;
use App\Models\Media;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SearchProductRequest extends FormRequest implements HttpRequestInterface
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'type' => 'nullable|in:' . Product::TYPE_SUBSCRIPTION
                . ',' . Product::TYPE_MEMBERSHIP
                . ',' . Product::TYPE_MEDIA
                . ',' . Product::TYPE_GENERAL,
            'currency_name' => 'nullable|in:CNY,COIN',
            'product_user_type' => 'nullable|in:all,self,user',
            'media_id' => [
                'nullable',
                Rule::exists('medias', 'id')->where('status',Media::STATUS_ACTIVE)->whereNull('deleted_at')
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

    function toDto(): ProductSearchDto
    {
        return new ProductSearchDto([
            'userId' => $this->input('user_id'),
            'type' => $this->input('type'),
            'productUserType' => $this->input('product_user_type'),
            'currencyName' => $this->input('currency_name'),
            'mediaId' => $this->input('media_id'),
        ]);
    }
}
