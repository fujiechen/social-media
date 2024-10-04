<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Services\PostService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateDraftPost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'promotion:create-draft-post';

    protected $description = 'Create Draft Post';


    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /**
         * @var PostService $postService
         */
        $postService = app(PostService::class);

        $startOfToday = Carbon::today()->startOfDay();
        $endOfToday = Carbon::today()->endOfDay();

        $accounts = Account::where('status', '=', Account::STATUS_ACTIVE)->get();
        foreach ($accounts as $account) {
            if ($postService
                ->fetchPostQuery($account->id, $startOfToday, $endOfToday)
                ->count()) {
                continue;
            }
            $postService->createDraftPost('挑选创意模版创建Post, 创建完毕后更新状态为Active', $account->id);
        }
    }
}
