<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\ServerTable;
use App\Admin\Components\Tables\UserTable;
use App\Dtos\FileDto;
use App\Dtos\ServerUserDto;
use App\Models\File;
use App\Models\Server;
use App\Models\ServerUser;
use App\Models\User;
use App\Services\FileService;
use App\Services\ServerUserService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;
use Illuminate\Http\JsonResponse;

class ServerUserController extends AdminController
{

    private ServerUserService $serverUserService;
    private FileService $fileService;

    public function __construct(ServerUserService $serverUserService, FileService $fileService)
    {
        $this->serverUserService = $serverUserService;
        $this->fileService = $fileService;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(ServerUser::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->column('id')->sortable();
                $grid->column('user_id', 'User ID');
                $grid->column('user.nickname', 'User');
                $grid->column('server.category.name', 'Category');
                $grid->column('server.name', 'Server');
                $grid->column('server.type', 'Type');
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
        return Show::make($id, ServerUser::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                /**
                 * @var ServerUser $serverUser
                 */
                $serverUser = $show->model();

                $url = $serverUser->server->ovpn_file_path;
                $show->field('id');
                $show->field('user_id', 'User ID');
                $show->field('user.nickname', 'User');
                $show->field('server.category.name', 'Category');
                $show->field('server.type', 'Type');
                $show->field('server.name', 'Server');

                if ($url) {
                    $show->field('OVPN File URL')->value($url)->link();
                }

                $show->field('radius_uuid', 'Radius UUID');
                $show->field('server.ipsec_shared_key', 'IPsec Shared Key');
                $show->field('radius_username', 'Radius Username');
                $show->field('radius_password', 'Radius Password');
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
        return Form::make(ServerUser::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');

                $form->selectTable('server_id', 'Server')
                    ->title('Please select a Server')
                    ->from(ServerTable::make())
                    ->model(Server::class, 'id', 'name')
                    ->required();

                $form->selectTable('user_id', 'User')
                    ->title('Please select a User')
                    ->from(UserTable::make())
                    ->model(User::class, 'id', 'nickname')
                    ->required();

                $form->text('radius_uuid', 'Radius UUID');
                $form->text('radius_username', 'Radius Username');
                $form->text('radius_password', 'Radius Password');
            });
    }

    private function save()
    {
        if (empty(request()->input('server_id'))) {
            return new JsonResponse();
        }

        $dto = new ServerUserDto([
            'serverId' => request()->input('server_id'),
            'userId' => request()->input('user_id'),
            'radiusUuid' => request()->input('radius_uuid'),
            'radiusUsername' => request()->input('radius_username'),
            'radiusPassword' => request()->input('radius_password'),
        ]);

        $this->serverUserService->updateOrCreateServerUser($dto);

        return $this->form()
            ->response()
            ->redirect('serverUser/')
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
        return 'Server Users';
    }

    public function routeName(): string
    {
        return 'serverUser';
    }
}
