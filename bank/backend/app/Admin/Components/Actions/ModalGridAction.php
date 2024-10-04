<?php

namespace App\Admin\Components\Actions;

use Dcat\Admin\Grid\LazyRenderable;
use Dcat\Admin\Grid\Tools\AbstractTool;
use Dcat\Admin\Widgets\Modal;

class ModalGridAction extends AbstractTool
{
    private LazyRenderable $grid;

    public function __construct(LazyRenderable $grid, $title = null)
    {
        $this->grid = $grid;
        parent::__construct($title);
    }

    public function html()
    {
        return Modal::make()
            ->lg()
            ->title($this->title())
            ->body($this->grid)
            ->button("<button class='btn btn-primary pull-right ml-1'><i class=\"feather icon-folder\"></i> &nbsp;{$this->title()}</button> &nbsp;");
    }
}
