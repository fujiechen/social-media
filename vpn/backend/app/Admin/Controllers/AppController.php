<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\AppCategoryTable;
use App\Dtos\AppDto;
use App\Dtos\FileDto;
use App\Models\App;
use App\Models\AppCategory;
use App\Models\File;
use App\Services\AppService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class AppController extends AdminController
{

    private AppService $appService;

    public function __construct(AppService $appService)
    {
        $this->appService = $appService;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(App::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->column('id')->sortable();
                $grid->column('name');
                $grid->column('appCategory.name', 'Category');
                $grid->column('description');
                $grid->column('is_hot', 'Hot')->bool();
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
        return Show::make($id, App::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('name', 'Name');
                $show->field('app_category.name', 'Category');
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
        return Form::make(App::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');

                $form->text('name', 'Name')->required();

                $form->selectTable('app_category_id', 'Category')
                    ->title('Please select a category')
                    ->from(AppCategoryTable::make())
                    ->model(AppCategory::class, 'id', 'name')
                    ->required();

                $form->url('url', 'Url')->required();
                $form->textarea('description', 'Description');

                $form->radio('is_hot_int', 'Hot')
                    ->options([
                        0 => 'No',
                        1 => 'Yes'
                    ])->required();

                $form->image('icon_file_path', 'Icon')
                    ->url('ajax/upload')
                    ->autoUpload()
                    ->removable();
            });
    }

    private function save()
    {
        //use ajax will call with empty request once
        if (!empty(request()->input('name'))) {
            $iconFileDto = FileDto::createFileDto(request()->input('icon_file_path'), File::TYPE_PUBLIC_BUCKET);
            $dto = new AppDto([
                'appId' => request()->input('id') ? request()->input('id') : 0,
                'isHot' => (bool)request()->input('is_hot_int'),
                'name' => request()->input('name'),
                'appCategoryId' => request()->input('app_category_id'),
                'url' => request()->input('url'),
                'description' => request()->input('description', ''),
                'iconFileDto' => $iconFileDto,
            ]);

            $this->appService->updateOrCreateApp($dto);

            return $this->form()
                ->response()
                ->redirect('app/')
                ->success(trans('admin.save_succeeded'));
        }
    }

    public
    function store()
    {
        return $this->save();
    }

    public
    function update($id)
    {
        return $this->save();
    }

    public
    function title(): string
    {
        return 'Apps';
    }

    public
    function routeName(): string
    {
        return 'app';
    }
}
