<?php

namespace App\Admin\Components\Tables;

use App\Models\Contact;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Illuminate\Contracts\Support\Renderable;

class ContactTable extends LazyRenderable implements Renderable
{
    public function grid(): Grid
    {
        return Grid::make(Contact::query(),
            function (Grid $grid) {
                $grid->disableCreateButton();
                $grid->disableRefreshButton();
                $grid->disableActions();

                $grid->quickSearch(['contact']);

                $grid->column('id', 'Id');
                $grid->column('contact', 'Email or Phone');
                $grid->column('type', 'Type');
                $grid->column('created_at_formatted', 'Created');
            });
    }
}
