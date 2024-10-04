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

    }
}
