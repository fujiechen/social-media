<?php

use App\Dtos\TagDto;
use App\Models\ResourceTag;
use App\Models\Tag;
use App\Services\TagService;
use Tests\TestCase;

class TagServiceTest extends TestCase
{
    public function testUpdateOrCreateTagWithoutResourceTags(): void {
        $dto = new TagDto([
            'name' => 'test tag',
        ]);

        /**
         * @var TagService $tagService
         */
        $tagService = app(TagService::class);
        $tag = $tagService->updateOrCreateTag($dto);
        $this->assertEquals('test tag', $tag->name);

        $dto = new TagDto([
            'name' => 'test tag',
        ]);

        $tag = $tagService->updateOrCreateTag($dto);

        $this->assertEquals('test tag', $tag->name);
        $this->assertEquals(1,
            Tag::query()->where('name', '=', 'test tag')->count());


        $dto = new TagDto([
            'tagId' => $tag->id,
            'name' => 'test tag2',
        ]);

        $tag = $tagService->updateOrCreateTag($dto);
        $this->assertEquals('test tag2', $tag->name);
        $this->assertEquals(0,
            Tag::query()->where('name', '=', 'test tag')->count());
    }

    public function testUpdateOrCreateTagWithResourceTags(): void {
        /**
         * @var TagService $tagService
         */
        $tagService = app(TagService::class);

        $resourceTag1 = ResourceTag::create(['name' => 'resource tag1']);
        $resourceTag2 = ResourceTag::create(['name' => 'resource tag2']);

        //create
        $tag = $tagService->updateOrCreateTag(new TagDto([
            'name' => 'tag',
            'resourceTagIds' => [$resourceTag1->id, $resourceTag2->id],
        ]));

        $resourceTag1->refresh();
        $this->assertEquals($tag->id, $resourceTag1->tag_id);

        $resourceTag2->refresh();
        $this->assertEquals($tag->id, $resourceTag2->tag_id);

        $this->assertEquals(2, $tag->resourceTags()->count());

        //update
        $tag = $tagService->updateOrCreateTag(new TagDto([
            'name' => 'tag',
            'resourceTagIds' => [$resourceTag1->id],
        ]));

        $this->assertEquals(1, $tag->resourceTags()->count());

        $resourceTag1->refresh();
        $this->assertEquals($tag->id, $resourceTag1->tag_id);

        $resourceTag2->refresh();
        $this->assertNull($resourceTag2->tag_id);
    }
}
