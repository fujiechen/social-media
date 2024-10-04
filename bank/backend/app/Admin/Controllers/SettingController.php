<?php

namespace App\Admin\Controllers;

use App\Models\Meta;
use App\Models\Setting;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class SettingController extends AdminController
{

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Setting::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();
                $grid->disableDeleteButton();

                $grid->column('id')->sortable();
                $grid->column('name');
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
        return Show::make($id, Setting::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('name');
                $show->field('value');
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
        return Form::make(Setting::query(),
            function (Form $form) {
                $form->disableDeleteButton();
                $form->display('id');
                $form->hidden('id');

                $form->select('name')
                    ->options([
                        Setting::URL_ABOUT_US => Setting::URL_ABOUT_US,
                        Setting::URL_BANNER_IMAGE => Setting::URL_BANNER_IMAGE,
                        Setting::TEXT_BANNER_TITLE => Setting::TEXT_BANNER_TITLE,
                        Setting::TEXT_BANNER_SLOGAN => Setting::TEXT_BANNER_SLOGAN,
                        Setting::URL_ACCOUNT_IMAGE => Setting::URL_ACCOUNT_IMAGE,
                        Setting::TRANSLATABLE_HTML_HELP_APP => Setting::TRANSLATABLE_HTML_HELP_APP,
                        Setting::TRANSLATABLE_HTML_TERMS_APP => Setting::TRANSLATABLE_HTML_TERMS_APP,
                        Setting::JSON_BANK_ACCOUNT => Setting::JSON_BANK_ACCOUNT,
                        Setting::TRANSLATABLE_HTML_HELP_DEPOSIT => Setting::TRANSLATABLE_HTML_HELP_DEPOSIT,
                        Setting::TRANSLATABLE_HTML_HELP_EXCHANGE => Setting::TRANSLATABLE_HTML_HELP_EXCHANGE,
                        Setting::TRANSLATABLE_HTML_HELP_TRANSFER => Setting::TRANSLATABLE_HTML_HELP_TRANSFER,
                        Setting::TRANSLATABLE_HTML_HELP_PURCHASE => Setting::TRANSLATABLE_HTML_HELP_PURCHASE,
                        Setting::TRANSLATABLE_HTML_HELP_WITHDRAW => Setting::TRANSLATABLE_HTML_HELP_WITHDRAW,
                        Setting::URL_CUSTOMER_SERVICE => Setting::URL_CUSTOMER_SERVICE,
                        Setting::DEPOSIT_AMOUNT_OPTIONS => Setting::DEPOSIT_AMOUNT_OPTIONS,
                    ])
                    ->required();
                $form->textarea('value');

            });
    }

    public function title(): string
    {
        return admin_trans_label('setting');
    }

    public function routeName(): string
    {
        return 'setting';
    }
}
