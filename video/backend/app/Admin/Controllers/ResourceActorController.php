<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\ActorTable;
use App\Admin\Components\Tables\ResourceTable;
use App\Events\ResourceActorAddActorEvent;
use App\Events\ResourceActorRemoveActorEvent;
use App\Models\Actor;
use App\Models\Resource;
use App\Models\ResourceActor;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class ResourceActorController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(ResourceActor::query()->with(['resource', 'actor']),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();

                $grid->quickCreate(function (Grid\Tools\QuickCreate $create) {
                    $create->select('resource_id', admin_trans_field('resource'))
                        ->options(Resource::query()->pluck('name', 'id')->toArray());
                    $create->text('name');
                    $create->text('country');
                });

                $grid->selector(function (Grid\Tools\Selector $selector) {
                    $selector->select('resource.id',
                        admin_trans_field('resource'). ':', Resource::query()
                            ->pluck('name', 'id')
                            ->all());
                });

                $grid->filter(function (Grid\Filter $filter) {
                    $filter->panel();

                    $filter->equal('actor_id', admin_trans_field('actor'))
                        ->multipleSelectTable(ActorTable::make())
                        ->options(function ($v) {
                            if (!$v) {
                                return [];
                            }
                            return Actor::find($v)->pluck('name', 'id');
                        })
                        ->width(6);
                });

                $grid->quickSearch(['name']);

                $grid->column('id')->sortable();
                $grid->column('resource.name', admin_trans_field('resource'))->sortable('resource_id');
                $grid->column('name')->sortable();
                $grid->column('country')->sortable();
                $grid->column('actor.name', admin_trans_field('actor'))->sortable('actor_id')->label();
                $grid->column('created_at');
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
        return Show::make($id, ResourceActor::query()->with(['resource', 'actor']), function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('resource.name', admin_trans_field('resource'));
                $show->field('name');
                $show->field('country');
                $show->field('actor.name', admin_trans_field('actor'));
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
        $existingActorId = null;
        return Form::make(ResourceActor::query(),
            function (Form $form) use (&$existingActorId) {
                $existingActorId = $form->model()->actor_id;
                $form->display('id');
                $form->text('name')->required();
                $form->text('country');
                $form->selectTable('resource_id', admin_trans_field('resource'))
                    ->from(ResourceTable::make())
                    ->model(Resource::class, 'id', 'name')
                    ->required();
                $form->selectTable('actor_id', admin_trans_field('actor'))
                    ->title(admin_trans_label('select_only_one_actor'))
                    ->from(ActorTable::make())
                    ->model(Actor::class, 'id', 'name');
            })->saved(function (Form $form) use (&$existingActorId)
        {
            $resourceActor = $form->model();

            if (empty($existingActorId) && !empty($resourceActor->actor_id)) {
                // Add
                event(new ResourceActorAddActorEvent($resourceActor->id));
            } elseif (!empty($existingActorId) && empty($resourceActor->actor_id)) {
                // Delete
                event(new ResourceActorRemoveActorEvent($resourceActor->id, $existingActorId));
            } elseif (!empty($existingActorId) &&
                !empty($resourceActor->actor_id) &&
                $existingActorId != $resourceActor->actor_id
            ) {
                // Update
                event(new ResourceActorRemoveActorEvent($resourceActor->id, $existingActorId));
            }
        });
    }

    public function title(): string
    {
        return admin_trans_label('resourceActor');
    }

    public function routeName(): string
    {
        return 'resourceActor';
    }
}
