<?php

use App\Dtos\MediaDto;
use App\Dtos\MediaSearchDto;
use App\Dtos\UploadFileDto;
use App\Dtos\VideoDto;
use App\Events\OrderSavedEvent;
use App\Models\Actor;
use App\Models\Category;
use App\Models\File;
use App\Models\Media;
use App\Models\Order;
use App\Models\Role;
use App\Models\Tag;
use App\Models\Video;
use App\Services\MediaService;
use App\Services\UserFollowingService;
use App\Services\VideoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MediaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testFetchAllMediasQueryWithVisitor(): void {
        /**
         * @var MediaService $mediaService
         */
        $mediaService = app(MediaService::class);

        /**
         * @var VideoService $videoService
         */
        $videoService = app(VideoService::class);

        $video = $videoService->updateOrCreateVideo(new VideoDto([
            'type' => Video::TYPE_UPLOAD,
            'name' => $this->faker()->name,
            'description' => $this->faker()->text,
            'thumbnailFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_LOCAL_BUCKET,
                'uploadPath' => '',
            ]),
            'previewFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_LOCAL_BUCKET,
                'uploadPath' => '',
            ]),
            'videoFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_LOCAL_BUCKET,
                'uploadPath' => '',
            ]),
        ]));

        $roleVisitorMedia = $mediaService->updateOrCreateMedia(new MediaDto([
            'userId' => $this->adminUser()->id,
            'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
            'mediaPermission' => Media::MEDIA_PERMISSION_ROLE,
            'videoId' => $video->id,
            'mediaRoleIds' => [Role::ROLE_VISITOR_ID, Role::ROLE_USER_ID],
        ]));

        $roleUserMedia = $mediaService->updateOrCreateMedia(new MediaDto([
            'userId' => $this->adminUser()->id,
            'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
            'mediaPermission' => Media::MEDIA_PERMISSION_ROLE,
            'videoId' => $video->id,
            'mediaRoleIds' => [Role::ROLE_USER_ID],
        ]));

        $subscriptionMedia = $mediaService->updateOrCreateMedia(new MediaDto([
            'userId' => $this->adminUser()->id,
            'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
            'mediaPermission' => Media::MEDIA_PERMISSION_SUBSCRIPTION,
            'videoId' => $video->id,
        ]));

        $user = $this->createUser();
        $userMedias = $mediaService->fetchAllMediasQuery(new MediaSearchDto([
            'userId' => $user->id,
        ]));
        $this->assertEquals(3, $userMedias->count());
        $this->assertTrue($userMedias->pluck('id')->contains($roleVisitorMedia->id));
        $this->assertTrue($userMedias->pluck('id')->contains($roleUserMedia->id));
        $this->assertTrue($userMedias->pluck('id')->contains($subscriptionMedia->id));
    }

    public function testFetchAllMediasQueryGeneral(): void
    {
        /**
         * @var MediaService $mediaService
         */
        $mediaService = app(MediaService::class);

        /**
         * @var VideoService $videoService
         */
        $videoService = app(VideoService::class);

        $tag1 = Tag::create(['name' => 'tag1']);
        $tag2 = Tag::create(['name' => 'tag2']);
        $category1 = Category::create(['name' => 'cat1']);
        $actor1 = Actor::create(['name' => 'actor1', 'country' => 'CN']);

        $video1 = $videoService->updateOrCreateVideo(new VideoDto([
            'type' => Video::TYPE_UPLOAD,
            'name' => $this->faker()->name,
            'description' => $this->faker()->text,
            'thumbnailFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_LOCAL_BUCKET,
                'uploadPath' => '',
            ]),
            'previewFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_LOCAL_BUCKET,
                'uploadPath' => '',
            ]),
            'videoFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_LOCAL_BUCKET,
                'uploadPath' => '',
            ]),
            'tagIds' => [$tag1->id],
            'categoryIds' => [$category1->id],
            'actorIds' => [$actor1->id]
        ]));

        $user1 = $this->createVisitor();
        $media1 = $mediaService->updateOrCreateMedia(new MediaDto([
            'userId' => $user1->id,
            'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
            'mediaPermission' => Media::MEDIA_PERMISSION_ROLE,
            'videoId' => $video1->id,
            'mediaRoleIds' => [Role::ROLE_VISITOR_ID],
        ]));

        $video2 = $videoService->updateOrCreateVideo(new VideoDto([
            'type' => Video::TYPE_UPLOAD,
            'name' => $this->faker()->name,
            'description' => $this->faker()->text,
            'thumbnailFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_LOCAL_BUCKET,
                'uploadPath' => '',
            ]),
            'previewFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_LOCAL_BUCKET,
                'uploadPath' => '',
            ]),
            'videoFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_LOCAL_BUCKET,
                'uploadPath' => '',
            ]),
            'tagIds' => [$tag2->id],
            'actorIds' => [$actor1->id]
        ]));

        $user2 = $this->createVisitor();
        $media2 = $mediaService->updateOrCreateMedia(new MediaDto([
            'userId' => $user2->id,
            'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
            'mediaPermission' => Media::MEDIA_PERMISSION_ROLE,
            'videoId' => $video2->id,
            'mediaRoleIds' => [Role::ROLE_VISITOR_ID],
        ]));

        $user3 = $this->createVisitor();
        $series = $this->createSeries([$video1->id, $video2->id]);

        $media3 = $mediaService->updateOrCreateMedia(new MediaDto([
            'userId' => $user3->id,
            'mediaableType' => Media::toMediaableType(Media::TYPE_SERIES),
            'mediaPermission' => Media::MEDIA_PERMISSION_ROLE,
            'seriesId' => $series->id,
            'mediaRoleIds' => [Role::ROLE_VISITOR_ID],
        ]));

        /**
         * video1 has tag1, category1, actor1
         * video2 has tag2, actor1,
         *
         * media1 has tag1, category1, actor1, video1 name & description
         * media2 has tag2, no category, actor1, video2 name & description
         * media3 has video1 & video2
         */

        $query = $mediaService->fetchAllMediasQuery(new MediaSearchDto([
            'mediaableTypes' => [Media::toMediaableType(Media::TYPE_VIDEO)],
            'tagName' => $tag1->name
        ]));
        $this->assertEquals(2, $query->count()); //media1(video1) & media3 (series(video1, video2)

        $query = $mediaService->fetchAllMediasQuery(new MediaSearchDto([
            'mediaUserId' => $user1->id
        ]));
        $this->assertEquals(1, $query->count());

        $query = $mediaService->fetchAllMediasQuery(new MediaSearchDto([
            'mediaUserId' => $user2->id
        ]));
        $this->assertEquals(1, $query->count());

        $query = $mediaService->fetchAllMediasQuery(new MediaSearchDto([
            'mediaUserId' => $user3->id
        ]));
        $this->assertEquals(3, $query->count()); //series media has 3 medias

        $query = $mediaService->fetchAllMediasQuery(new MediaSearchDto([
            'mediaUserId' => $user1->id,
            'mediaableTypes' => [Media::toMediaableType(Media::TYPE_VIDEO)],
        ]));
        $this->assertEquals(1, $query->count());

        $query = $mediaService->fetchAllMediasQuery(new MediaSearchDto([
            'mediaUserId' => $user3->id,
            'mediaableTypes' => [Media::toMediaableType(Media::TYPE_SERIES)],
        ]));
        $this->assertEquals(1, $query->count());

        $query = $mediaService->fetchAllMediasQuery(new MediaSearchDto([
            'tagIds' => [$tag2->id]
        ]));
        $this->assertEquals(3, $query->count()); //video2 media + series media + series-video2 media

        $query = $mediaService->fetchAllMediasQuery(new MediaSearchDto([
            'actorId' => $actor1->id,
        ]));
        $this->assertEquals(5, $query->count()); //video 1 media + video 2 media + series media + series video 1 media + series video 2 media

        $query = $mediaService->fetchAllMediasQuery(new MediaSearchDto([
            'categoryId' => $category1->id
        ]));
        $this->assertEquals(3, $query->count()); //video 1 media  + series media + series video 1 media

        //video1 & series (has video1)
        $query = $mediaService->fetchAllMediasQuery(new MediaSearchDto([
            'mediaSearchText' => $video1->name
        ]));
        $this->assertEquals(3, $query->count()); //video 1 media  + series media + series video 1 media

        $query = $mediaService->fetchAllMediasQuery(new MediaSearchDto([
            'mediaSearchText' => $category1->name
        ]));
        $this->assertEquals(3, $query->count()); //video 1 media  + series media + series video 1 media

        $query = $mediaService->fetchAllMediasQuery(new MediaSearchDto([
            'mediaableTypes' => [Media::toMediaableType(Media::TYPE_VIDEO)],
            'tagName' => $tag2->name
        ]));
        $this->assertEquals(2, $query->count()); // media2 (video2) + media3 (series (video2))

        $query = $mediaService->fetchAllMediasQuery(new MediaSearchDto([
            'mediaableTypes' => [Media::toMediaableType(Media::TYPE_VIDEO)],
            'actorName' => $actor1->name
        ]));
        $this->assertEquals(4, $query->count()); // media 1(video 1) + media2 (video2) + media3 (series (video1, video2))

        $query = $mediaService->fetchAllMediasQuery(new MediaSearchDto([
            'mediaableTypes' => [Media::toMediaableType(Media::TYPE_VIDEO)],
            'categoryName' => $category1->name
        ]));
        $this->assertEquals(2, $query->count());

        $query = $mediaService->fetchAllMediasQuery(new MediaSearchDto([
            'mediaableTypes' => [Media::toMediaableType(Media::TYPE_VIDEO)],
            'nickName' => $user1->nickname
        ]));
        $this->assertEquals(1, $query->count());
    }

    public function testUpdateOrCreateMediaWithRolePermission(): void {
        $media = $this->createMediaWithRolePermission($this->adminUser()->id, [Role::ROLE_VISITOR_ID]);
        $this->assertEquals(Role::ROLE_VISITOR_ID, $media->roles->first()->id);
        $this->assertEquals(Media::MEDIA_PERMISSION_ROLE, $media->media_permission);
        $this->assertEquals(Media::TYPE_VIDEO, $media->type);
    }

    public function testUpdateOrCreateMediaWithSubscriptionPermission(): void {
        $media = $this->createMediaWithSubscriptionPermission($this->adminUser()->id);
        $this->assertEmpty($media->roles);
        $this->assertEquals(Media::MEDIA_PERMISSION_SUBSCRIPTION, $media->media_permission);
        $this->assertEquals(Media::TYPE_VIDEO, $media->type);
    }

    public function testUpdateOrCreateVideoMediaWithPurchasePermission(): void {
        $media = $this->createMediaWithPurchasePermission($this->adminUser()->id, 11.11);
        $this->assertEmpty($media->roles);
        $this->assertEquals(Media::MEDIA_PERMISSION_PURCHASE, $media->media_permission);
        $this->assertEquals(Media::TYPE_VIDEO, $media->type);
        $this->assertEquals(1111, $media->mediaProduct()->unit_cents);
    }


    public function testUpdateOrCreateSeriesMediaWithPurchasePermission(): void {
        $user = $this->createUser();
        $video1 = $this->createVideo();
        $video2 = $this->createVideo();
        $series = $this->createSeries([$video1->id, $video2->id]);

        /**
         * @var MediaService $mediaService
         */
        $mediaService = app(MediaService::class);
        $media = $mediaService->updateOrCreateMedia(new MediaDto([
            'userId' => $user->id,
            'mediaableType' => Media::toMediaableType(Media::TYPE_SERIES),
            'seriesId' => $series->id,
            'name' => $this->faker()->name,
            'description' => $this->faker()->text,
            'mediaPermission' => Media::MEDIA_PERMISSION_PURCHASE,
            'mediaProductPrice' => 11.11,
            'mediaProductCurrencyName' => 'CNY',
        ]));

        $this->assertEmpty($media->roles);
        $this->assertEquals(Media::MEDIA_PERMISSION_PURCHASE, $media->media_permission);
        $this->assertEquals(Media::TYPE_SERIES, $media->type);
        $this->assertEquals(1111, $media->mediaProduct()->unit_cents);
        $this->assertEquals(2, $media->childrenMedias->count());
        foreach ($media->childrenMedias as $child) {
            $this->assertEquals($media->mediaProduct(), $child->mediaProduct());
            $this->assertEquals($media->id, $child->parent_media_id);
        }
    }


    /**
     * @throws \App\Exceptions\IllegalArgumentException
     */
    public function testIsRoleMediaAvailableToUser(): void
    {
        $userMedia = $this->createMediaWithRolePermission($this->adminUser()->id, [Role::ROLE_USER_ID]);
        $membershipMedia = $this->createMediaWithRolePermission($this->adminUser()->id, [Role::ROLE_MEMBERSHIP_ID]);

        $user = $this->createUser();

        /**
         * @var MediaService $mediaService
         */
        $mediaService = app(MediaService::class);

        $this->assertTrue($mediaService->isMediaAvailableToUser($user->id, $userMedia->id));
        $this->assertFalse($mediaService->isMediaAvailableToUser($user->id, $membershipMedia->id));
    }

    public function testIsPurchaseMediaAvailableToUser(): void {
        /**
         * @var MediaService $mediaService
         */
        $mediaService = app(MediaService::class);
        $visitor = $this->createVisitor();

        $purchaseMedia = $this->createMediaWithPurchasePermission($this->adminUser()->id, 11.11);
        $this->assertFalse($mediaService->isMediaAvailableToUser($visitor->id, $purchaseMedia->id));

        Event::fake([OrderSavedEvent::class]);
        $this->createOrder($visitor->id, $purchaseMedia->mediaProduct()->id, Order::STATUS_COMPLETED);
        $this->assertTrue($mediaService->isMediaAvailableToUser($visitor->id, $purchaseMedia->id));
    }

    public function testIsSubscriptionMediaAvailableToUser(): void {
        /**
         * @var MediaService $mediaService
         */
        $mediaService = app(MediaService::class);
        $user = $this->createUser();

        $subscriptionMedia = $this->createMediaWithSubscriptionPermission($this->adminUser()->id);
        $this->assertFalse($mediaService->isMediaAvailableToUser($user->id, $subscriptionMedia->id));

        /**
         * @var UserFollowingService $userFollowingService
         */
        $userFollowingService = app(UserFollowingService::class);
        $userFollowingService->addSubscription($user->id, $this->adminUser()->id, null);
        $this->assertTrue($mediaService->isMediaAvailableToUser($user->id, $subscriptionMedia->id));
    }

    public function testUpdateOrCreateMediaWithVideoOfCreation(): void {

        $video = $this->createVideo();
        /**
         * @var MediaService $mediaService
         */
        $mediaService = app(MediaService::class);
        $media = $mediaService->updateOrCreateMedia(new MediaDto([
            'userId' => $this->adminUser()->id,
            'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
            'mediaPermission' => Media::MEDIA_PERMISSION_ROLE,
            'videoId' => $video->id,
            'mediaRoleIds' => [Role::ROLE_VISITOR_ID],
        ]));

        $this->assertEquals($video->name, $media->name);
        $this->assertEquals($video->description, $media->description);
        $this->assertTrue($media->isVideo());
        $this->assertEquals($video->id, $media->mediaable->id);

    }

    public function testUpdateOrCreateMediaWithSeriesOfCreation(): void {
        $video1 = $this->createVideo();
        $video2 = $this->createVideo();
        $series = $this->createSeries([$video1->id, $video2->id]);

        /**
         * @var MediaService $mediaService
         */
        $mediaService = app(MediaService::class);
        $media = $mediaService->updateOrCreateMedia(new MediaDto([
            'userId' => $this->adminUser()->id,
            'mediaableType' => Media::toMediaableType(Media::TYPE_SERIES),
            'mediaPermission' => Media::MEDIA_PERMISSION_ROLE,
            'seriesId' => $series->id,
            'mediaRoleIds' => [Role::ROLE_VISITOR_ID],
        ]));

        $this->assertEquals($series->name, $media->name);
        $this->assertEquals($series->description, $media->description);
        $this->assertTrue($media->isSeries());
        $this->assertFalse($media->isVideo());
        $this->assertEquals($series->id, $media->mediaable->id);
        $this->assertEquals(2, $media->childrenMedias->count());

        foreach ($media->childrenMedias as $child) {
            $this->assertEquals($media->id, $child->parent_media_id);
        }
    }
}
