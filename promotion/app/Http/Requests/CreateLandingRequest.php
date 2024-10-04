<?php

namespace App\Http\Requests;

use App\Dtos\LandingDto;
use Illuminate\Foundation\Http\FormRequest;

class CreateLandingRequest extends FormRequest implements HttpRequestInterface
{

    public function rules(): array
    {
        return [
            'url' => 'required',
            'signature' => 'required',
            'landing_template_id' => 'required|exists:landing_templates,id',
            'account_id' => 'required|exists:accounts,id',
            'post_id' => 'exists:posts,id',
            'redirect' => 'boolean'
        ];
    }

    function toDto(): LandingDto
    {
        return new LandingDto([
            'ip' => $this->ip(),
            'url' => $this->input('url'),
            'signature' => $this->input('signature'),
            'landingTemplateId' => $this->input('landing_template_id'),
            'postId' => $this->input('post_id'),
            'accountId' => $this->input('account_id'),
            'redirect' => $this->input('redirect', false),
        ]);
    }
}
