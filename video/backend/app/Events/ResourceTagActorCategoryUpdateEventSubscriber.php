<?php

namespace App\Events;

use App\Services\ResourceActorService;
use App\Services\ResourceCategoryService;
use App\Services\ResourceTagService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Events\Dispatcher;

class ResourceTagActorCategoryUpdateEventSubscriber implements ShouldQueue
{
    private ResourceTagService $resourceTagService;
    private ResourceActorService $resourceActorService;
    private ResourceCategoryService $resourceCategoryService;

    public function __construct(ResourceTagService $resourceTagService, ResourceActorService $resourceActorService, ResourceCategoryService $resourceCategoryService) {
        $this->resourceTagService = $resourceTagService;
        $this->resourceActorService = $resourceActorService;
        $this->resourceCategoryService = $resourceCategoryService;
    }

    public function handleAddTag(ResourceTagAddTagEvent $addEvent): bool {
        return $this->resourceTagService->buildVideoTagsFromResourceTags($addEvent->resourceTagId, null);
    }

    public function handleRemoveTag(ResourceTagRemoveTagEvent $removeEvent): bool {
        return $this->resourceTagService->buildVideoTagsFromResourceTags($removeEvent->resourceTagId, $removeEvent->removedTagId);
    }

    public function handleAddActor(ResourceActorAddActorEvent $addEvent): bool {
        return $this->resourceActorService->buildVideoActorsFromResourceActors($addEvent->resourceActorId, null);
    }

    public function handleRemoveActor(ResourceActorRemoveActorEvent $removeEvent): bool {
        return $this->resourceActorService->buildVideoActorsFromResourceActors($removeEvent->resourceActorId, $removeEvent->removedActorId);
    }

    public function handleAddCategory(ResourceCategoryAddCategoryEvent $addEvent): bool {
        return $this->resourceCategoryService->buildVideoCategoriesFromResourceCategories($addEvent->resourceCategoryId, null);
    }

    public function handleRemoveCategory(ResourceCategoryRemoveCategoryEvent $removeEvent): bool {
        return $this->resourceCategoryService->buildVideoCategoriesFromResourceCategories($removeEvent->resourceCategoryId, $removeEvent->removedCategoryId);
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            ResourceTagAddTagEvent::class => 'handleAddTag',
            ResourceTagRemoveTagEvent::class => 'handleRemoveTag',
            ResourceActorAddActorEvent::class => 'handleAddActor',
            ResourceActorRemoveActorEvent::class => 'handleRemoveActor',
            ResourceCategoryAddCategoryEvent::class => 'handleAddCategory',
            ResourceCategoryRemoveCategoryEvent::class => 'handleRemoveCategory',
        ];
    }
}
