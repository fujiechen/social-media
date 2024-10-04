<?php

namespace App\Events;

use App\Models\Contact;
use Illuminate\Queue\SerializesModels;

class ContactSavedEvent
{
    use SerializesModels;

    public Contact $contact;

    public function __construct(Contact $contact) {
        $this->contact = $contact;
    }
}
