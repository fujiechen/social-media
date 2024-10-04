<?php

namespace App\Console\Commands;

use App\Models\Language;
use App\Models\Product;
use App\Models\Role;
use App\Services\CurrencyRateService;
use App\Services\TranslationService;
use App\Services\UserService;
use Carbon\Carbon;
use Faker\Generator as Faker;
use App\Models\Currency;
use App\Models\Setting;
use App\Models\ProductCategory;
use App\Services\ProductService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InitBank extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bank:init
    {--c|currency : boot currency rates}
    {--t|translate : boot translations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize Bank App';

    private Faker $faker;
    private ProductService $productService;
    private UserService $userService;
    private CurrencyRateService $currencyRateService;
    private TranslationService $translationService;

    /**
     * Create a new command instance.
     *
     * @param Faker $faker
     * @param ProductService $productService
     * @param UserService $userService
     * @param CurrencyRateService $currencyRateService
     * @param TranslationService $translationService
     */
    public function __construct(Faker $faker, ProductService $productService, UserService $userService, CurrencyRateService $currencyRateService, TranslationService $translationService)
    {
        parent::__construct();
        $this->faker = $faker;
        $this->productService = $productService;
        $this->userService = $userService;
        $this->currencyRateService = $currencyRateService;
        $this->translationService = $translationService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $doCurrency = $this->option('currency');
        $doTranslate = $this->option('translate');

        $this->info('Start Initializing');
        $this->bootSettings();

        if (is_bool($doCurrency) && $doCurrency) {
            $this->bootCurrencyRates();
        }

        $this->bootCategories();
        //$this->bootHistoricalProducts();
        //$this->bootUSDTProducts();
        $this->bootGeneralProducts();
        $this->bootAdminUser();

        if (is_bool($doTranslate) && $doTranslate) {
            $this->bootTranslations();
        }

        $this->info('Done!');

        return 0;
    }

    private function bootCurrencyRates()
    {
        $this->info('Initializing Currency Rates...');

        $toCurrencyIds = [];

        foreach (Currency::all() as $toCurrency) {
            $toCurrencyIds[] = $toCurrency->id;
        }

        foreach (Currency::all() as $fromCurrency) {
            $this->currencyRateService->createExchangeRates($fromCurrency->id, $toCurrencyIds);
        }
    }

    private function bootTranslations()
    {
        $this->info('Initializing Translations...');
        $bar = $this->output->createProgressBar(count(Language::LANGUAGES));
        $bar->start();
        foreach (Language::LANGUAGES as $language) {

            foreach (Product::all() as $product) {
                $this->translationService->translateModel($product, $language);
            }

            foreach (ProductCategory::all() as $category) {
                $this->translationService->translateModel($category, $language);
            }

            foreach (Setting::all() as $setting) {
                $this->translationService->translateSetting($setting, $language);
            }
            $bar->advance();
        }
        $bar->finish();
        $this->line('');
    }

    private function bootCategories()
    {
        $this->info('Initializing Categories...');
        foreach ([ProductCategory::EQUITY_FUNDS, ProductCategory::MULTI_ASSET, ProductCategory::FIXED_INCOME] as $cat) {
            ProductCategory::create([
                'name' => $cat,
            ]);
        }
    }

    private function bootHistoricalProducts()
    {
        $this->info('Initializing Historical USDT Products...');

        $currencyUSDT = Currency::where('name', Currency::USDT)->first();

        $categoryEF = ProductCategory::where('name', ProductCategory::EQUITY_FUNDS)->first();
        $categoryMA = ProductCategory::where('name', ProductCategory::MULTI_ASSET)->first();
        $categoryFI = ProductCategory::where('name', ProductCategory::FIXED_INCOME)->first();

        $bar = $this->output->createProgressBar(4);
        $bar->start();

        // equity funds $10k - deactivated 4 months ago
        $startAmount = 10000;
        $freezeDays = 14;
        $stock = 999;
        $title = 'AGVGX';
        $name = 'American Funds Global Insight Fund - Growth Funds';
        $description = 'A prudent approach to global investing. Global strategy pursues prudent growth of capital and conservation of principal by investing in companies predominately based in developed markets. The strategy seeks to provide a smoother return profile over a full market cycle — less volatility and lower downside capture than the market.';
        $fundAssets = '$82,397 (millions)';
        $fundFactUrl = 'https://www.capitalgroup.com/advisor/pdf/shareholder/MFF2SSX-122-636241.pdf';
        $prospectusUrl = 'https://www.capitalgroup.com/advisor/pdf/shareholder/mfgeprx-122-628851.pdf';
        $isRecommend = false;
        $estimateRate = 438;
        $deactivatedAt = Carbon::yesterday();
        $this->productService->create($categoryEF->id, $currencyUSDT->id, $title,
            $name, $description, $startAmount,
            $stock, $freezeDays, $fundAssets, $fundFactUrl, $prospectusUrl,
            $isRecommend, $estimateRate, $deactivatedAt);
        $bar->advance();

        // equity funds $50k - deactivated 2 months ago
        $startAmount = 50000;
        $freezeDays = 14;
        $stock = 999;
        $title = 'NFFFX';
        $name = 'New World Fund - Growth Funds';
        $description = 'A flexible approach to developing markets. Seeks broad exposure to emerging markets opportunities, principally by investing in emerging markets companies as well as multinational companies with material emerging markets exposure.';
        $fundAssets = '$54,288 (millions)';
        $fundFactUrl = 'https://www.capitalgroup.com/advisor/pdf/shareholder/mff2ssx-036_nwff2ffs.pdf';
        $prospectusUrl = 'https://www.capitalgroup.com/advisor/pdf/shareholder/mfgeprx-036_nwfp.pdf';
        $isRecommend = false;
        $estimateRate = 604;
        $deactivatedAt = Carbon::yesterday();
        $this->productService->create($categoryEF->id, $currencyUSDT->id, $title,
            $name, $description, $startAmount,
            $stock, $freezeDays, $fundAssets, $fundFactUrl, $prospectusUrl,
            $isRecommend, $estimateRate, $deactivatedAt);
        $bar->advance();

        // multi asset  $100k - deactivated 2 months ago
        $startAmount = 100000;
        $freezeDays = 14;
        $stock = 999;
        $title = 'CAIFX';
        $name = 'Capital Income Builder';
        $description = 'A globally diversified multi-asset approach to building income. Focuses on prudently generating a high and growing stream of income, which has historically led to equity-like returns and relatively less volatility than global markets. This flexible equity-income strategy uses a mix of stocks and bonds in pursuit of its income objective.';
        $fundAssets = '$109,820 (millions)';
        $fundFactUrl = 'https://www.capitalgroup.com/advisor/pdf/shareholder/mff2ssx-012_cibf2ffs.pdf';
        $prospectusUrl = 'https://www.capitalgroup.com/advisor/pdf/shareholder/mfgeprx-012_cibp.pdf';
        $isRecommend = false;
        $estimateRate = 468;
        $deactivatedAt = Carbon::yesterday();
        $this->productService->create($categoryMA->id, $currencyUSDT->id, $title,
            $name, $description, $startAmount,
            $stock, $freezeDays, $fundAssets, $fundFactUrl, $prospectusUrl,
            $isRecommend, $estimateRate, $deactivatedAt);
        $bar->advance();

        // fix income $30w - deactivated yesterday
        $startAmount = 300000;
        $freezeDays = 14;
        $stock = 999;
        $title = 'GBLFX';
        $name = 'American Funds Global Balanced Fund - Balanced Funds';
        $description = 'A balanced approach to global growth-and-income investing. This globally diversified balanced strategy has the ability to invest between 45% and 75% in equities, with an emphasis on well-established companies. The diversified and predominantly high-quality bond portfolio has provided income and reduced volatility.';
        $fundAssets = '$25,688 (millions)';
        $fundFactUrl = 'https://www.capitalgroup.com/advisor/pdf/shareholder/mff2ssx-037_gbalf2ffs.pdf';
        $prospectusUrl = 'https://www.capitalgroup.com/advisor/pdf/shareholder/mfgeprx-037_gbalp.pdf';
        $isRecommend = false;
        $estimateRate = 301;
        $deactivatedAt = Carbon::yesterday();
        $this->productService->create($categoryFI->id, $currencyUSDT->id, $title,
            $name, $description, $startAmount,
            $stock, $freezeDays, $fundAssets, $fundFactUrl, $prospectusUrl,
            $isRecommend, $estimateRate, $deactivatedAt);
        $bar->advance();

        $bar->finish();
        $this->line('');
    }

    private function bootUSDTProducts()
    {
        $this->info('Initializing USDT Products...');

        $currencyUSDT = Currency::where('name', Currency::USDT)->first();

        $categoryEF = ProductCategory::where('name', ProductCategory::EQUITY_FUNDS)->first();
        $categoryMA = ProductCategory::where('name', ProductCategory::MULTI_ASSET)->first();
        $categoryFI = ProductCategory::where('name', ProductCategory::FIXED_INCOME)->first();

        $bar = $this->output->createProgressBar(6);
        $bar->start();

        // equity funds $50w
        $startAmount = 500000;
        $freezeDays = 14;
        $stock = 999;
        $title = 'AGVGX';
        $name = 'American Funds Global Insight Fund - Growth Funds';
        $description = 'A prudent approach to global investing. Global strategy pursues prudent growth of capital and conservation of principal by investing in companies predominately based in developed markets. The strategy seeks to provide a smoother return profile over a full market cycle — less volatility and lower downside capture than the market.';
        $fundAssets = '$82,397 (millions)';
        $fundFactUrl = 'https://www.capitalgroup.com/advisor/pdf/shareholder/MFF2SSX-122-636241.pdf';
        $prospectusUrl = 'https://www.capitalgroup.com/advisor/pdf/shareholder/mfgeprx-122-628851.pdf';
        $isRecommend = false;
        $estimateRate = 438;
        $deactivatedAt = null;
        $this->productService->create($categoryEF->id, $currencyUSDT->id, $title,
            $name, $description, $startAmount,
            $stock, $freezeDays, $fundAssets, $fundFactUrl, $prospectusUrl,
            $isRecommend, $estimateRate, $deactivatedAt);
        $bar->advance();

        // equity funds $50w
        $startAmount = 500000;
        $freezeDays = 14;
        $stock = 999;
        $title = 'NFFFX';
        $name = 'New World Fund - Growth Funds';
        $description = 'A flexible approach to developing markets. Seeks broad exposure to emerging markets opportunities, principally by investing in emerging markets companies as well as multinational companies with material emerging markets exposure.';
        $fundAssets = '$54,288 (millions)';
        $fundFactUrl = 'https://www.capitalgroup.com/advisor/pdf/shareholder/mff2ssx-036_nwff2ffs.pdf';
        $prospectusUrl = 'https://www.capitalgroup.com/advisor/pdf/shareholder/mfgeprx-036_nwfp.pdf';
        $isRecommend = false;
        $estimateRate = 604;
        $deactivatedAt = null;
        $this->productService->create($categoryEF->id, $currencyUSDT->id, $title,
            $name, $description, $startAmount,
            $stock, $freezeDays, $fundAssets, $fundFactUrl, $prospectusUrl,
            $isRecommend, $estimateRate, $deactivatedAt);
        $bar->advance();

        // multi asset  $50w
        $startAmount = 500000;
        $freezeDays = 14;
        $stock = 999;
        $title = 'CAIFX';
        $name = 'Capital Income Builder';
        $description = 'A globally diversified multi-asset approach to building income. Focuses on prudently generating a high and growing stream of income, which has historically led to equity-like returns and relatively less volatility than global markets. This flexible equity-income strategy uses a mix of stocks and bonds in pursuit of its income objective.';
        $fundAssets = '$109,820 (millions)';
        $fundFactUrl = 'https://www.capitalgroup.com/advisor/pdf/shareholder/mff2ssx-012_cibf2ffs.pdf';
        $prospectusUrl = 'https://www.capitalgroup.com/advisor/pdf/shareholder/mfgeprx-012_cibp.pdf';
        $isRecommend = false;
        $estimateRate = 468;
        $deactivatedAt = null;
        $this->productService->create($categoryMA->id, $currencyUSDT->id, $title,
            $name, $description, $startAmount,
            $stock, $freezeDays, $fundAssets, $fundFactUrl, $prospectusUrl,
            $isRecommend, $estimateRate, $deactivatedAt);
        $bar->advance();

        // multi asset  $50w
        $startAmount = 500000;
        $freezeDays = 14;
        $stock = 999;
        $title = 'GBLFX';
        $name = 'American Funds Global Balanced Fund - Balanced Funds';
        $description = 'A balanced approach to global growth-and-income investing. This globally diversified balanced strategy has the ability to invest between 45% and 75% in equities, with an emphasis on well-established companies. The diversified and predominantly high-quality bond portfolio has provided income and reduced volatility.';
        $fundAssets = '$25,688 (millions)';
        $fundFactUrl = 'https://www.capitalgroup.com/advisor/pdf/shareholder/mff2ssx-037_gbalf2ffs.pdf';
        $prospectusUrl = 'https://www.capitalgroup.com/advisor/pdf/shareholder/mfgeprx-037_gbalp.pdf';
        $isRecommend = false;
        $estimateRate = 301;
        $deactivatedAt = null;
        $this->productService->create($categoryMA->id, $currencyUSDT->id, $title,
            $name, $description, $startAmount,
            $stock, $freezeDays, $fundAssets, $fundFactUrl, $prospectusUrl,
            $isRecommend, $estimateRate, $deactivatedAt);
        $bar->advance();

        // fixed income $50w
        $startAmount = 500000;
        $freezeDays = 14;
        $stock = 999;
        $title = 'BFWFX';
        $name = 'Capital World Bond Fund - Bound Funds';
        $description = 'A broadly diversified approach to global bonds. Invests primarily in sovereign and corporate bonds in more than 60 developed and developing markets. It provides exposure to high-yielding bond markets and currencies outside the U.S., adding an extra layer of diversification from U.S. investment-grade bonds.';
        $fundAssets = '$14,695 (millions)';
        $fundFactUrl = 'https://www.capitalgroup.com/advisor/pdf/shareholder/mff2ssx-031_wbff2ffs.pdf';
        $prospectusUrl = 'https://www.capitalgroup.com/advisor/pdf/shareholder/mfgeprx-031_wbfp.pdf';
        $isRecommend = false;
        $estimateRate = 470;
        $deactivatedAt = null;
        $this->productService->create($categoryFI->id, $currencyUSDT->id, $title,
            $name, $description, $startAmount,
            $stock, $freezeDays, $fundAssets, $fundFactUrl, $prospectusUrl,
            $isRecommend, $estimateRate, $deactivatedAt);
        $bar->advance();

        // fixed income $50w
        $startAmount = 500000;
        $freezeDays = 14;
        $stock = 999;
        $title = 'MIAYX';
        $name = 'American Funds Multi-Sector Income Fund - Bound Funds';
        $description = 'An income-oriented strategy. MSI is a relative value credit fund that takes advantage of investment opportunities across high yield, investment grade, and emerging markets, as well as securitized debt.';
        $fundAssets = '$3,354 (millions)';
        $fundFactUrl = 'https://www.capitalgroup.com/advisor/pdf/shareholder/MFF2SSX-126-647791.pdf';
        $prospectusUrl = 'https://www.capitalgroup.com/advisor/pdf/shareholder/mfgeprx-126-622861.pdf';
        $isRecommend = false;
        $estimateRate = 490;
        $deactivatedAt = null;
        $this->productService->create($categoryFI->id, $currencyUSDT->id, $title,
            $name, $description, $startAmount,
            $stock, $freezeDays, $fundAssets, $fundFactUrl, $prospectusUrl,
            $isRecommend, $estimateRate, $deactivatedAt);
        $bar->advance();

        $bar->finish();
        $this->line('');
    }

    public function bootGeneralProducts()
    {
        $this->info('Initializing General Products...');
        $bar = $this->output->createProgressBar(3);
        $bar->start();
        foreach ([ProductCategory::EQUITY_FUNDS] as $cat) {
            foreach ([Currency::CNY, Currency::USD] as $symbol) {

                $currency = Currency::where('name', $symbol)->first();
                $category = ProductCategory::where('name', $cat)->first();

                // fixed income $1w
                $startAmount = 10000;
                $freezeDays = 7;
                $stock = 0;
                $title = $symbol . 'GI';
                $name = $symbol . ' Growth and Income ' . $cat;
                $description = 'Global growth with a dividend focus. Invests primarily in seasoned companies, including those paying consistent dividends and with attractive growth prospects, which historically has led to lower volatility and relative downside protection.';
                $fundAssets = '$' . $this->faker->randomNumber(3) . ',' . $this->faker->randomNumber(3) . ' (millions)';
                $fundFactUrl = null;
                $prospectusUrl = null;
                $estimateRate = rand(301, 399);
                $deactivatedAt = null;
                $this->productService->create($category->id, $currency->id, $title,
                    $name, $description, $startAmount,
                    $stock, $freezeDays, $fundAssets, $fundFactUrl, $prospectusUrl,
                    true, $estimateRate, $deactivatedAt);
                $bar->advance();
            }
        }
        $bar->finish();
        $this->line('');
    }

    private function bootSettings()
    {
        $this->info('Initializing Settings...');

        DB::table('currencies')->insert([
            ['id' => 1, 'name' => Currency::CNY, 'symbol' => '¥', 'is_default' => true],
            ['id' => 2, 'name' => Currency::USD, 'symbol' => '$', 'is_default' => false],
        ]);

        DB::table('currencies')->insert([
            ['id' => 3, 'name' => Currency::COIN, 'symbol' => '¥', 'is_default' => false,
                'purchase_enabled' => false, 'deposit_enabled' => false, 'withdraw_enabled' => false,
                'exchange_enabled' => false, 'transfer_enabled' => false]
        ]);

        DB::table('settings')->insert([
            ['name' => Setting::URL_ABOUT_US, 'value' => 'https://www.capitalgroup.com/individual/about-us.html'],
            ['name' => Setting::URL_BANNER_IMAGE, 'value' => config('app.url') . '/image/banner.jpeg'],
            ['name' => Setting::TEXT_BANNER_TITLE, 'value' => 'CAPITAL GROUP'],
            ['name' => Setting::TEXT_BANNER_SLOGAN, 'value' => 'Helping people get from "Can I?" to "I can" since 1931'],
            ['name' => Setting::TRANSLATABLE_HTML_HELP_DEPOSIT, 'value' => ''],
            ['name' => Setting::TRANSLATABLE_HTML_HELP_EXCHANGE, 'value' => ''],
            ['name' => Setting::TRANSLATABLE_HTML_HELP_TRANSFER, 'value' => ''],
            ['name' => Setting::TRANSLATABLE_HTML_HELP_PURCHASE, 'value' => ''],
            ['name' => Setting::TRANSLATABLE_HTML_HELP_WITHDRAW, 'value' => ''],
            ['name' => Setting::TRANSLATABLE_HTML_TERMS_APP, 'value' => 'Help HTML'],
            ['name' => Setting::URL_ACCOUNT_IMAGE, 'value' => config('app.url') . '/image/account_card.jpeg'],
            ['name' => Setting::JSON_BANK_ACCOUNT, 'value' => ''],
            ['name' => Setting::TRANSLATABLE_HTML_HELP_APP, 'value' => ''],
        ]);

        DB::statement("INSERT INTO `translations` (`id`, `hash`, `from_language`, `from_text`, `to_language`, `to_text`)
VALUES
	(8, 'b0f82ab01f0d26a830f2f8616246428e', 'en', '<h4>Purchase</h4>\n          <p class=\"font-14 mb-0\"><strong>Purchase the investment product with any amount more than the minimum requirement, the purchase will take the fund directly from the related account and freeze for a given day. You can monitor the purchased investment returns from \"Invest\". </strong></p>\n<p class=\"font-14 mb-0\">Please follow the steps below to make the purchase:</p>\n<ol class=\"font-12 mb-0 ps-3\">\n<li>Read the product description</li>\n<li>Make sure you have enough account balance, the account balance will be shown under the amount field</li>\n<li>Fill in the amount of fund you would like to purchase for the product, <strong>make sure it is more than the minimum requirement</strong></li>\n<li>Read and accept the Terms and Conditions</li>\n<li>Click \"confirm purchase\" button to finish the purchase</li>\n<li>If there are any errors during the process, you can follow the error message or contact our customer service in \"Account\"</li>\n</ol><br/>\n          <h4>Deposit</h4>\n          <p class=\"font-14 mb-0\"><strong>Ensure the network you choose to deposit matches the withdrawal network or assets may be lost. Please follow the steps below to make the deposit</strong>:</p>\n<ol class=\"font-12 mb-0 ps-3\">\n<li>Choose the deposit network and copy the USDT deposit address</li>\n<li>Make sure to send only USDT to this deposit address</li>\n<li>Enter the total amount you will deposit</li>\n<li>Read and accept the Terms and Conditions</li>\n<li>Click the \"deposit funds\" button to submit a deposit request</li>\n<li><strong>You should transfer the fund when the deposit request is created successfully, our customer service will confirm and set the received amount to your USDT account</strong></li>\n<li>If there are any errors during the process, you can follow the error message or contact our customer service in \"Account\"</li>\n</ol><br/>\n          <h4>Withdraw</h4>\n          <p class=\"font-14 mb-0\"><strong>Please note that wired withdrawal services will be charged a 5% processing fee and minimum equivalent US$1,000, and cash withdrawal services will be charged a 20% processing fee and minimum equivalent US$100,000</strong></p>\n<p class=\"font-14 mb-0\">Please follow the steps below to request the withdrawal:</p>\n<ol class=\"font-12 mb-0 ps-3\">\n<li>Choose the withdrawal account</li>\n<li>Choose the withdrawal types, withdrawal addresses are required for \"Cash\", and your bank account is required for \"Wired\"</li>\n<li>You can redirect to the address or bank account management page by clicking the quick link</li>\n<li>Enter the total amount you would like to withdraw, and make sure you have enough account balance</li>\n<li>Read and accept the Terms and Conditions</li>\n<li>Click the \"withdraw funds\" button to submit the withdrawal request</li>\n<li>The withdrawal will be performed as soon as customer service receives the request, we will contact you directly to confirm the final delivery</li>\n<li>If there are any errors during the process, you can follow the error message or contact our customer service in \"Account\"</li>\n</ol><br/>\n          <h4>Exchange</h4>\n          <p class=\"font-14 mb-0\"><strong>Please note that all exchange rates are estimated and the final rate may change during the process</strong></p>\n<p class=\"font-14 mb-0\">Please follow the steps below to exchange between accounts:</p>\n<ol class=\"font-12 mb-0 ps-3\">\n<li>Choose \"From\" and \"To\" Accounts and review the account balances</li>\n<li>Enter the exchange amount you would like to exchange from the From account, make sure you have enough account balance in your \"From\" account</li>\n<li>We will prompt and display the current estimated exchange rate and the estimated final amount (No guarantee due to fast exchange rate update)</li>\n<li>Read and accept the Terms and Conditions</li>\n<li>Click the \"convert and add to account\" button to submit the exchange request</li>\n<li>The exchange action will be done immediately, and you should be able to check the status of the action and transaction in \"Asset\"</li>\n<li>If there are any errors during the process, you can follow the error message or contact our customer service in \"Account\"</li>\n</ol><br/>\n          <h4>Transfer</h4>\n          <p class=\"font-14 mb-0\"><strong>Please note that there is no extra fee applied to our transfer service</strong></p>\n<p class=\"font-14 mb-0\">Please follow the steps below to transfer funds to other platform users:</p>\n<ol class=\"font-12 mb-0 ps-3\">\n<li>Choose an Account and review your account balance</li>\n<li>Fill in the receiver\'s full name and email address which should exactly match what they have on the platform account profile</li>\n<li>Enter the transfer amount you would like to transfer, make sure you have enough account balance</li>\n<li>Read and accept the Terms and Conditions</li>\n<li>Click the \"transfer to friend\" button to submit the transfer request</li>\n<li>The transfer action will be done immediately, and you should be able to check the status of the action and transaction in \"Asset\"</li>\n<li>If there are any errors during the process, you can follow the error message or contact our customer service in \"Account\"</li>\n</ol>', 'zh', '<h4>购买</h4>\n          <p class=\"font-14 mb-0\"><strong>以超过最低要求的任何金额购买投资产品，购买时将直接从相关账户中提取资金，并冻结在某一天。你可以从 \"投资 \"中监测所购买的投资回报。 </strong></p>\n<p class=\"font-14 mb-0\">请按照以下步骤进行购买。</p>\n<ol class=\"font-12 mb-0 ps-3\">\n<li>阅读产品说明</li>\n<li>确保你有足够的账户余额，账户余额将显示在金额栏下</li>\n<li>填写你想购买该产品的资金数额，确保<strong>它超过最低要求</strong>。</li>\n<li>阅读并接受条款和条件</li>\n<li>点击 \"确认购买 \"按钮，完成购买。</li>\n<li>如果在此过程中出现任何错误，您可以根据错误信息或在 \"账户 \"中联系我们的客服人员。</li>\n</ol><br/>\n          <h4>存款</h4>\n          <p class=\"font-14 mb-0\"><strong>确保你选择的入金网络与出金网络一致，否则资产可能会丢失。请按照以下步骤进行存款</strong>。</p>\n<ol class=\"font-12 mb-0 ps-3\">\n<li>选择存款网络并复制USDT存款地址</li>\n<li>确保只发送USDT到这个存款地址</li>\n<li>输入你将存入的总金额</li>\n<li>阅读并接受条款和条件</li>\n<li>点击 \"存入资金 \"按钮，提交存款请求</li>\n<li><strong>当存款请求创建成功后，您应该转移资金，我们的客户服务将确认并设置收到的金额到您的USDT帐户</strong></li>\n<li>如果在此过程中出现任何错误，您可以关注错误信息或在 \"账户 \"中联系我们的客服人员</li>\n</ol><br/>\n          <h4>提款</h4>\n          <p class=\"font-14 mb-0\"><strong>请注意，电汇提款服务将被收取5%的手续费和最低等值1000美元，而现金提款服务将被收取20%的手续费和最低等值100,000美元。</strong></p>\n<p class=\"font-14 mb-0\">请按照以下步骤申请提款。</p>\n<ol class=\"font-12 mb-0 ps-3\">\n<li>选择取款账户</li>\n<li>选择提款类型，\"现金 \"需要提款地址，\"电汇 \"需要您的银行账户</li>\n<li>你可以通过点击快速链接重定向到地址或银行账户管理页面</li>\n<li>输入你想提取的总金额，并确保你有足够的账户余额</li>\n<li>阅读并接受条款和条件</li>\n<li>点击 \"取款 \"按钮，提交取款请求</li>\n<li>客服人员收到请求后，将立即进行提款，我们将直接与您联系，确认最终交付。</li>\n<li>如果在此过程中出现任何错误，您可以关注错误信息或在 \"账户 \"中联系我们的客服人员。</li>\n</ol><br/>\n          <h4>兑换</h4>\n          <p class=\"font-14 mb-0\"><strong>请注意，所有的汇率都是估计的，最终的汇率可能会在这个过程中发生变化。</strong></p>\n<p class=\"font-14 mb-0\">请按照以下步骤进行账户间的兑换。</p>\n<ol class=\"font-12 mb-0 ps-3\">\n<li>选择 \"转出 \"和 \"转入 \"账户并查看账户余额</li>\n<li>输入你想从 \"转出 \"账户兑换的金额，确保你的 \"转出 \"账户有足够的账户余额。</li>\n<li>我们将提示并显示当前的估计汇率和估计的最终金额（由于汇率更新快，不保证）。</li>\n<li>阅读并接受条款和条件</li>\n<li>点击 \"兑换并添加到账户 \"按钮，提交兑换请求。</li>\n<li>兑换行动将立即完成，你应该能够在 \"资产 \"中检查行动和交易的状态</li>\n<li>如果在这个过程中出现任何错误，你可以关注错误信息或在 \"账户 \"中联系我们的客服人员。</li>\n</ol><br/>\n          <h4>转账</h4>\n          <p class=\"font-14 mb-0\"><strong>请注意，我们的转账服务不收取任何额外费用。</strong></p>\n<p class=\"font-14 mb-0\">请按照以下步骤将资金转移给其他平台用户。</p>\n<ol class=\"font-12 mb-0 ps-3\">\n<li>选择一个账户并查看您的账户余额</li>\n<li>填写收款人的全名和电子邮件地址，应与他们在平台账户资料中的内容完全一致</li>\n<li>输入你想转移的金额，确保你有足够的账户余额</li>\n<li>阅读并接受条款和条件</li>\n<li>点击 \"转账给朋友 \"按钮，提交转账请求</li>\n<li>转账行动将立即完成，你应该能够在 \"资产 \"中检查行动和交易的状态</li>\n<li>如果在这个过程中出现任何错误，你可以关注错误信息或在 \"账户 \"中联系我们的客服人员</li>\n</ol>'),
	(9, '13ba344793663b05258a1f5e33a294c9', 'en', '<p class=\"font-12 mb-0\"><strong>Please note that all exchange rates are estimated and the final rate may change during the process</strong></p>\n<p class=\"font-12 mb-0\">Please follow the steps below to exchange between accounts:</p>\n<ol class=\"font-11 mb-0 ps-3\">\n<li>Choose \"From\" and \"To\" Accounts and review the account balances</li>\n<li>Enter the exchange amount you would like to exchange from the From account, make sure you have enough account balance in your \"From\" account</li>\n<li>We will prompt and display the current estimated exchange rate and the estimated final amount (No guarantee due to fast exchange rate update)</li>\n<li>Read and accept the Terms and Conditions</li>\n<li>Click the \"convert and add to account\" button to submit the exchange request</li>\n<li>The exchange action will be done immediately, and you should be able to check the status of the action and transaction in \"Asset\"</li>\n<li>If there are any errors during the process, you can follow the error message or contact our customer service in \"Account\"</li>\n</ol>', 'zh', '<p class=\"font-12 mb-0\"><strong>请注意，所有的汇率都是估计的，最终的汇率可能在这个过程中发生变化。</strong></p>\n<p class=\"font-12 mb-0\">请按照以下步骤进行账户间的兑换。</p>\n<ol class=\"font-11 mb-0 ps-3\">\n<li>选择 \"转出\" 和 \"转入\"账户并查看账户余额</li>\n<li>输入你想从 \"转出\" 账户兑换的金额，确保你的 \"转出\" 账户有足够的账户余额。</li>\n<li>我们将提示并显示当前的估计汇率和估计的最终金额（由于汇率更新快，不保证）。</li>\n<li>阅读并接受条款和条件</li>\n<li>点击 \"兑换并转入账户 \"按钮，提交兑换请求。</li>\n<li>兑换行动将立即完成，你应该能够在 \"资产 \"中检查行动和交易的状态</li>\n<li>如果在这个过程中出现任何错误，你可以关注错误信息或在 \"账户 \"中联系我们的客服人员</li>\n</ol>'),
	(10, '566f05c44027f31f19d7e865005afadc', 'en', '<p class=\"font-12 mb-0\"><strong>Please note that wired withdrawal services will be charged a 5% processing fee and minimum equivalent US$1,000, and cash withdrawal services will be charged a 20% processing fee and minimum equivalent US$100,000</strong></p>\n<p class=\"font-12 mb-0\">Please follow the steps below to request the withdrawal:</p>\n<ol class=\"font-11 mb-0 ps-3\">\n<li>Choose the withdrawal account</li>\n<li>Choose the withdrawal types, withdrawal addresses are required for \"Cash\", and your bank account is required for \"Wired\"</li>\n<li>You can redirect to the address or bank account management page by clicking the quick link</li>\n<li>Enter the total amount you would like to withdraw, and make sure you have enough account balance</li>\n<li>Read and accept the Terms and Conditions</li>\n<li>Click the \"withdraw funds\" button to submit the withdrawal request</li>\n<li>The withdrawal will be performed as soon as customer service receives the request, we will contact you directly to confirm the final delivery</li>\n<li>If there are any errors during the process, you can follow the error message or contact our customer service in \"Account\"</li>\n</ol>', 'zh', '<p class=\"font-12 mb-0\"><strong>请注意，电汇取款服务将被收取5%的手续费和最低等值1000美元，现金取款服务将被收取20%的手续费和最低等值10万美元。</strong></p>\n<p class=\"font-12 mb-0\">请按照以下步骤申请提款。</p>\n<ol class=\"font-11 mb-0 ps-3\">\n<li>选择取款账户</li>\n<li>选择提款类型，\"现金 \"需要提款地址，\"电汇\" 需要您的银行账户</li>\n<li>你可以通过点击快速链接重定向到地址或银行账户管理页面</li>\n<li>输入你想提取的总金额，并确保你有足够的账户余额</li>\n<li>阅读并接受条款和条件</li>\n<li>点击 \"确认提现 \"按钮，提交提款请求</li>\n<li>客服人员收到请求后，将立即进行提款，我们将直接与您联系，确认最终交付。</li>\n<li>如果在此过程中出现任何错误，您可以根据错误信息或在 \"账户 \"中联系我们的客服人员</li>\n</ol>'),
	(11, 'a9d954be2907602caf74de116b7ddf2b', 'en', '<p class=\"font-12 mb-0\"><strong>Ensure the network you choose to deposit matches the withdrawal network or assets may be lost. Please follow the steps below to make the deposit</strong>:</p>\n<ol class=\"font-11 mb-0 ps-3\">\n<li>Choose the deposit network and copy the USDT deposit address</li>\n<li>Make sure to send only USDT to this deposit address</li>\n<li>Enter the total amount you will deposit</li>\n<li>Read and accept the Terms and Conditions</li>\n<li>Click the \"deposit funds\" button to submit a deposit request</li>\n<li><strong>You should transfer the fund when the deposit request is created successfully, our customer service will confirm and set the received amount to your USDT account</strong></li>\n<li>If there are any errors during the process, you can follow the error message or contact our customer service in \"Account\"</li>\n</ol>', 'zh', '<p class=\"font-12 mb-0\"><strong>确保你选择的存款网络与提款网络一致，否则资产可能会丢失。请按照以下步骤</strong>进行入金。</p>\n<ol class=\"font-11 mb-0 ps-3\">\n<li>选择存款网络并复制USDT存款地址</li>\n<li>确保只发送USDT到这个存款地址</li>\n<li>输入你将存入的总金额</li>\n<li>阅读并接受条款和条件</li>\n<li>点击 \"确认充值 \"按钮，提交存款请求</li>\n<li><strong>当存款请求创建成功后，您应该转移资金，我们的客户服务将确认并设置收到的金额到您的USDT帐户</strong></li>\n<li>如果在此过程中出现任何错误，您可以根据错误信息或在 \"账户 \"中联系我们的客服人员</li>\n</ol>'),
	(12, '104a487080a32283cc287672cc92fe26', 'en', '<p class=\"font-12 mb-0\"><strong>Please note that there is no extra fee applied to our transfer service</strong></p>\n<p class=\"font-12 mb-0\">Please follow the steps below to transfer funds to other platform users:</p>\n<ol class=\"font-11 mb-0 ps-3\">\n<li>Choose an Account and review your account balance</li>\n<li>Fill in the receiver\'s full name and email address which should exactly match what they have on the platform account profile</li>\n<li>Enter the transfer amount you would like to transfer, make sure you have enough account balance</li>\n<li>Read and accept the Terms and Conditions</li>\n<li>Click the \"transfer to friend\" button to submit the transfer request</li>\n<li>The transfer action will be done immediately, and you should be able to check the status of the action and transaction in \"Asset\"</li>\n<li>If there are any errors during the process, you can follow the error message or contact our customer service in \"Account\"</li>\n</ol>', 'zh', '<p class=\"font-12 mb-0\"><strong>请注意，我们的转账服务不收取任何额外费用。</strong></p>\n<p class=\"font-12 mb-0\">请按照以下步骤将资金转给其他平台用户。</p>\n<ol class=\"font-11 mb-0 ps-3\">\n<li>选择一个账户并查看您的账户余额</li>\n<li>填写收款人的全名和电子邮件地址，应与他们在平台账户资料中的内容完全一致</li>\n<li>输入你想转移的金额，确保你有足够的账户余额</li>\n<li>阅读并接受条款和条件</li>\n<li>点击 \"确认转账 \"按钮，提交转账请求</li>\n<li>转账行动将立即完成，你应该能够在 \"资产 \"中检查行动和交易的状态</li>\n<li>如果在这个过程中出现任何错误，你可以关注错误信息或在 \"账户 \"中联系我们的客服人员</li>\n</ol>');
");


        $terms = '<h4>1. Terms and Conditions</h4>
          <p>
            By accessing this website, you are agreeing to be bound by these website Terms and Conditions of Use, all applicable laws and regulations, and agree that you are responsible for compliance with any applicable local laws. If you do not agree with any of these terms, you are prohibited from using or accessing this site. The materials contained in this website are protected by applicable copyright and trademark law.
          </p>
          <h4>2. Account Agreement</h4>
          <p>
              Your online transactions with American Funds are subject to written confirmation to ensure accuracy. All information obtained and transactions requested through this site are subject to confirmation in writing by us. Without written confirmation from us, no assurance can be given that the information obtained will be accurate or that the transactions requested will be processed. The online acknowledgments or other messages that appear on your screen for transactions requested do not mean that the transaction requests have been accepted or rejected. These acknowledgments are only an indication that the transactional information entered by you has either been transmitted, or that it cannot be transmitted. In addition, transactions are subject to certain requirements and restrictions outlined in the applicable fund’s prospectus and statement of additional information. Please review your transaction confirmations to verify the accuracy of your account and transaction information, and notify us immediately of any errors or inaccuracies.
          </p>
          <h4>3. Disclaimer</h4>
          <p>
            The materials on Capital Group Trust website are provided "as is". Capital Group Trust makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties, including without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights. Further, Capital Group Trust does not warrant or make any representations concerning the accuracy, likely results, or reliability of the use of the materials on its Internet web site or otherwise relating to such materials or on any sites linked to this site.
          </p>
          <h4>4. Limitations</h4>
          <p>
            In no event shall Capital Group Trust or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption,) arising out of the use or inability to use the materials on Capital Group Trust Internet site, even if Capital Group Trust or a Capital Group Trust authorized representative has been notified orally or in writing of the possibility of such damage. Because some jurisdictions do not allow limitations on implied warranties, or limitations of liability for consequential or incidental damages, these limitations may not apply to you.
          </p>
          <h4>5. Revisions and Errata</h4>
          <p>
            The materials appearing on Capital Group Trust website could include technical, typographical, or photographic errors. Capital Group Trust does not warrant that any of the materials on its website are accurate, complete, or current. Capital Group Trust may make changes to the materials contained on its website at any time without notice. Capital Group Trust does not, however, make any commitment to update the materials.
          </p>
          <h4>6. Site Terms of Use Modifications</h4>
          <p>
            Capital Group Trust may revise these terms of use for its website at any time without notice. By using this website you are agreeing to be bound by the then current version of these Terms and Conditions of Use.
          </p>
          <h4>8. Governing Law</h4>
          <p>
            Any claim relating to Capital Group Trust website shall be governed by the laws of the State of United States without regard to its conflict of law provisions.
          </p>';
        Setting::where('name', Setting::TRANSLATABLE_HTML_TERMS_APP)->update(['value' => $terms]);

        $depositHelpHTML = '<p class="font-12 mb-0"><strong>Ensure the network you choose to deposit matches the withdrawal network or assets may be lost. Please follow the steps below to make the deposit</strong>:</p>
<ol class="font-11 mb-0 ps-3">
<li>Choose the deposit network and copy the USDT deposit address</li>
<li>Make sure to send only USDT to this deposit address</li>
<li>Enter the total amount you will deposit</li>
<li>Read and accept the Terms and Conditions</li>
<li>Click the "deposit funds" button to submit a deposit request</li>
<li><strong>You should transfer the fund when the deposit request is created successfully, our customer service will confirm and set the received amount to your USDT account</strong></li>
<li>If there are any errors during the process, you can follow the error message or contact our customer service in "Account"</li>
</ol>';
        Setting::where('name', Setting::TRANSLATABLE_HTML_HELP_DEPOSIT)->update(['value' => $depositHelpHTML]);

        $exchangeHelpHTML = '<p class="font-12 mb-0"><strong>Please note that all exchange rates are estimated and the final rate may change during the process</strong></p>
<p class="font-12 mb-0">Please follow the steps below to exchange between accounts:</p>
<ol class="font-11 mb-0 ps-3">
<li>Choose "From" and "To" Accounts and review the account balances</li>
<li>Enter the exchange amount you would like to exchange from the From account, make sure you have enough account balance in your "From" account</li>
<li>We will prompt and display the current estimated exchange rate and the estimated final amount (No guarantee due to fast exchange rate update)</li>
<li>Read and accept the Terms and Conditions</li>
<li>Click the "convert and add to account" button to submit the exchange request</li>
<li>The exchange action will be done immediately, and you should be able to check the status of the action and transaction in "Asset"</li>
<li>If there are any errors during the process, you can follow the error message or contact our customer service in "Account"</li>
</ol>';
        Setting::where('name', Setting::TRANSLATABLE_HTML_HELP_EXCHANGE)->update(['value' => $exchangeHelpHTML]);

        $transferHelpHTML = '<p class="font-12 mb-0"><strong>Please note that there is no extra fee applied to our transfer service</strong></p>
<p class="font-12 mb-0">Please follow the steps below to transfer funds to other platform users:</p>
<ol class="font-11 mb-0 ps-3">
<li>Choose an Account and review your account balance</li>
<li>Fill in the receiver\'s full name and email address which should exactly match what they have on the platform account profile</li>
<li>Enter the transfer amount you would like to transfer, make sure you have enough account balance</li>
<li>Read and accept the Terms and Conditions</li>
<li>Click the "transfer to friend" button to submit the transfer request</li>
<li>The transfer action will be done immediately, and you should be able to check the status of the action and transaction in "Asset"</li>
<li>If there are any errors during the process, you can follow the error message or contact our customer service in "Account"</li>
</ol>';
        Setting::where('name', Setting::TRANSLATABLE_HTML_HELP_TRANSFER)->update(['value' => $transferHelpHTML]);

        $purchaseHelpHTML = '<p class="font-12 mb-0"><strong>Purchase the investment product with any amount more than the minimum requirement, the purchase will take the fund directly from the related account and freeze for a given day. You can monitor the purchased investment returns from "Invest". </strong></p>
<p class="font-12 mb-0">Please follow the steps below to make the purchase:</p>
<ol class="font-11 mb-0 ps-3">
<li>Read the product description</li>
<li>Make sure you have enough account balance, the account balance will be shown under the amount field</li>
<li>Fill in the amount of fund you would like to purchase for the product, <strong>make sure it is more than the minimum requirement</strong></li>
<li>Read and accept the Terms and Conditions</li>
<li>Click "confirm purchase" button to finish the purchase</li>
<li>If there are any errors during the process, you can follow the error message or contact our customer service in "Account"</li>
</ol>';
        Setting::where('name', Setting::TRANSLATABLE_HTML_HELP_PURCHASE)->update(['value' => $purchaseHelpHTML]);

        $withdrawHelpHTML = '<p class="font-12 mb-0"><strong>Please note that wired withdrawal services will be charged a 5% processing fee and minimum equivalent US$1,000, and cash withdrawal services will be charged a 20% processing fee and minimum equivalent US$100,000</strong></p>
<p class="font-12 mb-0">Please follow the steps below to request the withdrawal:</p>
<ol class="font-11 mb-0 ps-3">
<li>Choose the withdrawal account</li>
<li>Choose the withdrawal types, withdrawal addresses are required for "Cash", and your bank account is required for "Wired"</li>
<li>You can redirect to the address or bank account management page by clicking the quick link</li>
<li>Enter the total amount you would like to withdraw, and make sure you have enough account balance</li>
<li>Read and accept the Terms and Conditions</li>
<li>Click the "withdraw funds" button to submit the withdrawal request</li>
<li>The withdrawal will be performed as soon as customer service receives the request, we will contact you directly to confirm the final delivery</li>
<li>If there are any errors during the process, you can follow the error message or contact our customer service in "Account"</li>
</ol>';
        Setting::where('name', Setting::TRANSLATABLE_HTML_HELP_WITHDRAW)->update(['value' => $withdrawHelpHTML]);

        $helpApp = '<h4>Purchase</h4>
          <p class="font-14 mb-0"><strong>Purchase the investment product with any amount more than the minimum requirement, the purchase will take the fund directly from the related account and freeze for a given day. You can monitor the purchased investment returns from "Invest". </strong></p>
<p class="font-14 mb-0">Please follow the steps below to make the purchase:</p>
<ol class="font-12 mb-0 ps-3">
<li>Read the product description</li>
<li>Make sure you have enough account balance, the account balance will be shown under the amount field</li>
<li>Fill in the amount of fund you would like to purchase for the product, <strong>make sure it is more than the minimum requirement</strong></li>
<li>Read and accept the Terms and Conditions</li>
<li>Click "confirm purchase" button to finish the purchase</li>
<li>If there are any errors during the process, you can follow the error message or contact our customer service in "Account"</li>
</ol><br/>
          <h4>Deposit</h4>
          <p class="font-14 mb-0"><strong>Ensure the network you choose to deposit matches the withdrawal network or assets may be lost. Please follow the steps below to make the deposit</strong>:</p>
<ol class="font-12 mb-0 ps-3">
<li>Choose the deposit network and copy the USDT deposit address</li>
<li>Make sure to send only USDT to this deposit address</li>
<li>Enter the total amount you will deposit</li>
<li>Read and accept the Terms and Conditions</li>
<li>Click the "deposit funds" button to submit a deposit request</li>
<li><strong>You should transfer the fund when the deposit request is created successfully, our customer service will confirm and set the received amount to your USDT account</strong></li>
<li>If there are any errors during the process, you can follow the error message or contact our customer service in "Account"</li>
</ol><br/>
          <h4>Withdraw</h4>
          <p class="font-14 mb-0"><strong>Please note that wired withdrawal services will be charged a 5% processing fee and minimum equivalent US$1,000, and cash withdrawal services will be charged a 20% processing fee and minimum equivalent US$100,000</strong></p>
<p class="font-14 mb-0">Please follow the steps below to request the withdrawal:</p>
<ol class="font-12 mb-0 ps-3">
<li>Choose the withdrawal account</li>
<li>Choose the withdrawal types, withdrawal addresses are required for "Cash", and your bank account is required for "Wired"</li>
<li>You can redirect to the address or bank account management page by clicking the quick link</li>
<li>Enter the total amount you would like to withdraw, and make sure you have enough account balance</li>
<li>Read and accept the Terms and Conditions</li>
<li>Click the "withdraw funds" button to submit the withdrawal request</li>
<li>The withdrawal will be performed as soon as customer service receives the request, we will contact you directly to confirm the final delivery</li>
<li>If there are any errors during the process, you can follow the error message or contact our customer service in "Account"</li>
</ol><br/>
          <h4>Exchange</h4>
          <p class="font-14 mb-0"><strong>Please note that all exchange rates are estimated and the final rate may change during the process</strong></p>
<p class="font-14 mb-0">Please follow the steps below to exchange between accounts:</p>
<ol class="font-12 mb-0 ps-3">
<li>Choose "From" and "To" Accounts and review the account balances</li>
<li>Enter the exchange amount you would like to exchange from the From account, make sure you have enough account balance in your "From" account</li>
<li>We will prompt and display the current estimated exchange rate and the estimated final amount (No guarantee due to fast exchange rate update)</li>
<li>Read and accept the Terms and Conditions</li>
<li>Click the "convert and add to account" button to submit the exchange request</li>
<li>The exchange action will be done immediately, and you should be able to check the status of the action and transaction in "Asset"</li>
<li>If there are any errors during the process, you can follow the error message or contact our customer service in "Account"</li>
</ol><br/>
          <h4>Transfer</h4>
          <p class="font-14 mb-0"><strong>Please note that there is no extra fee applied to our transfer service</strong></p>
<p class="font-14 mb-0">Please follow the steps below to transfer funds to other platform users:</p>
<ol class="font-12 mb-0 ps-3">
<li>Choose an Account and review your account balance</li>
<li>Fill in the receiver\'s full name and email address which should exactly match what they have on the platform account profile</li>
<li>Enter the transfer amount you would like to transfer, make sure you have enough account balance</li>
<li>Read and accept the Terms and Conditions</li>
<li>Click the "transfer to friend" button to submit the transfer request</li>
<li>The transfer action will be done immediately, and you should be able to check the status of the action and transaction in "Asset"</li>
<li>If there are any errors during the process, you can follow the error message or contact our customer service in "Account"</li>
</ol>';
        Setting::where('name', Setting::TRANSLATABLE_HTML_HELP_APP)->update(['value' => $helpApp]);

        $accountJson = json_encode([
            [
                'token' => 'ETH: ERC20 & ERC721',
                'address' => '0x92793b4f05bce7828844a73af5e56D6155fFe2bE',
            ],
            [
                'token' => 'TRON: TRX & TRC10 & TRC20',
                'address' => 'TWEX7gQiFxgvgiFjam3P4c9m2nCCc3vA3W'
            ],
        ]);
        Setting::where('name', Setting::JSON_BANK_ACCOUNT)->update(['value' => $accountJson]);
    }

    private function bootAdminUser()
    {
        $this->info('Initializing Admin User...');

        $username = 'admin';
        $password = '';
        $email = 'admin@test.com';
        $accessToken = 'xx';

        $this->userService->create(
            1,
            $accessToken,
            'Administrator',
            $email,
            Hash::make($password),
            $username,
            'en',
            [Role::ROLE_ADMINISTRATOR_ID]
        );

        $this->info('admin user created');
        $this->info('username: ' . $username);
        $this->info('password: ' . $password);
        $this->info('jwt: ' . $accessToken);
    }
}
