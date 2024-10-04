<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\IndexProductRequest;
use App\Http\Resources\ProductCategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\ProductCategory;
use App\Services\ProductService;
use App\Services\TranslationService;
use Illuminate\Http\Request;

/**
 * Class ProductController
 *
 * @package App\Http\Controllers\Api
 */
class ProductController extends BaseController
{
    const ITEM_PER_PAGE = 15;

    private ProductService $productService;
    private TranslationService $translationService;

    public function __construct(ProductService $productService, TranslationService $translationService)
    {
        $this->productService = $productService;
        $this->translationService = $translationService;
    }

    public function show(Request $request, $id)
    {
        $request->merge(['id' => $request->route('id')]);
        $request->validate(['id' => 'exists:products,id']);
        $product = $this->productService->getProductsQuery($id)->limit(1)->first();
        $product = $this->translationService->translateModel($product, $this->getLanguage($request));
        return new ProductResource($product);
    }

    public function index(IndexProductRequest $request)
    {
        $limit = $request->get('limit', self::ITEM_PER_PAGE);
        $currencyId = $request->get('currency_id', null);
        $isRecommend = $request->boolean('is_recommend', true);
        $isActive = $request->boolean('is_active', true);

        $orderBy = $request->get('order_by', 'id');
        $sort = $request->get('sort', 'desc');

        $query = $this->productService->getProductsQuery(
            null,
            $currencyId,
            $isRecommend,
            $orderBy,
            $sort,
            $isActive
        );
        $products = $query->paginate($limit);

        $decoratedProducts = [];
        foreach ($products as $product) {
            $decoratedProducts[] = $this->translationService->translateModel($product, $this->getLanguage($request));
        }

        return ProductResource::collection($decoratedProducts);
    }

    public function categories(Request $request)
    {
        $isActive = $request->get('is_active', true);
        $language = $this->getLanguage($request);

        $categories = ProductCategory::all();
        foreach ($categories as $key => $category) {
            if ($isActive) {
                $activeProducts = [];
                foreach ($category->activeProducts as $product) {
                    $activeProducts[] = $this->translationService->translateModel($product, $language);
                }
                $categories[$key] = $this->translationService->translateModel($category, $language);
                $categories[$key]->activeProducts = $activeProducts;
            } else {
                $products = [];
                foreach ($category->products as $product) {
                    $products[] = $this->translationService->translateModel($product, $language);
                }
                $categories[$key] = $this->translationService->translateModel($category, $language);
                $categories[$key]->products = $products;
            }
        }

        return ProductCategoryResource::collection($categories);
    }
}
