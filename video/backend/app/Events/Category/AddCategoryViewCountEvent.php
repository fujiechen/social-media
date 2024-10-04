<?php

namespace App\Events\Category;

use App\Models\Category;
use Illuminate\Queue\SerializesModels;

class AddCategoryViewCountEvent
{
    use SerializesModels;

    public Category $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }
}
