<?php

use App\Dtos\ResourceCategoryDto;
use App\Dtos\ResourceTagDto;
use App\Dtos\ResourceVideoDto;
use App\Dtos\SeriesDto;
use App\Dtos\UploadFileDto;
use App\Dtos\VideoDto;
use App\Models\Actor;
use App\Models\Category;
use App\Models\File;
use App\Models\ResourceActor;
use App\Models\SeriesVideo;
use App\Models\Tag;
use App\Models\Video;
use App\Services\ResourceVideoService;
use App\Services\SeriesService;
use App\Services\VideoService;
use Tests\TestCase;

class SeriesServiceTest extends TestCase
{

    public function testUpdateOrCreateSeries(): void
    {
        $video1 = $this->createVideo();
        $video2 = $this->createVideo();

        /**
         * @var SeriesService $seriesService
         */
        $seriesService = app(SeriesService::class);

        $seriesName = $this->faker()->name;
        $seriesDescription = $this->faker()->text;

        $series = $seriesService->updateOrCreateSeries(new SeriesDto([
            'name' => $seriesName,
            'description' => $seriesDescription,
            'thumbnailFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
            'videoDtos' => [
                new VideoDto([
                    'videoId' => $video1->id,
                    'type' => Video::TYPE_CLOUD,
                ]),
                new VideoDto([
                    'videoId' => $video2->id,
                    'type' => Video::TYPE_CLOUD,
                ]),
            ],
        ]));

        $this->assertEquals($seriesName, $series->name);
        $this->assertEquals($seriesDescription, $series->description);
        $this->assertEquals(2, $series->videos()->count());

        $video3 = $this->createVideo();
        $seriesName = $this->faker()->name;
        $seriesDescription = $this->faker()->text;

        $series = $seriesService->updateOrCreateSeries(new SeriesDto([
            'seriesId' => $series->id,
            'name' => $seriesName,
            'description' => $seriesDescription,
            'thumbnailFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
            'videoDtos' => [
                new VideoDto([
                    'videoId' => $video1->id,
                    'type' => Video::TYPE_CLOUD,
                ]),
                new VideoDto([
                    'videoId' => $video3->id,
                    'type' => Video::TYPE_CLOUD,
                ]),
            ],
        ]));

        $this->assertEquals($seriesName, $series->name);
        $this->assertEquals($seriesDescription, $series->description);
        $this->assertEquals(2, $series->videos()->count());
        $this->assertEquals(1, SeriesVideo::query()
            ->where('series_id', '=', $series->id)
            ->where('video_id', '=', $video1->id)
            ->count());
        $this->assertEquals(1, SeriesVideo::query()
            ->where('series_id', '=', $series->id)
            ->where('video_id', '=', $video3->id)
            ->count());

    }

    public function testFetchOtherVideoIdsInSeries(): void {
        $video1 = $this->createVideo();
        $video2 = $this->createVideo();
        $video3 = $this->createVideo();
        $series = $this->createSeries([$video1->id, $video2->id, $video3->id]);

        /**
         * @var SeriesService $seriesService
         */
        $seriesService = app(SeriesService::class);
        $videoIds = $seriesService->fetchOtherVideoIdsInSeries($video1->id);
        $this->assertEquals([$video2->id, $video3->id], $videoIds);
    }

}
