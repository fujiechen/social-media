<?php

namespace App\Admin\Controllers;

use App\Models\Meta;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class MetaController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Meta::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->column('id')->sortable();
                $grid->column('key', 'Key');
                $grid->column('created_at_formatted', 'Created');
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
        return Show::make($id, Meta::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('key');
                $show->field('value');
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
        return Form::make(Meta::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');

                $form->select('key', 'Key')
                    ->options([
                        Meta::BANNER_HOME_URL => Meta::BANNER_HOME_URL,
                        Meta::SHARE_IMAGE_URL => Meta::SHARE_IMAGE_URL,
                        Meta::SHARE_TEXT => Meta::SHARE_TEXT,
                        Meta::SHARE_INSTRUCTION_HTML => Meta::SHARE_INSTRUCTION_HTML,
                        Meta::CUSTOMER_SERVICE_QR_HTML => Meta::CUSTOMER_SERVICE_QR_HTML
                    ])
                    ->required();
                $form->textarea('value', 'Value');

            });
    }

    public function title(): string
    {
        return 'Meta';
    }

    public function routeName(): string
    {
        return 'meta';
    }
}
