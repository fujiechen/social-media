<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Http;

/**
 * For DO:
 * update url and video queue id
 *
 * unset DATABASE_DB && unset DATABASE_URL && php artisan video:reprocess-complete-video-queue
 */
class ReProcessCompleteVideoQueueRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:reprocess-complete-video-queue';

    protected $description = 'Load from activity log and reprocess the video queue complete request';


    /**
     * Execute the console command.
     */
    public function handle(Logger $logger): void
    {
        $logger->info('started running video:reprocess-complete-video-queue ... ');

        for ($i = 1638; $i < 1698; $i++) {
            $activityLog = ActivityLog::query()
                ->where('event', 'like', '%api/video/queues/' . $i . '/completed')
                ->where('description', '=', 'POST')
                ->first();

            if (!$activityLog) {
                continue;
            }

            $payload = json_decode($activityLog->properties, true);

            $videoQueuePayload = $payload['data'];
            $jwtToken = $videoQueuePayload['user']['access_token'];

            Http::withHeaders([
                'Authorization' => 'Bearer ' . $jwtToken,
                'Accept' => 'application/json'
            ])->post('https://xxx/api/video/queues/' . $i . '/completed', $videoQueuePayload);
        }

        $logger->info('completed running video:reprocess-complete-video-queue ... ');
    }
}
