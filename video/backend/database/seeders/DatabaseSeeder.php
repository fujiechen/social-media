<?php

namespace Database\Seeders;

use App\Models\File;
use App\Models\Media;
use App\Models\MediaRole;
use App\Models\Resource;
use App\Models\ResourceVideo;
use App\Models\Role;
use App\Models\Series;
use App\Models\SeriesVideo;
use App\Models\Video;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;

class DatabaseSeeder extends Seeder
{
    private Faker $faker;

    public function __construct(Faker $faker) {
        $this->faker = $faker;
    }

    public function run()
    {
        $resource = Resource::create([
            'name' => 'resource 1',
        ]);


        //create videos
        for ($i = 0; $i < 50; $i++) {
            $video = Video::create([
                'name' => 'Video ' . $this->faker->name,
                'description' => $this->faker->text,
            ]);

            $file = File::create([
                'name' => $this->faker->name,
                'path' => $this->faker->url,
            ]);

            ResourceVideo::create([
                'name' => $this->faker->name,
                'description' => $this->faker->text,
                'resource_video_url' => $this->faker->url,
                'file_id' => $file->id,
                'video_id' => $video->id,
                'resource_id' => $resource->id,
            ]);
        }

        foreach(Video::all() as $i => $video) {
            if ($i % 3) {
                $media = Media::create([
                    'name' => $video->name,
                    'description' => $video->description,
                    'mediaable_type' => Video::class,
                    'mediaable_id' => $video->id,
                ]);
            } else {
                $series = Series::create([
                   'name' => 'Series ' . $this->faker->name,
                   'description' => $this->faker->text,
                ]);

                SeriesVideo::create([
                   'series_id' => $series->id,
                   'video_id' => $video->id,
                ]);

                $media = Media::create([
                    'name' => $video->name,
                    'description' => $video->description,
                    'mediaable_type' => Series::class,
                    'mediaable_id' => $series->id,
                ]);
            }

            MediaRole::create([
                'media_id' => $media->id,
                'role_id' => Role::ROLE_USER_ID,
            ]);
        }
    }
}
