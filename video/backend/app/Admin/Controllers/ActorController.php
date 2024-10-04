<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\PublicFileTable;
use App\Admin\Components\Tables\ResourceActorTable;
use App\Admin\Components\Tables\ResourceCategoryTable;
use App\Dtos\ActorDto;
use App\Dtos\BucketFileDto;
use App\Dtos\UploadFileDto;
use App\Models\Actor;
use App\Models\File;
use App\Models\ResourceActor;
use App\Models\ResourceCategory;
use App\Services\ActorService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class ActorController extends AdminController
{

    private ActorService $actorService;

    public function __construct(ActorService $actorService) {
        $this->actorService = $actorService;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Actor::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();

                $grid->filter(function (Grid\Filter $filter) {
                    $filter->panel();

                    $filter->equal('resourceActors.id', admin_trans_field('resource_actor_ids'))
                        ->multipleSelectTable(ResourceActorTable::make())
                        ->options(function ($v) {
                            if (!$v) {
                                return [];
                            }
                            return ResourceActor::find($v)->pluck('name', 'id');
                        })
                        ->width(6);
                });

                $grid->quickSearch(['name', 'country']);

                $grid->column('id')->sortable();
                $grid->column('name')->sortable();
                $grid->column('country')->sortable();
                $grid->column('resource_actor_names')->label();
                $grid->column('priority')->sortable();
                $grid->column('views_count');
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
        return Show::make($id, Actor::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('name');
                $show->field('description');
                $show->field('priority');
                $show->field('views_count');
                $show->field('resource_actor_names')->label();
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
        return Form::make(Actor::query()->with(['resourceActors']),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');
                $form->text('name')->required();
                $form->textarea('description');
                $form->number('priority');
                $form->text('country')->required();

                $form->multipleSelectTable('resource_actor_ids')
                    ->title(admin_trans_label('select_multiple_resource_actors'))
                    ->from(ResourceActorTable::make())
                    ->model(ResourceActor::class, 'id', 'name');

                $options = [
                    Actor::TYPE_CLOUD => admin_trans_option('Cloud','type'),
                    Actor::TYPE_UPLOAD => admin_trans_option('Upload','type'),
                ];

                if ($form->isEditing()) {
                    unset($options[Actor::TYPE_UPLOAD]);
                }

                $form->radio('type')
                    ->required()
                    ->options($options)
                    ->default(Actor::TYPE_UPLOAD)
                    ->when([Actor::TYPE_CLOUD], function (Form $form) {
                        $form->selectTable('avatar_file_id')
                            ->title(admin_trans_label('please_select_a_thumbnail_file'))
                            ->from(PublicFileTable::make())
                            ->model(File::class, 'id', 'name')
                            ->required();
                    })->when([Actor::TYPE_UPLOAD], function (Form $form) {
                        $form->image('avatar_file_path')
                            ->url('ajax/upload')
                            ->autoUpload()
                            ->removable()
                            ->required();
                    });
            });
    }

    private function save() {
        $avatarFileDto = null;
        if (request()->input('type') == Actor::TYPE_UPLOAD) {
            $avatarFileDto = new UploadFileDto([
                'fileId' => 0,
                'uploadPath' => request()->input('avatar_file_path'),
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
            ]);
        } else if (request()->input('type') == Actor::TYPE_CLOUD) {
            $avatarFileDto = new BucketFileDto(['fileId' => request()->input('avatar_file_id'), 'bucketType' => File::TYPE_PUBLIC_BUCKET]);
        }

        $dto = new ActorDto([
            'type' => request()->input('type'),
            'actorId' => request()->input('id') ? request()->input('id') : 0,
            'name' => request()->input('name', ''),
            'description' => request()->input('description', ''),
            'priority' => request()->input('priority', 0),
            'country' => request()->input('country', ''),
            'resourceActorIds' => request()->input('resource_actor_ids') ? explode(',', request()->input('resource_actor_ids')) : [],
            'avatarFileDto' => $avatarFileDto,
        ]);

        $this->actorService->updateOrCreateActor($dto);

        return $this->form()
            ->response()
            ->redirect('actor/')
            ->success(trans('admin.save_succeeded'));
    }

    public function store()
    {
        return $this->save();
    }

    public function update($id)
    {
        return $this->save();
    }


    public function title(): string
    {
        return admin_trans_label('actors');
    }

    public function routeName(): string
    {
        return 'actor';
    }
}
