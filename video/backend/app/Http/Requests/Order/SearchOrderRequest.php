<?php

namespace App\Http\Requests\Order;

use App\Dtos\OrderSearchDto;
use App\Dtos\ProductSearchDto;
use App\Http\Requests\HttpRequestInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SearchOrderRequest extends FormRequest implements HttpRequestInterface
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

    function toDto(): OrderSearchDto
    {
        return new OrderSearchDto([
            'userId' => Auth::user()->id,
            'status' => $this->input('status'),
            'productId' => $this->input('product_id'),
        ]);
    }
}
