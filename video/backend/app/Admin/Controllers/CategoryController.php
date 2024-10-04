<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\PublicFileTable;
use App\Admin\Components\Tables\ResourceCategoryTable;
use App\Admin\Components\Tables\ResourceTagTable;
use App\Dtos\BucketFileDto;
use App\Dtos\CategoryDto;
use App\Dtos\UploadFileDto;
use App\Models\Category;
use App\Models\File;
use App\Models\ResourceCategory;
use App\Models\ResourceTag;
use App\Services\CategoryService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class CategoryController extends AdminController
{

    private CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Category::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();

                $grid->filter(function (Grid\Filter $filter) {
                    $filter->panel();

                    $filter->equal('resourceCategories.id', admin_trans_field('resource_category'))
                        ->multipleSelectTable(ResourceCategoryTable::make())
                        ->options(function ($v) {
                            if (!$v) {
                                return [];
                            }
                            return ResourceCategory::find($v)->pluck('name', 'id');
                        })
                        ->width(6);
                });

                $grid->quickSearch(['name']);

                $grid->column('id')->sortable();
                $grid->column('name')->sortable();
                $grid->column('resource_category_names', admin_trans_field('resource_category'))->label();
                $grid->column('priority')->sortable();
                $grid->column('views_count')->sortable();
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
        return Show::make($id, Category::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('name');
                $show->field('priority');
                $show->field('resource_category_names', admin_trans_field('resource_category'))->label();
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
        return Form::make(Category::query()->with(['resourceCategories']),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');
                $form->text('name')->required();
                $form->number('priority');
                $form->multipleSelectTable('resource_category_ids', admin_trans_field('resource_category'))
                    ->title(admin_trans_label('select_resource_category'))
                    ->from(ResourceCategoryTable::make())
                    ->model(ResourceCategory::class, 'id', 'name');


                $options = [
                    Category::TYPE_CLOUD => admin_trans_option('Cloud','type'),
                    Category::TYPE_UPLOAD => admin_trans_option('Upload','type'),
                ];

                if ($form->isEditing()) {
                    unset($options[Category::TYPE_UPLOAD]);
                }

                $form->radio('type')
                    ->required()
                    ->options($options)
                    ->default(Category::TYPE_UPLOAD)
                    ->when([Category::TYPE_CLOUD], function (Form $form) {
                        $form->selectTable('avatar_file_id', admin_trans_field('thumbnail_file'))
                            ->title(admin_trans_label('select_thumbnail'))
                            ->from(PublicFileTable::make())
                            ->model(File::class, 'id', 'name')
                            ->required();
                    })->when([Category::TYPE_UPLOAD], function (Form $form) {
                        $form->image('avatar_file_path', admin_trans_field('thumbnail_file'))
                            ->url('ajax/upload')
                            ->autoUpload()
                            ->removable()
                            ->required();
                    });

            });
    }

    private function save()
    {
        $avatarFileDto = null;
        if (request()->input('type') == Category::TYPE_UPLOAD) {
            $avatarFileDto = new UploadFileDto([
                'fileId' => 0,
                'uploadPath' => request()->input('avatar_file_path'),
                'bucketType' => File::TYPE_PUBLIC_BUCKET,
            ]);
        } else if (request()->input('type') == Category::TYPE_CLOUD) {
            $avatarFileDto = new BucketFileDto(['fileId' => request()->input('avatar_file_id')]);
        }

        $dto = new CategoryDto([
            'type' => request()->input('type'),
            'categoryId' => request()->input('id') ? request()->input('id') : 0,
            'name' => request()->input('name', ''),
            'priority' => request()->input('priority', 0),
            'resourceCategoryIds' => request()->input('resource_category_ids') ? explode(',', request()->input('resource_category_ids')) : [],
            'avatarFileDto' => $avatarFileDto,
        ]);

        $this->categoryService->updateOrCreateCategory($dto);

        return $this->form()
            ->response()
            ->redirect('category/')
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
        return admin_trans_label('category');
    }

    public function routeName(): string
    {
        return 'category';
    }
}
