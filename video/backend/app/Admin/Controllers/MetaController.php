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
                $grid->column('meta_key');
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
        return Show::make($id, Meta::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('meta_key');
                $show->field('meta_value');
                $show->field('created_at_formatted');
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

                $options = [];
                foreach (Meta::META_KEYS as $metaKey) {
                    $options[$metaKey] = $metaKey;
                }

                $form->select('meta_key')
                    ->options($options)
                    ->required();
                $form->textarea('meta_value');

            });
    }

    public function title(): string
    {
        return admin_trans_label('meta');
    }

    public function routeName(): string
    {
        return 'meta';
    }
}
