<?php

namespace App\Events;

use App\Models\CategoryUser;
use Illuminate\Queue\SerializesModels;

class CategoryUserCreatedEvent
{
    use SerializesModels;

    public CategoryUser $categoryUser;

    public function __construct(CategoryUser $categoryUser) {
        $this->categoryUser = $categoryUser;
    }
}
