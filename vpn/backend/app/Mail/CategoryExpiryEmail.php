<?php

namespace App\Mail;

use App\Models\CategoryUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CategoryExpiryEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private CategoryUser $categoryUser;
    private int $expiryInDays;

    public function __construct(CategoryUser $categoryUser, int $expiryInDays)
    {
        $this->categoryUser = $categoryUser;
        $this->expiryInDays = $expiryInDays;
    }

    public function build() {
        $categoryName = $this->categoryUser->category->name;
        $subject = '您的' . config('app.name') . '[' . $categoryName . ']' . '还有' . $this->expiryInDays . '天过期';
        if ($this->expiryInDays === 0) {
            $subject = '您的' . config('app.name') . '[' . $categoryName . ']' . '已经过期';
        }

        return $this->markdown('emails.category.expiry')
            ->subject($subject)
            ->with([
                'user' => $this->categoryUser->user->nickname,
                'categoryName' => $categoryName,
                'expiryInDays' => $this->expiryInDays,
                'categoryUrl' => config('app.frontend_url') . '/category/' . $this->categoryUser->category_id
            ]);
    }

}
