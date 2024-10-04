<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\SearchProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use App\Transformers\ProductTransformer;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager as Fractal;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class ProductController extends Controller
{
    private Fractal $fractal;
    private ProductTransformer $productTransformer;

    public function __construct(Fractal $fractal, ProductTransformer $productTransformer) {
        $this->fractal = $fractal;
        $this->productTransformer = $productTransformer;
    }

    public function show(int $productId): JsonResponse {
        $product = Product::find($productId);
        $resource = new Item($product, $this->productTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }
}
