<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class TestEvent
{
    use SerializesModels;

    public function __construct() {
    }
}
