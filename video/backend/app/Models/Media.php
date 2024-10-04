<?php

namespace App\Models;

use App\Events\MediaDeletedEvent;
use App\Events\MediaSavedEvent;
use App\Models\Traits\HasCreatedAt;
use App\Models\Traits\HasUpdatedAt;
use App\Models\Traits\HasUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property int $user_id
 * @property string $description
 * @property Video|Series|Album $mediaable
 * @property string $mediaable_type
 * @property int $mediaable_id
 * @property ?int $parent_media_id
 * @property Carbon deleted_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Role[]|Collection $roles
 * @property User $user
 * @property string $media_permission
 * @property Collection $role_ids
 * @property string $type
 * @property ?string $search_text
 * @property Tag[]|Collection $tags
 * @property Category[]|Collection $categories
 * @property Actor[]|Collection $actors
 * @property ?Media $parentMedia
 * @property Media[]|Collection $childrenMedias
 * @property Media[]|Collection $mediaProducts
 * @property User[]|Collection $likeUsers
 * @property User[]|Collection $favoriteUsers
 * @property MediaComment[]|Collection $mediaComments
 * @property MediaLike[]|Collection $mediaLikes
 * @property MediaFavorite[]|Collection $mediaFavorites
 * @property bool $registration_redirect
 * @property bool $product_redirect
 * @property int $views_count
 * @property int $status
 * @property ?bool $readyable
 * @property int $favorites_count
 * @property int $comments_count
 * @property int $likes_count
 * @property int $children_count
 * @property MediaPermission[]|Collection $mediaPermissions
 * @property Collection $permissions
 */
class Media extends Model
{
    use HasUser;
    use SoftDeletes;
    use HasCreatedAt;
    use HasUpdatedAt;

    public $table = 'medias';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'mediaable_type',
        'mediaable_id',
        'media_permission',
        'search_text',
        'parent_media_id',
        'deleted_at',
        'status',
        'created_at',
        'updated_at',
        'views_count',
        'favorites_count',
        'comments_count',
        'likes_count',
        'children_count',
        'readyable',
    ];

    protected $with = [
        'roles',
        'user',
        'mediaPermissions',
        'parentMedia',
        'mediaVideoProducts',
        'mediaSeriesProducts',
    ];

    protected $appends = [
        'created_at_formatted',
        'updated_at_formatted',
        'role_ids',
        'role_names',
        'type',
        'video_id',
        'series_id',
        'album_id',
        'media_product_price',
        'media_product_currency_name',
        'media_permission_string',
        'tag_names',
        'actor_names',
        'category_names',
        'status_name',
        'permissions'
    ];

    const TYPE_SERIES = 'Series';
    const TYPE_VIDEO = 'Video';
    const TYPE_ALBUM = 'Album';

    const MEDIA_PERMISSION_ROLE = 'role';
    const MEDIA_PERMISSION_SUBSCRIPTION = 'subscription';
    const MEDIA_PERMISSION_PURCHASE = 'purchase';

    const MEDIA_REDIRECT_REGISTRATION = 'registration';
    const MEDIA_REDIRECT_PRODUCT = 'product';
    const MEDIA_REDIRECT_SUBSCRIPTION = 'subscription';
    const MEDIA_REDIRECT_MEMBERSHIP = 'membership';

    const STATUS_DELETED = 0;
    const STATUS_DRAFT = 1;
    const STATUS_READY = 2;
    const STATUS_ACTIVE = 3;

    protected $dispatchesEvents = [
        'saved' => MediaSavedEvent::class,
        'deleted' => MediaDeletedEvent::class,
    ];


    public static function toMediaableType(string $type):string {
        return match ($type) {
            self::TYPE_VIDEO => Video::class,
            self::TYPE_SERIES => Series::class,
            self::TYPE_ALBUM => Album::class,
        };
    }

    public static function toType(string $mediaableType):string {
        return match ($mediaableType) {
            Video::class => self::TYPE_VIDEO,
            Series::class => self::TYPE_SERIES,
            Album::class => self::TYPE_ALBUM,
        };
    }

    public function getTypeAttribute(): string {
        return self::toType($this->mediaable_type);
    }

    public function roles(): HasManyThrough {
        return $this->hasManyThrough(Role::class, MediaRole::class,
            'media_id', 'id', 'id', 'role_id');
    }

    public function mediaPermissions(): HasMany {
        return $this->hasMany(MediaPermission::class);
    }

    public function mediaVideoProducts(): HasMany {
        return $this->hasMany(Product::class, 'media_id', 'id');
    }

    public function mediaSeriesProducts(): HasMany {
        return $this->hasMany(Product::class, 'media_id', 'parent_media_id');
    }

    public function mediaProduct(): ?Product {
        if ($this->parent_media_id) {
            return $this->mediaSeriesProducts()->first();
        }

        return $this->mediaVideoProducts()->first();
    }

    public function getMediaProductPriceAttribute(): ?float {
        if ($this->mediaProduct()) {
            return $this->mediaProduct()->unit_cents / 100;
        }
        return null;
    }

    public function getMediaProductCurrencyNameAttribute(): ?string {
        if ($this->mediaProduct()) {
            return $this->mediaProduct()->currency_name;
        }
        return null;
    }

    public function getRoleIdsAttribute(): Collection {
        return $this->roles->pluck('id');
    }

    public function getRoleNamesAttribute(): Collection {
        return $this->roles->pluck('name');
    }

    public function mediaable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isSeries():bool {
        return $this->mediaable_type === Series::class;
    }

    public function isVideo():bool {
        return $this->mediaable_type === Video::class;
    }

    public function isAlbum():bool {
        return $this->mediaable_type === Album::class;
    }

    public function getVideoIdAttribute(): ?int {
        if ($this->isVideo()) {
            return $this->mediaable_id;
        }
        return null;
    }

    public function getSeriesIdAttribute(): ?int {
        if ($this->isSeries()) {
            return $this->mediaable_id;
        }
        return null;
    }

    public function getAlbumIdAttribute(): ?int {
        if ($this->isAlbum()) {
            return $this->mediaable_id;
        }
        return null;
    }

    public function getMediaPermissionStringAttribute(): array {
        $permissions = [];
        if ($this->mediaPermissions->pluck('permission')->contains(self::MEDIA_PERMISSION_ROLE)) {
            $permissions[] = admin_trans_label('required_roles').': ' . \implode( admin_trans_label('permission_or') , $this->getRoleNamesAttribute()->toArray());
        }

        if ($this->mediaPermissions->pluck('permission')->contains(self::MEDIA_PERMISSION_SUBSCRIPTION)) {
            $items = [];
            foreach ($this->user->subscriptionProducts as $subscriptionProduct) {
                $frequencyString = admin_trans_option($subscriptionProduct->frequency, 'subscription_frequency');
                $items[] = $frequencyString . '=> ' . $subscriptionProduct->unit_price  . ' (' .  $subscriptionProduct->currency_name . ')';;
            }

            $permissions[] = admin_trans_label('required_subscription').': ' . \implode(admin_trans_label('permission_or')  , $items);
        }

        if ($this->mediaPermissions->pluck('permission')->contains(self::MEDIA_PERMISSION_PURCHASE)) {
            $permissions[] = admin_trans_label('required_purchase').': ' . $this->mediaProduct()->unit_price . ' (' .  $this->mediaProduct()->currency_name . ')';
        }

        return $permissions;
    }

    public function getPermissionsAttribute(): Collection {
        return $this->mediaPermissions->pluck('permission');
    }

    public function mediaTags(): HasMany {
        return $this->hasMany(MediaTag::class);
    }

    public function mediaActors(): HasMany {
        return $this->hasMany(MediaActor::class);
    }

    public function mediaCategories(): HasMany {
        return $this->hasMany(MediaCategory::class);
    }

    public function tags(): HasManyThrough
    {
        return $this->hasManyThrough(Tag::class, MediaTag::class,
            'media_id', 'id', 'id', 'tag_id');
    }

    public function categories(): HasManyThrough
    {
        return $this->hasManyThrough(Category::class, MediaCategory::class,
            'media_id', 'id', 'id', 'category_id');
    }

    public function actors(): HasManyThrough
    {
        return $this->hasManyThrough(Actor::class, MediaActor::class,
            'media_id', 'id', 'id', 'actor_id');
    }

    public function getTagNamesAttribute(): Collection {
        return $this->tags->pluck('name');
    }

    public function getCategoryNamesAttribute(): Collection {
        return $this->categories->pluck('name');
    }

    public function getActorNamesAttribute(): Collection {
        return $this->actors->pluck('name');
    }

    public function parentMedia(): BelongsTo {
        return $this->belongsTo(Media::class, 'parent_media_id');
    }

    public function childrenMedias(): HasMany {
        return $this->hasMany(Media::class, 'parent_media_id');
    }

    public function likeUsers(): HasManyThrough {
        return $this->hasManyThrough(User::class, MediaLike::class,
            'media_id', 'id', 'id', 'user_id');
    }

    public function favoriteUsers(): HasManyThrough {
        return $this->hasManyThrough(User::class, MediaFavorite::class,
            'media_id', 'id', 'id', 'user_id');
    }

    public function mediaComments(): HasMany {
        return $this->hasMany(MediaComment::class);
    }

    public function mediaLikes(): HasMany {
        return $this->hasMany(MediaLike::class);
    }

    public function mediaFavorites(): HasMany {
        return $this->hasMany(MediaFavorite::class);
    }

    public function getThumbnailImage(): ?File {
        return $this->mediaable?->thumbnailFile;
    }

    public function getStatusNameAttribute(): string {
        return match ($this->status) {
            self::STATUS_DELETED => 'Deleted',
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_READY => 'Ready',
            self::STATUS_ACTIVE => 'Active',
        };
    }
}
