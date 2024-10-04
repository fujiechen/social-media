<?php

use App\Dtos\AlbumDto;
use App\Dtos\UploadFileDto;
use App\Models\Actor;
use App\Models\Album;
use App\Models\Category;
use App\Models\File;
use App\Models\Tag;
use App\Models\Video;
use App\Services\AlbumService;
use Tests\TestCase;

/**
 * update create album
 * - from upload & cloud
 */
class AlbumServiceTest extends TestCase
{

    public function testUpdateOrCreateAlbumFromUpload(): void
    {

        /**
         * @var AlbumService $albumService
         */
        $albumService = app(AlbumService::class);

        $tag1 = Tag::create(['name' => 'tag1']);
        $tag2 = Tag::create(['name' => 'tag2']);
        $tag3 = Tag::create(['name' => 'tag3']);

        $actor1 = Actor::create(['name' => 'actor1', 'country' => 'CN']);
        $category1 = Category::create(['name' => 'cat1']);

        $albumName = $this->faker()->name;
        $albumDescription = $this->faker()->text;

        //create
        $dto = new AlbumDto([
            'type' => Album::TYPE_UPLOAD,
            'name' => $albumName,
            'description' => $albumDescription,
            'thumbnailFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
            'imageFileDtos' => [new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ])],
            'tagIds' => [$tag1->id, $tag2->id, $tag3->id],
            'categoryIds' => [$category1->id],
            'actorIds' => [$actor1->id],
        ]);

        $album = $albumService->updateOrCreateAlbum($dto);

        $this->assertEquals($albumName, $album->name);
        $this->assertEquals($albumDescription, $album->description);
        $this->assertEquals(File::TYPE_PUBLIC_BUCKET, $album->images->first()->bucket_type);
        $this->assertEquals(3, $album->tags()->count());
        $this->assertEquals(1, $album->categories()->count());
        $this->assertEquals(1, $album->actors()->count());

        // update
        $tag4 = Tag::create(['name' => 'tag4']);
        $actor1->country = 'CA';
        $actor1->save();


        $albumName = $this->faker()->name;
        $albumDescription = $this->faker()->text;
        $dto = new AlbumDto([
            'albumId' => $album->id,
            'type' => Video::TYPE_UPLOAD,
            'name' => $albumName,
            'description' => $albumDescription,
            'imageFileDtos' => [new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ])],
            'tagIds' => [$tag1->id, $tag4->id],
            'categoryIds' => [],
            'actorIds' => [$actor1->id],
        ]);

        $album = $albumService->updateOrCreateAlbum($dto);

        $this->assertEquals($albumName, $album->name);
        $this->assertEquals($albumDescription, $album->description);
        $this->assertEquals(2, $album->tags()->count());
        $this->assertEquals(1, $album->tags()->where('name', '=', 'tag1')->count());
        $this->assertEquals(1, $album->tags()->where('name', '=', 'tag4')->count());
        $this->assertEquals(0, $album->categories()->count());
        $this->assertEquals(1, $album->actors()->count());
        $this->assertEquals(1, $album->actors()->where('name', '=', 'actor1')->count());
        $this->assertEquals(1, $album->actors()->where('country', '=', 'CA')->count());

    }
}
