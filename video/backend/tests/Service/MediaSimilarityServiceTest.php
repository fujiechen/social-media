<?php

use App\Dtos\MediaDto;
use App\Models\Media;
use App\Models\MediaTag;
use App\Models\Role;
use App\Models\Tag;
use App\Services\MediaSearchService;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaSimilarityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testFetchSimilarMediasByVideoInTheSameSeries(): void {
        /**
         * @var MediaService $mediaService
         */
        $mediaService = app(MediaService::class);

        $video1 = $this->createVideo();
        $video2 = $this->createVideo();
        $series = $this->createSeries([$video1->id, $video2->id]);

        $seriesMedia = $mediaService->updateOrCreateMedia(new MediaDto([
            'mediaId' => 0,
            'userId' => $this->adminUser()->id,
            'mediaableType' => Media::toMediaableType(Media::TYPE_SERIES),
            'seriesId' => $series->id,
            'name' => $this->faker()->name,
            'description' => $this->faker()->text,
            'mediaRoleIds' => [Role::ROLE_VISITOR_ID],
            'mediaPermission' => Media::MEDIA_PERMISSION_ROLE,
        ]));

        $this->assertEquals(2, $seriesMedia->childrenMedias->count());

        $medias1 = $mediaService->fetchSimilarMediasByVideoMedia($seriesMedia->childrenMedias->get(0)->id);
        $this->assertEquals(1, $medias1->count()); //video 1 media

        $medias2 = $mediaService->fetchSimilarMediasByVideoMedia($seriesMedia->childrenMedias->get(1)->id);
        $this->assertEquals(1, $medias2->count()); //video 2 media
    }

    public function testFetchSimilarMediasByVideoInSimilarSearches(): void {
        $tag1 = Tag::create(['name' => $this->faker()->name]);
        $tag2 = Tag::create(['name' => $this->faker()->name]);
        $tag3 = Tag::create(['name' => $this->faker()->name]);
        $tag4 = Tag::create(['name' => $this->faker()->name]);

        /**
         * @var MediaSearchService $mediaSearchService
         */
        $mediaSearchService = app(MediaSearchService::class);

        $media1 = $this->createMediaWithRolePermission($this->createVisitor()->id, [Role::ROLE_VISITOR_ID]);
        MediaTag::create(['media_id' => $media1->id, 'tag_id' => $tag1->id]);
        MediaTag::create(['media_id' => $media1->id, 'tag_id' => $tag2->id]);
        MediaTag::create(['media_id' => $media1->id, 'tag_id' => $tag3->id]);
        $mediaSearchService->reBuildMediaSearchText($media1->id);

        $media2 = $this->createMediaWithRolePermission($this->createVisitor()->id, [Role::ROLE_VISITOR_ID]);
        MediaTag::create(['media_id' => $media2->id, 'tag_id' => $tag3->id]);
        $mediaSearchService->reBuildMediaSearchText($media2->id);

        $media3 = $this->createMediaWithRolePermission($this->createVisitor()->id, [Role::ROLE_VISITOR_ID]);
        MediaTag::create(['media_id' => $media3->id, 'tag_id' => $tag4->id]);
        $mediaSearchService->reBuildMediaSearchText($media3->id);

        /**
         * @var MediaService $mediaService
         */
        $mediaService = app(MediaService::class);

        /**
         * media1 has tag1, tag2, tag3
         * media2 has tag3
         * media3 has tag4
         */
        $medias = $mediaService->fetchSimilarMediasByVideoMedia($media1->id);

        $this->assertEquals(1, $medias->count());
        $this->assertEquals($media2->id, $medias->get(0)->id);
    }
}
