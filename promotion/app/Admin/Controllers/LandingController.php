<?php

namespace App\Admin\Controllers;

use App\Models\Landing;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;

class LandingController extends AdminController
{

    public function title(): string
    {
        return admin_trans_label('landing');
    }

    public function routeName(): string
    {
        return 'landing';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Landing::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableDeleteButton();
                $grid->disableCreateButton();
                $grid->disableActions();

                $grid->column('id')->sortable();
                $grid->column('url');
                $grid->column('signature');
                $grid->column('landingTemplate.name', admin_trans_field('landing_template'));
                $grid->column('post.title', admin_trans_field('post'));
                $grid->column('account.nickname', admin_trans_field('account'));
                $grid->column('ip');
                $grid->column('country');
                $grid->column('redirect')->bool();
                $grid->column('created_at_formatted');
            });
    }
}
