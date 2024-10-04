<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $isActive = $request->get('is_active', true);

        $returnArr = [
            'id' => $this->id,
            'name' => $this->name,
        ];

        if ($isActive) {
            $returnArr['products'] = $this->whenLoaded('activeProducts', ProductResource::collection($this->activeProducts));
        } else {
            $returnArr['products'] = $this->whenLoaded('products', ProductResource::collection($this->products));
        }

        return $returnArr;
    }
}
