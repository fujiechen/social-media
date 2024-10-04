<?php

namespace App\Admin\Components\Actions;

use Dcat\Admin\Grid\Tools\AbstractTool;
use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Widgets\Modal;

class ModalFormAction extends AbstractTool
{
    private Form $form;

    public function __construct(Form $form, $title = null)
    {
        $this->form = $form;
        parent::__construct($title);
    }

    public function html()
    {
        return Modal::make()
            ->lg()
            ->title($this->title())
            ->body($this->form)
            ->button("<button class='btn btn-primary pull-right ml-1'><i class=\"feather icon-folder\"></i> &nbsp;{$this->title()}</button> &nbsp;");
    }
}
