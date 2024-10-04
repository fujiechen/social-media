<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\ResourceTable;
use App\Admin\Components\Tables\TagTable;
use App\Events\ResourceTagAddTagEvent;
use App\Events\ResourceTagRemoveTagEvent;
use App\Models\Resource;
use App\Models\ResourceTag;
use App\Models\Tag;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class ResourceTagController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(ResourceTag::query()->with(['resource', 'tag']),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableDeleteButton();

                $grid->quickCreate(function (Grid\Tools\QuickCreate $create) {
                    $create->select('resource_id', admin_trans_field('resource'))
                        ->options(Resource::query()->pluck('name', 'id')->toArray());
                    $create->text('name', admin_trans_field('name'));
                });

                $grid->selector(function (Grid\Tools\Selector $selector) {
                    $selector->select('resource.id',
                        admin_trans_field('resource').':', Resource::query()
                            ->pluck('name', 'id')
                            ->all());
                });

                $grid->filter(function (Grid\Filter $filter) {
                    $filter->panel();

                    $filter->equal('tag_id', admin_trans_field('tag'))
                        ->multipleSelectTable(TagTable::make())
                        ->options(function ($v) {
                            if (!$v) {
                                return [];
                            }
                            return Tag::find($v)->pluck('name', 'id');
                        })
                        ->width(6);
                });

                $grid->quickSearch(['name']);

                $grid->column('id')->sortable();
                $grid->column('resource.name', admin_trans_field('resource'))->sortable('resource_id');
                $grid->column('name')->sortable('name');
                $grid->column('tag.name', admin_trans_field('tag'))->sortable('tag_id')->label();
                $grid->column('created_at')->sortable();
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
        return Show::make($id, ResourceTag::query()->with(['resource', 'tag']), function (Show $show) {
            $show->disableEditButton();
            $show->disableDeleteButton();

            $show->field('id');
            $show->field('resource.name', admin_trans_field('resource'));
            $show->field('name');
            $show->field('tag.name', admin_trans_field('tag'));
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
        $existingTagId = null;
        return Form::make(ResourceTag::query(),
            function (Form $form) use (&$existingTagId) {
                $existingTagId = $form->model()->tag_id;
                $form->disableDeleteButton();
                $form->display('id');
                $form->text('name')->required();

                $form->selectTable('resource_id', admin_trans_field('resource'))
                    ->from(ResourceTable::make())
                    ->model(Resource::class, 'id', 'name')
                    ->required();

                $form->selectTable('tag_id', admin_trans_field('tag'))
                    ->title(admin_trans_label('select_only_one_tag'))
                    ->from(TagTable::make())
                    ->model(Tag::class, 'id', 'name');
            })->saved(function (Form $form) use (&$existingTagId)
            {
                $resourceTag = $form->model();

                if (empty($existingTagId) && !empty($resourceTag->tag_id)) {
                    // Add
                    event(new ResourceTagAddTagEvent($resourceTag->id));
                } elseif (!empty($existingTagId) && empty($resourceTag->tag_id)) {
                    // Delete
                    event(new ResourceTagRemoveTagEvent($resourceTag->id, $existingTagId));
                } elseif (!empty($existingTagId) &&
                    !empty($resourceTag->tag_id) &&
                    $existingTagId != $resourceTag->tag_id
                ) {
                    // Update
                    event(new ResourceTagRemoveTagEvent($resourceTag->id, $existingTagId));
                }
            });
    }

    public function title(): string
    {
        return admin_trans_label('resourceTag');
    }

    public function routeName(): string
    {
        return 'resourceTag';
    }
}
