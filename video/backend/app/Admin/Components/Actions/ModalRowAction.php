<?php

namespace App\Admin\Components\Actions;

use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Widgets\Modal;

abstract class ModalRowAction extends RowAction
{

    public function render()
    {
        return Modal::make()
            ->lg()
            ->title($this->modalSubject())
            ->body($this->modalContent())
            ->button($this->title());
    }

    protected abstract function modalSubject();

    protected abstract function modalContent();

}
