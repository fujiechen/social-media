<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $value
 */
class Setting extends Model
{
    public $timestamps = false;

    public const URL_ABOUT_US = 'URL_ABOUT_US';
    public const URL_BANNER_IMAGE = 'URL_BANNER_IMAGE';
    public const TEXT_BANNER_TITLE = 'TEXT_BANNER_TITLE';
    public const TEXT_BANNER_SLOGAN = 'TEXT_BANNER_SLOGAN';

    public const URL_ACCOUNT_IMAGE = 'URL_ACCOUNT_IMAGE';

    public const TRANSLATABLE_HTML_HELP_APP = 'TRANSLATABLE_HTML_HELP_APP';
    public const TRANSLATABLE_HTML_TERMS_APP = 'TRANSLATABLE_HTML_TERMS_APP';

    public const JSON_BANK_ACCOUNT = 'JSON_BANK_ACCOUNT';
    public const TRANSLATABLE_HTML_HELP_DEPOSIT = 'TRANSLATABLE_HTML_HELP_DEPOSIT';
    public const TRANSLATABLE_HTML_HELP_EXCHANGE = 'TRANSLATABLE_HTML_HELP_EXCHANGE';
    public const TRANSLATABLE_HTML_HELP_TRANSFER = 'TRANSLATABLE_HTML_HELP_TRANSFER';
    public const TRANSLATABLE_HTML_HELP_PURCHASE = 'TRANSLATABLE_HTML_HELP_PURCHASE';
    public const TRANSLATABLE_HTML_HELP_WITHDRAW = 'TRANSLATABLE_HTML_HELP_WITHDRAW';

    public const URL_CUSTOMER_SERVICE = 'URL_CUSTOMER_SERVICE';

    public const DEPOSIT_AMOUNT_OPTIONS = 'DEPOSIT_AMOUNT_OPTIONS';

}
