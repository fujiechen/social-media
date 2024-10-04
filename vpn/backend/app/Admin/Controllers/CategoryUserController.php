<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\CategoryTable;
use App\Admin\Components\Tables\UserTable;
use App\Dtos\CategoryUserDto;
use App\Models\Category;
use App\Models\CategoryUser;
use App\Models\User;
use App\Services\CategoryUserService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;
use Illuminate\Support\Carbon;

class CategoryUserController extends AdminController
{

    private CategoryUserService $categoryUserService;

    public function __construct(CategoryUserService $categoryUserService) {
        $this->categoryUserService = $categoryUserService;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(CategoryUser::query(),
            function (Grid $grid) {
                $grid->header('<div class="alert alert-info">如果Server Synced = false, 则vpn server同步出现错误，需要手动介入</div>');
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->column('id')->sortable();
                $grid->column('user.nickname', 'User');
                $grid->column('category.name', 'Category');
                $grid->column('vpn_server_synced', 'Server Synced')->bool();
                $grid->column('valid_until_at_formatted', 'Valid Until');
                $grid->column('valid_until_at_days', 'Valid Days');
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
        return Show::make($id, CategoryUser::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('created_at_formatted', 'Created');
            });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(CategoryUser::query(),
            function (Form $form) {
                $form->html('<div class="alert alert-info">更新后会触发所有分类下的server同步enable/disable user, 如果Server Synced = false, 则vpn server同步出现错误，需要手动介入</div>');
                $form->display('id');
                $form->hidden('id');

                $form->selectTable('category_id', 'Category')
                    ->title('Please select a Server')
                    ->from(CategoryTable::make())
                    ->model(Category::class, 'id', 'name')
                    ->required();

                $form->selectTable('user_id', 'User')
                    ->title('Please select a User')
                    ->from(UserTable::make())
                    ->model(User::class, 'id', 'nickname')
                    ->required();

                $form->date('valid_until_at', 'Valid Until');

            });
    }

    private function save() {
        $validUntilAt = null;
        if (request()->input('valid_until_at')) {
            $validUntilAt = Carbon::parse(request()->input('valid_until_at'));
        }

        $dto = new CategoryUserDto([
            'categoryId' => request()->input('category_id'),
            'userId' => request()->input('user_id'),
            'validUntilAt' => $validUntilAt,
        ]);

        $this->categoryUserService->updateOrCreateCategoryUser($dto);

        return $this->form()
            ->response()
            ->redirect('categoryUser/')
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
        return 'Category Users';
    }

    public function routeName(): string
    {
        return 'categoryUser';
    }
}
