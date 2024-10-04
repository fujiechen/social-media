<?php

namespace App\Events;

use App\Models\CategoryUser;
use Illuminate\Queue\SerializesModels;

class CategoryUserUpdatedEvent
{
    use SerializesModels;

    public CategoryUser $categoryUser;

    public function __construct(CategoryUser $categoryUser) {
        $this->categoryUser = $categoryUser;
    }
}
