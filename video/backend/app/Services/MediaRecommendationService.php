<?php

namespace App\Services;

use App\Models\Media;
use App\Models\MediaRecommendation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MediaRecommendationService
{
    /**
     * @param User|null $user
     * @return Builder
     */
    public function fetchAllMediasWithUserRecommendation(?User $user): Builder {
        if (empty($user)) {
            return Media::query()
                ->select('medias.*')
                ->join('media_recommendations', 'medias.id', '=', 'media_recommendations.media_id')
                ->where('media_recommendations.role_id', '=', Role::ROLE_VISITOR_ID)
                ->where('medias.status', '=', Media::STATUS_ACTIVE)
                ->whereNull('medias.deleted_at')
                ->inRandomOrder();
        } else {
            $topRoleId = null;
            foreach ($user->userRoles as $userRole) {
                $topRoleId = $userRole->role->id;
            }

            return Media::query()
                ->select('medias.*')
                ->distinct('medias.id')
                ->join('media_recommendations', 'medias.id', '=', 'media_recommendations.media_id')
                ->leftJoin('media_histories', function($join) use ($user) {
                    $join->on('media_histories.media_id', '=', 'medias.id')
                        ->where('media_histories.user_id', '=', $user->id);
                })
                ->whereNull('media_histories.id')
                ->where('media_recommendations.role_id', '=', $topRoleId)
                ->where('medias.status', '=', Media::STATUS_ACTIVE)
                ->whereNull('medias.deleted_at')
                ->inRandomOrder();
        }
    }

    /**
     * recommend videos for VISITOR
     * 2 visitor + 2 registration only + 1 other, order by media_id
     *
     * @return void
     */
    public function createMediaRecommendationForVisitor(): void
    {
        $visitorMediaIds = Media::query()
            ->select('medias.id')
            ->join('media_roles', 'medias.id', '=', 'media_roles.media_id')
            ->where('media_permission', '=', Media::MEDIA_PERMISSION_ROLE)
            ->where('media_roles.role_id', '=', Role::ROLE_VISITOR_ID)
            ->where('medias.status', '=', Media::STATUS_ACTIVE)
            ->groupBy('medias.id')
            ->orderBy('medias.id', 'desc')
            ->pluck('medias.id');

        $registrationMediaIds = Media::query()
            ->select('medias.id')
            ->join('media_roles', 'medias.id', '=', 'media_roles.media_id')
            ->where('media_permission', '=', Media::MEDIA_PERMISSION_ROLE)
            ->where('media_roles.role_id', '=', Role::ROLE_USER_ID)
            ->where('medias.status', '=', Media::STATUS_ACTIVE)
            ->groupBy('medias.id')
            ->orderBy('medias.id', 'desc')
            ->pluck('medias.id');
        $registrationMediaIds = $registrationMediaIds->reject(function ($id) use ($visitorMediaIds) {
            return $visitorMediaIds->contains($id);
        });

        $otherMediaIds = Media::query()
            ->where('medias.status', '=', Media::STATUS_ACTIVE)
            ->whereNotIn('id', $visitorMediaIds->merge($registrationMediaIds))
            ->groupBy('medias.id')
            ->orderBy('id', 'desc')
            ->pluck('id');

        $mediaIds = new Collection();
        while (!$visitorMediaIds->isEmpty()
            || !$registrationMediaIds->isEmpty()
            || !$otherMediaIds->isEmpty()) {

            // Add 2 visitor media IDs if available
            if ($visitorMediaIds->count() >= 2) {
                $mediaIds = $mediaIds->concat($visitorMediaIds->splice(0, 2));
            }

            // Add 2 registration media IDs if available
            if ($registrationMediaIds->count() >= 2) {
                $mediaIds = $mediaIds->concat($registrationMediaIds->splice(0, 2));
            }

            // Add 1 other media ID if available
            if ($otherMediaIds->count() >= 1) {
                $mediaIds = $mediaIds->concat($otherMediaIds->splice(0, 1));
            }

            if ($visitorMediaIds->count() < 2
                || $registrationMediaIds->count() < 2
                || $otherMediaIds->isEmpty()) {
                break;
            }
        }

        $mediaIds = $mediaIds->concat($visitorMediaIds);
        $mediaIds = $mediaIds->concat($registrationMediaIds);
        $mediaIds = $mediaIds->concat($otherMediaIds);

        DB::transaction(function () use ($mediaIds) {
            MediaRecommendation::query()
                ->where('role_id', '=', Role::ROLE_VISITOR_ID)
                ->delete();

            foreach ($mediaIds as $mediaId) {
                MediaRecommendation::create([
                    'media_id' => $mediaId,
                    'role_id' => Role::ROLE_VISITOR_ID
                ]);
            }
        });
    }

    /**
     * recommend videos for REGISTRATION users
     * 2 visitor or registration, 2 membership one order by media_id
     */
    public function createMediaRecommendationForRegistration(): void
    {
        $visitorOrRegistrationMediaIds = Media::query()
            ->select('medias.id')
            ->join('media_roles', 'medias.id', '=', 'media_roles.media_id')
            ->where('medias.status', '=', Media::STATUS_ACTIVE)
            ->where('media_permission', '=', Media::MEDIA_PERMISSION_ROLE)
            ->whereIn('media_roles.role_id', [Role::ROLE_VISITOR_ID, Role::ROLE_USER_ID])
            ->groupBy('medias.id')
            ->orderBy('medias.id', 'desc')
            ->pluck('medias.id');

        $membershipMediaIds = Media::query()
            ->select('medias.id')
            ->join('media_roles', 'medias.id', '=', 'media_roles.media_id')
            ->where('medias.status', '=', Media::STATUS_ACTIVE)
            ->where('media_permission', '=', Media::MEDIA_PERMISSION_ROLE)
            ->where('media_roles.role_id', '=', Role::ROLE_MEMBERSHIP_ID)
            ->groupBy('medias.id')
            ->orderBy('medias.id', 'desc')
            ->pluck('medias.id');
        $membershipMediaIds = $membershipMediaIds->reject(function ($id) use ($visitorOrRegistrationMediaIds) {
            return $visitorOrRegistrationMediaIds->contains($id);
        });

        $otherMediaIds = Media::query()
            ->where('medias.status', '=', Media::STATUS_ACTIVE)
            ->whereNotIn('id', $visitorOrRegistrationMediaIds->merge($membershipMediaIds))
            ->groupBy('medias.id')
            ->orderBy('id', 'desc')
            ->pluck('id');
;
        $mediaIds = new Collection();
        while (!$visitorOrRegistrationMediaIds->isEmpty()
            || !$membershipMediaIds->isEmpty()
            || !$otherMediaIds->isEmpty()) {

            // Add 2 visitor media IDs if available
            if ($visitorOrRegistrationMediaIds->count() >= 2) {
                $mediaIds = $mediaIds->concat($visitorOrRegistrationMediaIds->splice(0, 2));
            }

            // Add 2 registration media IDs if available
            if ($membershipMediaIds->count() >= 2) {
                $mediaIds = $mediaIds->concat($membershipMediaIds->splice(0, 2));
            }

            // Add 1 other media ID if available
            if ($otherMediaIds->count() >= 1) {
                $mediaIds = $mediaIds->concat($otherMediaIds->splice(0, 1));
            }

            if ($visitorOrRegistrationMediaIds->count() < 2
                || $membershipMediaIds->count() < 2
                || $otherMediaIds->isEmpty()) {
                break;
            }
        }

        $mediaIds = $mediaIds->concat($visitorOrRegistrationMediaIds);
        $mediaIds = $mediaIds->concat($membershipMediaIds);
        $mediaIds = $mediaIds->concat($otherMediaIds);

        DB::transaction(function () use ($mediaIds) {
            MediaRecommendation::query()
                ->where('role_id', '=', Role::ROLE_USER_ID)
                ->delete();

            foreach ($mediaIds as $mediaId) {
                MediaRecommendation::create([
                    'media_id' => $mediaId,
                    'role_id' => ROLE::ROLE_USER_ID
                ]);
            }
        });
    }

    /**
     * Recommend videos for MEMBERSHIP users
     * 3 visitor or register or membership + 1 product media + 1 subscription media
     */
    public function createMediaRecommendationForMembership(): void
    {
        $visitorOrRegistrationOrMembershipMediaIds = Media::query()
            ->select('medias.id')
            ->where('medias.status', '=', Media::STATUS_ACTIVE)
            ->join('media_roles', 'medias.id', '=', 'media_roles.media_id')
            ->where('media_permission', '=', Media::MEDIA_PERMISSION_ROLE)
            ->whereIn('media_roles.role_id', [Role::ROLE_VISITOR_ID, Role::ROLE_USER_ID, Role::ROLE_MEMBERSHIP_ID])
            ->groupBy('medias.id')
            ->orderBy('medias.id', 'desc')
            ->pluck('medias.id');

        $subscriptionOrProductMediaIds = Media::query()
            ->select('medias.id')
            ->where('medias.status', '=', Media::STATUS_ACTIVE)
            ->whereIn('media_permission', [Media::MEDIA_PERMISSION_SUBSCRIPTION, Media::MEDIA_PERMISSION_PURCHASE])
            ->groupBy('medias.id')
            ->orderBy('medias.id', 'desc')
            ->pluck('medias.id');

        $subscriptionOrProductMediaIds = $subscriptionOrProductMediaIds->reject(function ($id) use ($visitorOrRegistrationOrMembershipMediaIds) {
            return $visitorOrRegistrationOrMembershipMediaIds->contains($id);
        });


        $mediaIds = new Collection();
        while (!$visitorOrRegistrationOrMembershipMediaIds->isEmpty() && !$subscriptionOrProductMediaIds->isEmpty()) {
            // Add two visitor media IDs, if available
            if ($visitorOrRegistrationOrMembershipMediaIds->count() >= 3) {
                $mediaIds = $mediaIds->concat($visitorOrRegistrationOrMembershipMediaIds->splice(0, 3));
            }

            // Add two registration media IDs, if available
            if ($subscriptionOrProductMediaIds->count() >= 2) {
                $mediaIds = $mediaIds->concat($subscriptionOrProductMediaIds->splice(0, 2));
            }

            if ($visitorOrRegistrationOrMembershipMediaIds->count() < 3 || $subscriptionOrProductMediaIds->count() < 2) {
                break;
            }
        }

        $mediaIds = $mediaIds->concat($visitorOrRegistrationOrMembershipMediaIds);
        $mediaIds = $mediaIds->concat($subscriptionOrProductMediaIds);

        DB::transaction(function () use ($mediaIds) {
            MediaRecommendation::query()
                ->where('role_id', '=', Role::ROLE_MEMBERSHIP_ID)
                ->delete();

            foreach ($mediaIds as $mediaId) {
                MediaRecommendation::create([
                    'media_id' => $mediaId,
                    'role_id' => ROLE::ROLE_MEMBERSHIP_ID
                ]);
            }
        });
    }
}
