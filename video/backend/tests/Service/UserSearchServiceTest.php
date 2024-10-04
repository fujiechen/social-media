<?php

use App\Services\UserSearchService;
use Tests\TestCase;

class UserSearchServiceTest extends TestCase
{
    public function testGetHotUserSearchesQuery(): void {
        $visitor1 = $this->createVisitor();
        $visitor2 = $this->createVisitor();
        $visitor3 = $this->createVisitor();

        $search1 = $this->faker()->text;
        $search2 = $this->faker()->text;
        $search3 = $this->faker()->text;

        /**
         * @var UserSearchService $userSearchService
         */
        $userSearchService = app(UserSearchService::class);
        $userSearchService->updateOrCreateUserSearch($visitor1->id, $search1);
        $userSearchService->updateOrCreateUserSearch($visitor2->id, $search1);
        $userSearchService->updateOrCreateUserSearch($visitor3->id, $search1);

        $userSearchService->updateOrCreateUserSearch($visitor2->id, $search2);
        $userSearchService->updateOrCreateUserSearch($visitor3->id, $search2);

        $userSearchService->updateOrCreateUserSearch($visitor3->id, $search3);

        $userSearches = $userSearchService->getHotUserSearchesQuery()->get();
        $this->assertEquals(3, $userSearches->count());
        $this->assertEquals($search1, $userSearches->first()->search);
        $this->assertEquals($search3, $userSearches->last()->search);
    }
}
