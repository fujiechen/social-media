<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Actions\BatchUpdateMediaStatusFromDraftToReadyAction;
use App\Admin\Components\Tables\ActorTable;
use App\Admin\Components\Tables\AlbumTable;
use App\Admin\Components\Tables\CategoryTable;
use App\Admin\Components\Tables\SeriesTable;
use App\Admin\Components\Tables\TagTable;
use App\Admin\Components\Tables\UserTable;
use App\Admin\Components\Tables\VideoTable;
use App\Dtos\MediaDto;
use App\Models\Actor;
use App\Models\Album;
use App\Models\Category;
use App\Models\Media;
use App\Models\Role;
use App\Models\Series;
use App\Models\Tag;
use App\Models\User;
use App\Models\Video;
use App\Services\MediaService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Show;
use Illuminate\Support\Str;

class MediaController extends AdminController
{
    private MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Media::query(),
            function (Grid $grid) {
                $grid->disableRefreshButton();
                $grid->disableDeleteButton();
                $grid->disableBatchDelete();

                $grid->batchActions(function ($batch) {
                    $batch->add(new BatchUpdateMediaStatusFromDraftToReadyAction(admin_trans_label('change_draft_to_ready_title')));
                });

                $grid->quickSearch(['id', 'name', 'description']);

                $grid->selector(function (Grid\Tools\Selector $selector) {
                    $selector->select('mediaable_type', admin_trans_field('type'), [
                        Media::toMediaableType(Media::TYPE_VIDEO) => admin_trans_option('video', 'media_type'),
                        Media::toMediaableType(Media::TYPE_SERIES) => admin_trans_option('series', 'media_type'),
                        Media::toMediaableType(Media::TYPE_ALBUM) => admin_trans_option('album', 'media_type'),
                    ]);

                    $selector->select('media_permission', admin_trans_field('media_permission'), [
                        Media::MEDIA_PERMISSION_ROLE => admin_trans_option('role', 'media_permission'),
                        Media::MEDIA_PERMISSION_SUBSCRIPTION => admin_trans_option('subscription', 'media_permission'),
                        Media::MEDIA_PERMISSION_PURCHASE => admin_trans_option('purchase', 'media_permission'),
                    ]);

                    $selector->select('status', admin_trans_field('media_status'), [
                        Media::STATUS_DELETED => admin_trans_option('deleted', 'media_status'),
                        Media::STATUS_DRAFT => admin_trans_option('draft', 'media_status'),
                        Media::STATUS_READY => admin_trans_option('ready', 'media_status'),
                        Media::STATUS_ACTIVE => admin_trans_option('active', 'media_status'),
                    ]);

                    $selector->select('readyable', [
                        true => admin_trans_option('true','readyable'),
                        false => admin_trans_option('false','readyable'),
                    ]);
                });

                $grid->filter(function (Grid\Filter $filter) {
                    $filter->panel();

                    $filter->equal('user_id', admin_trans_field('user'))
                        ->multipleSelectTable(UserTable::make())
                        ->options(function ($v) {
                            if (!$v) {
                                return [];
                            }
                            return User::find($v)->pluck('nickname', 'id');
                        })
                        ->width(3);

                    $filter->equal('categories.id', admin_trans_field('category'))
                        ->multipleSelectTable(CategoryTable::make())
                        ->options(function ($v) {
                            if (!$v) {
                                return [];
                            }
                            return Category::find($v)->pluck('name', 'id');
                        })
                        ->width(3);

                    $filter->equal('tags.id', admin_trans_field('tag'))
                        ->multipleSelectTable(TagTable::make())
                        ->options(function ($v) {
                            if (!$v) {
                                return [];
                            }
                            return Tag::find($v)->pluck('name', 'id');
                        })
                        ->width(3);

                    $filter->equal('actors.id', admin_trans_field('actor'))
                        ->multipleSelectTable(ActorTable::make())
                        ->options(function ($v) {
                            if (!$v) {
                                return [];
                            }
                            return Actor::find($v)->pluck('name', 'id');
                        })
                        ->width(3);
                });

                $grid->column('id')->sortable();
                $grid->column('name')->width('15%')->sortable();
                $grid->column('readyable')->bool();
                $grid->column('status_name', admin_trans_field('media_status'))->display(function ($mediaStatus) {
                    switch ($mediaStatus) {
                        case 'Deleted':
                            return admin_trans_option('deleted', 'media_status');
                        case 'Draft':
                            return admin_trans_option('draft', 'media_status');
                        case 'Ready':
                            return admin_trans_option('ready', 'media_status');
                        case 'Active':
                            return admin_trans_option('active', 'media_status');
                    }
                    return '';
                })->label('warning');
                $grid->column('user.nickname', admin_trans_field('user'))->sortable('user_id');
                $grid->column('media_permission_string', admin_trans_field('media_permission'))->label();
                $grid->column('type')->sortable('mediaable_type')->display(function ($type) {
                    switch ($type) {
                        case Media::TYPE_VIDEO:
                            return admin_trans_option('video', 'media_type');
                        case Media::TYPE_ALBUM:
                            return admin_trans_option('album', 'media_type');
                        case Media::TYPE_SERIES:
                            return admin_trans_option('series', 'media_type');
                    }
                    return '';
                });
                $grid->column('parent_media_id', admin_trans_field('parent_media'))->sortable('parent_media_id');
                $grid->column('mediaable', admin_trans_field('library'))->display(function($lib) {
                    if (get_class($lib) == Video::class ) {
                        return '<a target="_blank" href="/admin/video/' . $lib->id . '/edit">'. admin_trans_field('video').':'. $lib->id . '</a> <br/>';
                    }

                    if (get_class($lib) == Album::class ) {
                        return '<a target="_blank" href="/admin/album/' . $lib->id . '/edit">'. admin_trans_field('album').': '. $lib->id . '</a> <br/>';
                    }

                    if (get_class($lib) == Series::class ) {
                        return '<a target="_blank" href="/admin/series/' . $lib->id . '/edit">'. admin_trans_field('series').': '. $lib->id . '</a> <br/>';
                    }

                });
                $grid->column('tag_names', admin_trans_field('tag'))->label();
                $grid->column('actor_names', admin_trans_field('actor'))->label();
                $grid->column('category_names', admin_trans_field('category'))->label();
                $grid->column('views_count')->sortable();
                $grid->column('created_at_formatted');
            });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id): Show
    {
        return Show::make($id, Media::query(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('created_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(Media::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');

                $form->selectTable('user_id', admin_trans_field('user'))
                    ->title(admin_trans_label('select_user'))
                    ->from(UserTable::make())
                    ->model(User::class, 'id', 'username')
                    ->required();

                $form->text('name')->help(admin_trans_label('copy_from_library'));
                $form->textarea('description')->help(admin_trans_label('copy_from_library'));

                $form->radio('status', admin_trans_field('media_status'))->options([
                    Media::STATUS_DELETED => admin_trans_option('deleted', 'media_status'),
                    Media::STATUS_DRAFT => admin_trans_option('draft', 'media_status'),
                    Media::STATUS_READY => admin_trans_option('ready', 'media_status'),
                    Media::STATUS_ACTIVE => admin_trans_option('active', 'media_status'),
                ])->default(Media::STATUS_DRAFT)->required();

                $form->radio('type')
                    ->required()
                    ->options([
                        Media::TYPE_VIDEO => admin_trans_option('video', 'media_type'),
                        Media::TYPE_SERIES => admin_trans_option('series', 'media_type'),
                        Media::TYPE_ALBUM => admin_trans_option('album', 'media_type'),
                    ])
                    ->default(Media::TYPE_VIDEO)
                    ->when([Media::TYPE_VIDEO], function (Form $form) {
                        $form->selectTable('video_id', admin_trans_field('video'))
                            ->from(VideoTable::make())
                            ->model(Video::class, 'id', 'name');
                    })
                    ->when([Media::TYPE_SERIES], function (Form $form) {
                        $form->selectTable('series_id', admin_trans_field('series'))
                            ->from(SeriesTable::make())
                            ->model(Series::class, 'id', 'name');
                    })
                    ->when([Media::TYPE_ALBUM], function (Form $form) {
                        $form->selectTable('album_id', admin_trans_field('album'))
                            ->from(AlbumTable::make())
                            ->model(Album::class, 'id', 'name');
                    });

                $form->checkbox('permissions', admin_trans_field('media_permission'))
                    ->required()
                    ->options([
                        Media::MEDIA_PERMISSION_ROLE => admin_trans_option('role', 'media_permission'),
                        Media::MEDIA_PERMISSION_SUBSCRIPTION => admin_trans_option('subscription', 'media_permission'),
                        Media::MEDIA_PERMISSION_PURCHASE => admin_trans_option('purchase', 'media_permission'),
                    ])
                    ->default(Media::MEDIA_PERMISSION_ROLE)
                    ->when(Media::MEDIA_PERMISSION_ROLE, function(Form $form) {
                        $form->checkbox('role_ids', admin_trans_field('roles'))
                            ->options([
                                Role::ROLE_VISITOR_ID => admin_trans_option('visitor', 'roles'),
                                Role::ROLE_USER_ID => admin_trans_option('user', 'roles'),
                                Role::ROLE_MEMBERSHIP_ID => admin_trans_option('membership', 'roles'),
                            ]);
                    })
                    ->when(Media::MEDIA_PERMISSION_SUBSCRIPTION, function(Form $form) {
                    })
                    ->when(Media::MEDIA_PERMISSION_PURCHASE, function(Form $form) {
                        $form->text('media_product_price', admin_trans_field('media_product_price'));
                        $form->select('media_product_currency_name', admin_trans_field('media_product_currency'))
                            ->options([
                                env('CURRENCY_CASH') => env('CURRENCY_CASH'),
                                env('CURRENCY_POINTS') => env('CURRENCY_POINTS'),
                            ]);
                    });

                if ($form->isEditing()) {
                    $form->html('<hr/>');
                    $form->html('<b>'. admin_trans_label('update_edit_page').'</b>');
                    $form->table('tags', admin_trans_label('tag_readonly'), function(Form\NestedForm $form) {
                        $form->display('name');
                    })->disableDelete()->disableCreate();

                    $form->table('actors', admin_trans_label('actor_readonly'), function(Form\NestedForm $form) {
                        $form->display('name');
                        $form->display('country');
                    })->disableDelete()->disableCreate();

                    $form->table('categories', admin_trans_label('category_readonly'), function(Form\NestedForm $form) {
                        $form->display('name');
                    })->disableDelete()->disableCreate();
                }
            });
    }

    private function save(): JsonResponse {
        $roleIds = [];
        foreach (request()->input('role_ids') as $roleId) {
            if (!empty($roleId)) {
                $roleIds[] = $roleId;
            }
        }

        $permissions = [];
        foreach (request()->input('permissions') as $permission) {
            if (isset($meta['_remove_']) && $meta['_remove_'] == 1) {
                continue;
            }
            if ($permission) {
                $permissions[] = $permission;
            }
        }

        $dto = new MediaDto([
            'mediaId' => request()->input('id') ? request()->input('id') : 0,
            'userId' => request()->input('user_id'),
            'mediaableType' => Media::toMediaableType(request()->input('type')),
            'videoId' => request()->input('video_id') ? request()->input('video_id') : 0,
            'seriesId' => request()->input('series_id') ? request()->input('series_id') : 0,
            'albumId' => request()->input('album_id') ? request()->input('album_id') : 0,
            'name' => request()->input('name'),
            'description' => request()->input('description'),
            'mediaRoleIds' => $roleIds,
            'mediaPermissions' => $permissions,
            'mediaProductPrice' => request()->input('media_product_price'),
            'mediaProductCurrencyName' => request()->input('media_product_currency_name'),
            'status' => request()->input('status'),
        ]);

        $media = $this->mediaService->updateOrCreateMedia($dto);

        return $this->form()
            ->response()
            ->redirect('media/' . $media->id . '/edit')
            ->success(trans('admin.save_succeeded'));
    }

    public function store()
    {
        return $this->save();
    }

    public function update($id)
    {
        $this->save();
    }

    public function title(): string
    {
        return admin_trans_label('media');
    }

    public function routeName(): string
    {
        return 'media';
    }
}
