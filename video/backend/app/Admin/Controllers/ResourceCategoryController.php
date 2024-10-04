<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\CategoryTable;
use App\Admin\Components\Tables\ResourceTable;
use App\Events\ResourceCategoryAddCategoryEvent;
use App\Events\ResourceCategoryRemoveCategoryEvent;
use App\Models\Category;
use App\Models\Resource;
use App\Models\ResourceCategory;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class ResourceCategoryController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(ResourceCategory::query()->with(['resource', 'category']),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();

                $grid->quickCreate(function (Grid\Tools\QuickCreate $create) {
                    $create->select('resource_id', admin_trans_field('Resource'))
                        ->options(Resource::query()->pluck('name', 'id')->toArray());
                    $create->text('name', admin_trans_field('name'));
                });

                $grid->selector(function (Grid\Tools\Selector $selector) {
                    $selector->select('resource.id',
                        admin_trans_field('Resource').':', Resource::query()
                            ->pluck('name', 'id')
                            ->all());
                });

                $grid->filter(function (Grid\Filter $filter) {
                    $filter->panel();

                    $filter->equal('category_id', admin_trans_field('category'))
                        ->multipleSelectTable(CategoryTable::make())
                        ->options(function ($v) {
                            if (!$v) {
                                return [];
                            }
                            return Category::find($v)->pluck('name', 'id');
                        })
                        ->width(6);
                });

                $grid->quickSearch(['name']);

                $grid->column('id')->sortable();
                $grid->column('resource.name', admin_trans_field('resource'))->sortable('resource_id');
                $grid->column('name')->sortable();
                $grid->column('category.name', admin_trans_field('category'))->sortable('category_id')->label();
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
        return Show::make($id, ResourceCategory::query()->with(['resource', 'category']), function (Show $show) {
            $show->disableEditButton();
            $show->disableDeleteButton();

            $show->field('id');
            $show->field('resource.name', admin_trans_field('resource'));
            $show->field('name');
            $show->field('category.name', admin_trans_field('category'));
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
        $existingCategoryId = null;
        return Form::make(ResourceCategory::query(),
            function (Form $form) use (&$existingCategoryId) {
                $existingCategoryId = $form->model()->category_id;
                $form->display('id');
                $form->text('name', 'Name')->required();
                $form->selectTable('resource_id', admin_trans_field('resource'))
                    ->from(ResourceTable::make())
                    ->model(Resource::class, 'id', 'name')
                    ->required();
                $form->selectTable('category_id', admin_trans_field('category'))
                    ->title(admin_trans_label('select_only_one_category'))
                    ->from(CategoryTable::make())
                    ->model(Category::class, 'id', 'name');
            })->saved(function (Form $form) use (&$existingCategoryId) {

                $resourceCategory = $form->model();

                if (empty($existingCategoryId) && !empty($resourceCategory->category_id)) {
                    // Add
                    event(new ResourceCategoryAddCategoryEvent($resourceCategory->id));
                } elseif (!empty($existingCategoryId) && empty($resourceCategory->category_id)) {
                    // Delete
                    event(new ResourceCategoryRemoveCategoryEvent($resourceCategory->id, $existingCategoryId));
                } elseif (!empty($existingCategoryId) &&
                    !empty($resourceCategory->category_id) &&
                    $existingCategoryId != $resourceCategory->category_id
                ) {
                    // Update
                    event(new ResourceCategoryRemoveCategoryEvent($resourceCategory->id, $existingCategoryId));
                }
        });
    }

    public function title(): string
    {
        return admin_trans_label('resourceCategory');
    }

    public function routeName(): string
    {
        return 'resourceCategory';
    }
}
