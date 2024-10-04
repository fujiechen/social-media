<?php

namespace App\Admin\Components\Actions;

use App\Models\Media;
use Dcat\Admin\Grid\BatchAction;
use Illuminate\Http\Request;

class BatchUpdateMediaStatusFromDraftToReadyAction extends BatchAction
{
    public function __construct($title = null)
    {
        $this->title = $title;
    }

    // 确认弹窗信息
    public function confirm(): string
    {
        return admin_trans_label('confirm_change_draft_to_ready');
    }

    // 处理请求
    public function handle(Request $request)
    {
        // 获取选中的文章ID数组
        $keys = $this->getKey();

        $medias = Media::query()->whereIn('id', $keys)->get();

        /**
         * @var Media $media
         */
        foreach ($medias as $media) {
            if ($media->status != Media::STATUS_DRAFT) {
                continue;
            }

            $media->status = Media::STATUS_READY;
            $media->save();
        }

        $successText = admin_trans_label('batch_update_success');
        return $this->response()->success($successText)->refresh();
    }
}
