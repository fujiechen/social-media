<?php

use App\Dtos\MediaDto;
use App\Dtos\UploadFileDto;
use App\Dtos\VideoDto;
use App\Models\File;
use App\Models\Media;
use App\Models\MediaRecommendation;
use App\Models\Role;
use App\Models\Video;
use App\Services\MediaRecommendationService;
use App\Services\MediaService;
use App\Services\VideoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaRecommendationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateMediaRecommendationForVisitor() {
        /**
         * @var VideoService $videoService
         */
        $videoService = app(VideoService::class);

        $video = $videoService->updateOrCreateVideo(new VideoDto([
            'type' => Video::TYPE_UPLOAD,
            'name' => $this->faker()->name,
            'description' => $this->faker()->text,
            'thumbnailFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
            'previewFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
            'videoFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PRIVATE_BUCKET,
                'uploadPath' => '',
            ]),
        ]));

        /**
         * @var MediaService $mediaService
         */
        $mediaService = app(MediaService::class);

        $media1 = $mediaService->updateOrCreateMedia(new MediaDto([
            'userId' => $this->adminUser()->id,
            'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
            'mediaPermission' => Media::MEDIA_PERMISSION_ROLE,
            'videoId' => $video->id,
            'mediaRoleIds' => [Role::ROLE_USER_ID],
        ]));

        $media2 = $mediaService->updateOrCreateMedia(new MediaDto([
            'userId' => $this->adminUser()->id,
            'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
            'mediaPermission' => Media::MEDIA_PERMISSION_ROLE,
            'videoId' => $video->id,
            'mediaRoleIds' => [Role::ROLE_USER_ID],
        ]));

        $media3 = $mediaService->updateOrCreateMedia(new MediaDto([
            'userId' => $this->adminUser()->id,
            'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
            'mediaPermission' => Media::MEDIA_PERMISSION_ROLE,
            'videoId' => $video->id,
            'mediaRoleIds' => [Role::ROLE_VISITOR_ID],
        ]));

        $media4 = $mediaService->updateOrCreateMedia(new MediaDto([
            'userId' => $this->adminUser()->id,
            'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
            'mediaPermission' => Media::MEDIA_PERMISSION_ROLE,
            'videoId' => $video->id,
            'mediaRoleIds' => [Role::ROLE_VISITOR_ID],
        ]));

        $media5 = $mediaService->updateOrCreateMedia(new MediaDto([
            'userId' => $this->adminUser()->id,
            'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
            'mediaPermission' => Media::MEDIA_PERMISSION_ROLE,
            'videoId' => $video->id,
            'mediaRoleIds' => [Role::ROLE_USER_ID],
        ]));

        $media6 = $mediaService->updateOrCreateMedia(new MediaDto([
            'userId' => $this->adminUser()->id,
            'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
            'mediaPermission' => Media::MEDIA_PERMISSION_SUBSCRIPTION,
            'videoId' => $video->id,
        ]));

        $media7 = $mediaService->updateOrCreateMedia(new MediaDto([
            'userId' => $this->adminUser()->id,
            'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
            'mediaPermission' => Media::MEDIA_PERMISSION_ROLE,
            'videoId' => $video->id,
            'mediaRoleIds' => [Role::ROLE_MEMBERSHIP_ID],
        ]));

        $media8 = $mediaService->updateOrCreateMedia(new MediaDto([
            'userId' => $this->adminUser()->id,
            'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
            'mediaPermission' => Media::MEDIA_PERMISSION_ROLE,
            'videoId' => $video->id,
            'mediaRoleIds' => [Role::ROLE_MEMBERSHIP_ID],
        ]));

        $media9 = $mediaService->updateOrCreateMedia(new MediaDto([
            'userId' => $this->adminUser()->id,
            'mediaableType' => Media::toMediaableType(Media::TYPE_VIDEO),
            'mediaPermission' => Media::MEDIA_PERMISSION_SUBSCRIPTION,
            'videoId' => $video->id,
        ]));

        /**
         * @var MediaRecommendationService $mediaRecommendationService
         */
        $mediaRecommendationService = app(MediaRecommendationService::class);
        $mediaRecommendationService->createMediaRecommendationForVisitor();
        $mediaRecommendationService->createMediaRecommendationForVisitor();


        /**
         * 1 - registration
         * 2 - registration
         * 3 - visitor
         * 4 - visitor
         * 5 - registration
         * 6 - subscription
         * 7 - membership
         * 8 - membership
         * 9 - subscription
         */

        /**
         * Visitor
         * skip the subscription media, so 4 + 1 = 5
         */
        $this->assertEquals(9, MediaRecommendation::query()
            ->where('role_id', ROLE::ROLE_VISITOR_ID)
            ->count());
        $mediaIds = MediaRecommendation::query()
            ->where('role_id', ROLE::ROLE_VISITOR_ID)
            ->pluck('media_id')
            ->toArray();
        $this->assertEquals([4,3,5,2,9,1,8,7,6], $mediaIds);

        /**
         * Registration
         */
        $mediaRecommendationService->createMediaRecommendationForRegistration();
        $mediaRecommendationService->createMediaRecommendationForRegistration();
        $this->assertEquals(9, MediaRecommendation::query()
            ->where('role_id', ROLE::ROLE_USER_ID)
            ->count());
        $mediaIds = MediaRecommendation::query()
            ->where('role_id', ROLE::ROLE_USER_ID)
            ->pluck('media_id')
            ->toArray();
        $this->assertEquals([5,4,8,7,9,3,2,1,6], $mediaIds);

        $mediaRecommendationService->createMediaRecommendationForMembership();
        $mediaRecommendationService->createMediaRecommendationForMembership();
        $this->assertEquals(9, MediaRecommendation::query()
            ->where('role_id', ROLE::ROLE_MEMBERSHIP_ID)
            ->count());
    }
}
