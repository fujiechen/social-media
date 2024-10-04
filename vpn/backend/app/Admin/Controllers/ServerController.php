<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\CategoryTable;
use App\Dtos\FileDto;
use App\Dtos\ServerDto;
use App\Model\Exercise;
use App\Models\Category;
use App\Models\File;
use App\Models\Server;
use App\Services\ServerService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;
use Illuminate\Http\JsonResponse;

class ServerController extends AdminController
{

    private ServerService $serverService;

    public function __construct(ServerService $serverService)
    {
        $this->serverService = $serverService;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Server::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->column('id')->sortable();
                $grid->column('category.name', 'Category');
                $grid->column('type', 'Type');
                $grid->column('name', 'Name');
                $grid->column('country_code', 'Country');
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
        return Show::make($id, Server::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('name', 'Name');
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
        return Form::make(Server::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');
                $form->text('name', 'Name')->required();

                $form->radio('type', 'Type')
                    ->options([
                        Server::TYPE_IPSEC => Server::TYPE_IPSEC,
                        Server::TYPE_OPENVPN => Server::TYPE_OPENVPN,
                    ])->when([Server::TYPE_IPSEC], function (Form $form) {
                        $form->text('ipsec_shared_key', 'IPsec Shared Key');
                    })->when([Server::TYPE_OPENVPN], function (Form $form) {
                        $form->file('ovpn_file_path', 'OVPN file')
                            ->url('ajax/upload')
                            ->autoUpload()
                            ->removable();
                    });

                $form->textarea('description', 'Description');

                $form->text('country_code', 'Country Code')->required();
                $form->text('ip', 'IP')->required();

                $form->selectTable('category_id', 'Category')
                    ->title('Please select a thumbnail file')
                    ->from(CategoryTable::make())
                    ->model(Category::class, 'id', 'name')
                    ->required();

                $form->text('admin_url', 'Admin URL');
                $form->text('admin_username', 'Admin Username');
                $form->text('admin_password', 'Admin Password');

                $form->text('api_url', 'API URL');
                $form->text('api_key', 'API Key');
                $form->text('api_secret', 'API Secret');

                $form->file('admin_pem_file_path', 'Admin Pem File')
                    ->url('ajax/upload')
                    ->autoUpload()
                    ->removable();
            });
    }

    private function save()
    {
        if (empty(request()->input('name'))) {
            return new JsonResponse();
        }

        $adminPemFileDto = null;
        if (request()->input('admin_pem_file_path')) {
            $adminPemFileDto = FileDto::createFileDto(request()->input('admin_pem_file_path'), File::TYPE_PRIVATE_BUCKET);
        }

        $ovpnFileDto = null;
        if (request()->input('ovpn_file_path')) {
            $ovpnFileDto = FileDto::createFileDto(request()->input('ovpn_file_path'), File::TYPE_PRIVATE_BUCKET);
        }

        $dto = new ServerDto([
            'serverId' => request()->input('id'),
            'name' => request()->input('name'),
            'type' => request()->input('type'),
            'ip' => request()->input('ip'),
            'countryCode' => request()->input('country_code'),
            'categoryId' => request()->input('category_id'),
            'adminUrl' => request()->input('admin_url'),
            'adminUsername' => request()->input('admin_username'),
            'adminPassword' => request()->input('admin_password'),
            'apiUrl' => request()->input('api_url'),
            'apiKey' => request()->input('api_key'),
            'apiSecret' => request()->input('api_secret'),
            'ipSecSharedKey' => request()->input('ipsec_shared_key'),
            'adminPemFileDto' => $adminPemFileDto,
            'ovpnFileDto' => $ovpnFileDto,
        ]);

        $this->serverService->updateOrCreateServer($dto);

        return $this->form()
            ->response()
            ->redirect('server/')
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
        return 'Server';
    }

    public function routeName(): string
    {
        return 'server';
    }
}
