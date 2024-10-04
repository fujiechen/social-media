<?php

namespace App\Admin\Components\Actions;

use App\Models\Event;
use Dcat\Admin\Show;

class EventLinkDisplayRowAction extends ModalRowAction
{
    public function title()
    {
        return '<i class="feather icon-edit"></i> 邀请达人 &nbsp;&nbsp;';
    }

    protected function modalSubject()
    {
        return '邀请达人';
    }

    protected function modalContent()
    {
        return Show::make(null, function (Show $show) {
            $show->disableDeleteButton();
            $show->disableEditButton();
            $show->disableListButton();

            $show->panel()->title('');

            $event = Event::find($this->getKey());
            $show->html('
                <div class="form-group purple-border">
                  <label for="exampleFormControlTextarea4">请发送下面的内容给您需要邀请的达人</label>
                  <textarea class="form-control" id="event_link_'. $event->id .'" rows="5">'
                . $event->invite_content .
                 '</textarea>
                </div>
                <button class="btn btn-white"
                onclick=\'$("#event_link_' . $event->id . '").select();
                document.execCommand("copy");\'>复制邀请链接</button>
            ');
        });
    }
}
