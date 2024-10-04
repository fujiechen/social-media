<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\MediaCommentService;
use App\Services\MediaFavoriteService;
use App\Services\MediaLikeService;
use Illuminate\Console\Command;
use Illuminate\Log\Logger;

class SyncMediaCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:sync-media-count';

    protected $description = 'Sync media like, favorites, comment and children count';


    /**
     * Execute the console command.
     */
    public function handle(Logger $logger): void
    {
        $medias = Media::query()->where('status','=', Media::STATUS_ACTIVE);

        $mediaLikeService = app(MediaLikeService::class);
        $mediaFavoriteService = app(MediaFavoriteService::class);
        $mediaCommentService = app(MediaCommentService::class);

        $medias->chunk(100, function ($chunk)
            use ($mediaLikeService, $mediaFavoriteService, $mediaCommentService) {
            /**
             * @var Media $media
             */
            foreach ($chunk as $media) {
                $totalLikes = $mediaLikeService->getTotalMediaCount($media->id);
                $totalFavorites = $mediaFavoriteService->getTotalMediaCount($media->id);
                $totalComments = $mediaCommentService->getTotalMediaCount($media->id);
                $totalChildren = $media->childrenMedias->count();

                Media::withoutEvents(function () use ($media, $totalLikes, $totalFavorites, $totalComments, $totalChildren ) {
                    $media->likes_count = $totalLikes;
                    $media->favorites_count = $totalFavorites;
                    $media->comments_count = $totalComments;
                    $media->children_count = $totalChildren;
                    $media->save();
                });
            }
        });

        $logger->info('completed running video:sync-media-count ... ');
    }
}
