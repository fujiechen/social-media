<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\PrivateFileTable;
use App\Dtos\TutorialDto;
use App\Dtos\TutorialFileDto;
use App\Models\File;
use App\Models\Tutorial;
use App\Services\TutorialService;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class TutorialController extends AdminController
{

    private TutorialService $tutorialService;

    public function __construct(TutorialService $tutorialService) {
        $this->tutorialService = $tutorialService;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Tutorial::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->column('id')->sortable();
                $grid->column('name', 'Name');
                $grid->column('os', 'OS');
                $grid->column('updated_at_formatted', 'Updated');
                $grid->column('created_at_formatted', 'Created');
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
        return Show::make($id, Tutorial::query(),
            function (Show $show) {
                $show->disableEditButton();
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('name');
                $show->field('os');
                $show->field('updated_at_formatted', 'Updated');
                $show->field('created_at_formatted', 'Created');
                $show->html('<hr/>');
                $show->html($show->model()->content);
            });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form(): Form
    {
        return Form::make(Tutorial::query(),
            function (Form $form) {
                $form->display('id');
                $form->hidden('id');
                $form->text('name', 'Name')->required();
                $form->select('os', 'Os')
                    ->options([
                        Tutorial::OS_WIN => Tutorial::OS_WIN,
                        Tutorial::OS_MAC => Tutorial::OS_MAC,
                        Tutorial::OS_IOS => Tutorial::OS_IOS,
                        Tutorial::OS_ANDROID => Tutorial::OS_ANDROID,
                        Tutorial::OS_SHARE => Tutorial::OS_SHARE,
                    ])
                    ->required();

                $form->table('tutorialFiles', function (Form\NestedForm $table) {
                    $table->text('name', 'Name');
                    $table->selectTable('file_id', 'File')
                        ->from(PrivateFileTable::make())
                        ->model(File::class, 'id', 'name')
                        ->width('30%');
                });

                $form->textarea('content', 'Content')->required();

            });
    }


    private function save()
    {
        $tutorialFileDtos = [];
        $tutorialFiles = request()->input('tutorialFiles', []);

        foreach ($tutorialFiles as $tutorialFile) {
            if (isset($tutorialFile['_remove_']) && $tutorialFile['_remove_'] == 1) {
                continue;
            }
            $tutorialFileDtos[] = new TutorialFileDto([
                'name' => $tutorialFile['name'],
                'fileId' => $tutorialFile['file_id'],
            ]);
        }

        $dto = new TutorialDto([
            'tutorialId' => request()->input('id') ? request()->input('id') : 0,
            'name' => request()->input('name', ''),
            'content' => request()->input('content', ''),
            'os' => request()->input('os'),
            'tutorialFileDtos' => $tutorialFileDtos,
        ]);

        $this->tutorialService->updateOrCreateTutorial($dto);

        return $this->form()
            ->response()
            ->redirect('tutorial/')
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
        return 'Tutorial';
    }

    public function routeName(): string
    {
        return 'tutorial';
    }
}
