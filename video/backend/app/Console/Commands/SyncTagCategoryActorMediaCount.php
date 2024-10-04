<?php

namespace App\Console\Commands;

use App\Models\Actor;
use App\Models\Category;
use App\Models\Tag;
use App\Services\ActorService;
use App\Services\CategoryService;
use App\Services\TagService;
use Illuminate\Console\Command;
use Illuminate\Log\Logger;

class SyncTagCategoryActorMediaCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:sync-tag-category-actor-count';

    protected $description = 'Sync Tag, Category and Actor Active Media Count';


    /**
     * Execute the console command.
     */
    public function handle(Logger $logger): void
    {
        /**
         * @var TagService $tagService
         */
        $tagService = app(TagService::class);

        /**
         * @var ActorService $actorService
         */
        $actorService = app(ActorService::class);

        /**
         * @var CategoryService $categoryService
         */
        $categoryService = app(CategoryService::class);

        $logger->info('running video:sync-tag-category-actor-count ... ');

        $tags = Tag::query();
        $tags->chunk(100, function ($chunk) use ($tagService, $actorService) {
            /**
             * @var Tag $tag
             */
            foreach ($chunk as $tag) {
                $tagService->syncActiveMediaCount($tag);
            }
        });

        $actors = Actor::query();
        $actors->chunk(100, function ($chunk) use ($actorService) {
            /**
             * @var Actor $actor
             */
            foreach ($chunk as $actor) {
                $actorService->syncActiveMediaCount($actor);
            }
        });

        $categories = Category::query();
        $categories->chunk(100, function ($chunk) use ($categoryService) {
            /**
             * @var Category $category
             */
            foreach ($chunk as $category) {
                $categoryService->syncActiveMediaCount($category);
            }
        });

        $logger->info('completed running video:sync-media-count ... ');
    }
}
