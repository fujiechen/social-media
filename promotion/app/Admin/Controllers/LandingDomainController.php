<?php

namespace App\Admin\Controllers;

use App\Models\LandingDomain;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class LandingDomainController extends AdminController
{

    public function title(): string
    {
        return admin_trans_label('landingDomain');
    }

    public function routeName(): string
    {
        return 'landingDomain';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(LandingDomain::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->column('id')->sortable();
                $grid->column('name');
                $grid->column('description');
                $grid->column('status');
                $grid->column('updated_at_formatted');
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
        return Show::make($id, LandingDomain::query(), function (Show $show) {
            $show->disableEditButton();
            $show->disableDeleteButton();

            $show->field('id');
            $show->field('name');
            $show->field('status');
            $show->field('updated_at_formatted');
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
        return Form::make(LandingDomain::query(),
            function (Form $form) {
                $form->display('id');
                $form->text('name')->required();
                $form->textarea('description');
                $form->text('access_key')->required();
                $form->text('secret')->required();
                $form->text('region')->required();
                $form->text('bucket')->required();
                $form->url('endpoint_url')->required();
                $form->url('cdn_url');
                $form->select('status')
                    ->options([
                        LandingDomain::STATUS_ACTIVE => admin_trans_option('active', 'landing_domain_status'),
                        LandingDomain::STATUS_INACTIVE => admin_trans_option('inactive', 'landing_domain_status'),
                    ])
                    ->default(LandingDomain::STATUS_ACTIVE)
                    ->required();
            });
    }
}
