<?php

namespace App\Admin\Controllers;

use App\Models\Job;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;

class JobController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Job::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableActions();

                $grid->column('id')->sortable();
                $grid->column('attempts');
                $grid->column('created_at_formatted');
                $grid->column('payload');
            });
    }


    public function title(): string
    {
        return 'Jobs';
    }

    public function routeName(): string
    {
        return 'job';
    }
}
