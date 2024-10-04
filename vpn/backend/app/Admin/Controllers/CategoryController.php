<?php

namespace App\Admin\Controllers;

use App\Dtos\CategoryDto;
use App\Dtos\FileDto;
use App\Models\Category;
use App\Models\File;
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
                $grid->disableFilterButton();

                $grid->column('id')->sortable();
                $grid->column('name');
                $grid->column('description');
                $grid->column('updated_at');
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
        return Show::make($id, Category::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('name', 'Name');
                $show->field('description', 'Description')->label();
                $show->field('updated_at', 'Updated');
                $show->field('created_at', 'Created');
            });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(Category::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');
                $form->text('name', 'Name')->required();
                $form->textarea('description', 'Description')->required();

                $form->table('tags', 'Tags', function($form) {
                    $form->text('name', 'Tag');
                })->required();

                $form->table('highlights', 'Highlights', function($form) {
                    $form->text('name', 'Highlight');
                    $form->radio('switch', 'On/Off')->options([
                        0 => 'No',
                        1 => 'Yes'
                    ]);
                })->required();

                $form->image('thumbnail_file_path', 'Thumbnail Image')
                    ->url('ajax/upload')
                    ->autoUpload()
                    ->removable();
            });
    }

    private function save()
    {
        //use ajax will call with empty request once
        if (!empty(request()->input('name'))) {
            $thumbnailFileDto = FileDto::createFileDto(request()->input('thumbnail_file_path'), File::TYPE_PUBLIC_BUCKET);

            $tags = [];
            foreach (request()->input('tags') as $tag) {
                if (isset($tag['_remove_']) && $tag['_remove_'] == 1) {
                    continue;
                }

                $tags[] = [
                    'name' => $tag['name'],
                ];
            }

            $highlights = [];
            foreach (request()->input('highlights') as $highlight) {
                if (isset($highlight['_remove_']) && $highlight['_remove_'] == 1) {
                    continue;
                }

                $highlights[] = [
                    'name' => $highlight['name'],
                    'switch' => $highlight['switch'],
                ];
            }

            $dto = new CategoryDto([
                'categoryId' => request()->input('id') ? request()->input('id') : 0,
                'name' => request()->input('name', ''),
                'description' => request()->input('description', ''),
                'thumbnailFileDto' => $thumbnailFileDto,
                'tags' => $tags,
                'highlights' => $highlights,
            ]);

            $this->categoryService->updateOrCreateCategory($dto);

            return $this->form()
                ->response()
                ->redirect('category/')
                ->success(trans('admin.save_succeeded'));
        }
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
        return 'Categories';
    }

    public function routeName(): string
    {
        return 'category';
    }
}
