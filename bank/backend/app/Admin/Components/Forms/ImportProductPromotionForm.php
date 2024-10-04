<?php

namespace App\Admin\Components\Forms;

use App\Jobs\ImportFileEvent;
use App\Jobs\ImportFileEventHandler;
use App\Models\ProductPromotionType;
use Dcat\Admin\Admin;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Facades\Storage;

class ImportProductPromotionForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function form()
    {
        $businessId = session('business_id');
        $this->textarea('product_promotion_description', '达人需求')->help('批量应用于全部商品')->required();
        $this->file('file', '文件')
            ->help('请上传xlsx文件，仅处理第一张表格。重复的商品会被复写。')
            ->required()
            ->disk($this->disk())
            ->accept('xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->autoUpload();
    }

    protected function disk()
    {
        //TODO change to s3 in the future
        return 'local';
    }

    public function handle(array $input)
    {
        $businessId = session('business_id');
        $productPromotionDescription = $input['product_promotion_description'];
        $productPromotionTypeIds = $input['product_promotion_type_ids'];

        $oldFilePath = $input['file'];
        $newFilePath = $businessId . '/' . time() . '.xlsx';
        Storage::move($oldFilePath, $newFilePath);

        ImportFileEventHandler::dispatch(new ImportFileEvent([
            'userId' => Admin::user()->id,
            'businessId' => $businessId,
            'filePath' => $newFilePath,
            'productPromotionTypeIds' => $productPromotionTypeIds,
            'productPromotionDescription' => $productPromotionDescription
        ]));

        return $this->response()
            ->success('文件上传成功，请耐心等待后刷新页面')
            ->refresh();
    }
}
