<?php

use App\Dtos\ActorDto;
use App\Dtos\UploadFileDto;
use App\Models\Actor;
use App\Models\File;
use App\Models\ResourceActor;
use App\Services\ActorService;
use Tests\TestCase;

class ActorServiceTest extends TestCase
{
    public function testUpdateOrCreateActorWithoutResourceActors(): void {
        $dto = new ActorDto([
            'name' => 'test actor',
            'type' => Actor::TYPE_UPLOAD,
            'avatarFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
        ]);

        /**
         * @var ActorService $actorService
         */
        $actorService = app(ActorService::class);
        $actor = $actorService->updateOrCreateActor($dto);
        $this->assertEquals('test actor', $actor->name);

        $actorName1 = $this->faker()->name;
        $dto = new ActorDto([
            'name' => $actorName1,
            'type' => Actor::TYPE_UPLOAD,
            'avatarFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
        ]);

        $actor = $actorService->updateOrCreateActor($dto);

        $this->assertEquals($actorName1, $actor->name);
        $this->assertEquals(1,
            Actor::query()->where('name', '=', $actorName1)->count());


        $actorName2 = $this->faker()->name;
        $dto = new ActorDto([
            'actorId' => $actor->id,
            'name' => $actorName2,
            'type' => Actor::TYPE_UPLOAD,
            'avatarFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
        ]);

        $actor = $actorService->updateOrCreateActor($dto);
        $this->assertEquals($actorName2, $actor->name);
        $this->assertEquals(0,
            Actor::query()->where('name', '=', $actorName1)->count());
    }

    public function testUpdateOrCreateActorWithResourceActors(): void {
        /**
         * @var ActorService $actorService
         */
        $actorService = app(ActorService::class);

        $resourceActor1 = ResourceActor::create(['name' => 'resource actor1']);
        $resourceActor2 = ResourceActor::create(['name' => 'resource actor2']);

        //create
        $actor = $actorService->updateOrCreateActor(new ActorDto([
            'name' => $this->faker()->name,
            'resourceActorIds' => [$resourceActor1->id, $resourceActor2->id],
            'type' => Actor::TYPE_UPLOAD,
            'avatarFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
        ]));

        $resourceActor1->refresh();
        $this->assertEquals($actor->id, $resourceActor1->actor_id);

        $resourceActor2->refresh();
        $this->assertEquals($actor->id, $resourceActor2->actor_id);

        $this->assertEquals(2, $actor->resourceActors()->count());

        //update
        $actor = $actorService->updateOrCreateActor(new ActorDto([
            'actorId' => $actor->id,
            'name' => $this->faker()->name,
            'resourceActorIds' => [$resourceActor1->id],
            'type' => Actor::TYPE_UPLOAD,
            'avatarFileDto' => new UploadFileDto([
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
                'uploadPath' => '',
            ]),
        ]));

        $this->assertEquals(1, $actor->resourceActors()->count());

        $resourceActor1->refresh();
        $this->assertEquals($actor->id, $resourceActor1->actor_id);

        $resourceActor2->refresh();
        $this->assertNull($resourceActor2->actor_id);
    }
}
