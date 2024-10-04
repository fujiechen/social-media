<?php

namespace App\Transformers;

use App\Models\Category;
use App\Models\Product;
use League\Fractal\TransformerAbstract;

class CategoryTransformer extends TransformerAbstract
{
    private FileTransformer $fileTransformer;
    private ProductTransformer $productTransformer;

    public function __construct(FileTransformer $fileTransformer, ProductTransformer $productTransformer) {
        $this->fileTransformer = $fileTransformer;
        $this->productTransformer = $productTransformer;
    }

    public function transform(Category $category): array
    {
        $products = [];
        foreach ($category->products as $product) {
            $products[] = $this->productTransformer->transform($product);
        }


        return [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'tags' => $category->tags,
            'highlights' => $category->highlights,
            'thumbnail_file' => $category->thumbnailFile ? $this->fileTransformer->transform($category->thumbnailFile) : null,
            'products' => $products,
        ];
    }
}
