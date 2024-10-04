<?php

namespace App\Http\Controllers;

use App\Exceptions\IllegalArgumentException;
use App\Http\Requests\Product\SearchMediaProductRequest;
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
    private ProductService $productService;
    private ProductTransformer $productTransformer;

    public function __construct(Fractal $fractal, ProductService $productService, ProductTransformer $productTransformer) {
        $this->fractal = $fractal;
        $this->productService = $productService;
        $this->productTransformer = $productTransformer;
    }

    public function index(SearchProductRequest $request): JsonResponse {
        $products = $this->productService->fetchAllProductsQuery($request->toDto())
            ->orderBy('products.id', 'desc')
            ->paginate($request->integer('per_page', 10));
        $resource = new Collection($products->items(), $this->productTransformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($products));
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    /**
     * @throws IllegalArgumentException
     */
    public function show(int $productId): JsonResponse {
        $product = Product::find($productId);
        if (!$product->is_active) {
            throw new IllegalArgumentException('is_active', 'Product is not active');
        }
        $resource = new Item($product, $this->productTransformer);
        return response()->json($this->fractal->createData($resource)->toArray());
    }

    public function mediaProducts(SearchMediaProductRequest $request): JsonResponse {
        $products = $this->productService->fetchMediaProductsQuery($request->toDto())
            ->orderBy('unit_cents')
            ->paginate($request->integer('per_page', 10));
        $resource = new Collection($products->items(), $this->productTransformer);
        $resource->setPaginator(new IlluminatePaginatorAdapter($products));
        return response()->json($this->fractal->createData($resource)->toArray());
    }
}
