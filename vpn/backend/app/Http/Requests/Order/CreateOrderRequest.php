<?php

namespace App\Http\Requests\Order;

use App\Dtos\OrderDto;
use App\Dtos\OrderProductDto;
use App\Http\Requests\HttpRequestInterface;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateOrderRequest extends FormRequest implements HttpRequestInterface
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string>
     */
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|gt:0'
        ];
    }

    /**
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function authorize(): bool
    {
        return true;
    }

    function toDto(): OrderDto
    {
        $orderProductDtos[] = new OrderProductDto([
            'productId' => $this->input('product_id'),
            'qty' => 1,
        ]);

        return new OrderDto([
            'userId' => Auth::user()->id,
            'status' => Order::STATUS_PENDING,
            'orderProductDtos' => $orderProductDtos
        ]);
    }
}
