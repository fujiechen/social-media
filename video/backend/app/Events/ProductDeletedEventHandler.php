<?php

namespace App\Events;

use App\Services\ProductService;

class ProductDeletedEventHandler
{
    private ProductService $productService;

    public function __construct(ProductService $productService) {
        $this->productService = $productService;
    }

    public function handle(ProductDeletedEvent $event): void
    {
        $this->productService->postDeleted($event->product);
    }
}
