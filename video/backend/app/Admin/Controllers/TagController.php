<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\ResourceTagTable;
use App\Dtos\TagDto;
use App\Models\ResourceTag;
use App\Models\Tag;
use App\Services\TagService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class TagController extends AdminController
{

    private TagService $tagService;

    public function __construct(TagService $tagService) {
        $this->tagService = $tagService;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Tag::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();

                $grid->filter(function (Grid\Filter $filter) {
                    $filter->panel();

                    $filter->equal('resourceTags.id', admin_trans_field('resource_tag'))
                        ->multipleSelectTable(ResourceTagTable::make())
                        ->options(function ($v) {
                            if (!$v) {
                                return [];
                            }
                            return ResourceTag::find($v)->pluck('name', 'id');
                        })
                        ->width(6);
                });

                $grid->quickSearch(['name']);

                $grid->column('id')->sortable();
                $grid->column('name')->sortable();
                $grid->column('priority')->sortable();
                $grid->column('views_count')->sortable();
                $grid->column('resource_tag_names', admin_trans_field('resource_tag'))->label();

                $grid->quickCreate(function (Grid\Tools\QuickCreate $create) {
                    $create->text('name', admin_trans_field('name'));
                });
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
        return Show::make($id, Tag::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('name');
                $show->field('priority');
                $show->field('views_count');
                $show->field('resource_tag_names', admin_trans_field('resource_tag'))->label();
                $show->field('created_at');
            });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(Tag::query()->with(['resourceTags']),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');
                $form->text('name')->required();
                $form->number('priority')->default(0);
                $form->multipleSelectTable('resource_tag_ids', admin_trans_field('resource_tag'))
                    ->title(admin_trans_field('select_resource_tag'))
                    ->from(ResourceTagTable::make())
                    ->model(ResourceTag::class, 'id', 'name');
            });
    }

    private function save() {
        $dto = new TagDto([
            'tagId' => request()->input('id') ? request()->input('id') : 0,
            'name' => request()->input('name', ''),
            'priority' => request()->input('priority'),
            'resourceTagIds' => request()->input('resource_tag_ids') ? explode(',', request()->input('resource_tag_ids')) : []
        ]);

        $this->tagService->updateOrCreateTag($dto);

        return $this->form()
            ->response()
            ->redirect('tag')
            ->success(trans('admin.save_succeeded'));
    }

    public function store()
    {
        return $this->save();
    }

    public function update($id)
    {
        return $this->save();
    }


    public function title(): string
    {
        return admin_trans_field('tag');
    }

    public function routeName(): string
    {
        return 'tag';
    }
}
