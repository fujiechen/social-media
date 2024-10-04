<?php

namespace App\Admin\Controllers;

use App\Dtos\UserDto;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Dcat\Admin\Exception\InvalidArgumentException;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;
use Error;
use Exception;

class UserController extends AdminController
{

    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function store()
    {
        return $this->save();
    }

    private function save()
    {
        try {
            $this->userService->createUnionUser(new UserDto([
                'username' => request()->input('username'),
                'password' => request()->input('password'),
                'nickname' => request()->input('nickname'),
                'language' => request()->input('language'),
                'email' => request()->input('email'),
                'roleIds' => array_filter(request()->input('role_ids'), function ($item) {
                    return !empty($item);
                }),
            ]));
        } catch (Error|Exception $e) {
            throw new InvalidArgumentException('Invalid Inputs');
        }

        return $this->form()
            ->response()
            ->redirect('user')
            ->success(trans('admin.save_succeeded'));
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(User::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');
                $form->text('username')->required();
                $form->text('email')->required();
                $form->text('nickname')->required();
                $form->password('password')->required();
                $form->select('language')
                    ->options([
                        'en' => admin_trans_option('en', 'languages'),
                        'zh' => admin_trans_option('zh', 'languages'),
                    ])
                    ->default('zh')
                    ->required();
                $form->checkbox('role_ids', admin_trans_field('roles'))
                    ->options([
                        Role::ROLE_ADMINISTRATOR_ID => Role::ROLE_ADMINISTRATOR_NAME,
                        Role::ROLE_USER_ID => Role::ROLE_USER_NAME,
                    ])
                    ->default(Role::ROLE_USER_ID)
                    ->required();
            });
    }

    public function update($id)
    {
        return $this->save();
    }

    public function title(): string
    {
        return admin_trans_label('user');
    }

    public function routeName(): string
    {
        return 'user';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(User::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableDeleteButton();
                $grid->disableEditButton();

                $grid->column('id')->sortable();
                $grid->column('username');
                $grid->column('nickname');
                $grid->column('role_names', admin_trans_field('roles'))->label();
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
        return Show::make($id, User::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('username');
                $show->field('nickname');
                $show->field('role_names', admin_trans_field('roles'))->label();
                $show->field('created_at');
            });
    }
}
