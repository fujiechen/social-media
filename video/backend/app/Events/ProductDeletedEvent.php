<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Queue\SerializesModels;

class ProductDeletedEvent
{
    use SerializesModels;

    public Product $product;

    public function __construct(Product $product) {
        $this->product = $product;
    }
}
