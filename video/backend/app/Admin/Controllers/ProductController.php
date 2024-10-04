<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\MediaTable;
use App\Admin\Components\Tables\PublicFileTable;
use App\Admin\Components\Tables\UserTable;
use App\Dtos\BucketFileDto;
use App\Dtos\FileDto;
use App\Dtos\ProductDto;
use App\Models\File;
use App\Models\Media;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\ProductService;
use Dcat\Admin\Exception\InvalidArgumentException;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Show;
use Illuminate\Support\Str;

class ProductController extends AdminController
{

    private ProductService $productService;

    public function __construct(ProductService $productService) {
        $this->productService = $productService;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid(): Grid
    {
        return Grid::make(Product::query(),
            function (Grid $grid) {
                $grid->disableRowSelector();
                $grid->disableRefreshButton();
                $grid->disableFilterButton();

                $grid->column('id')->sortable();
                $grid->column('name');
                $grid->column('is_active')->bool();
                $grid->column('order_num_allowance');
                $grid->column('type')->display(function ($type) {
                    return admin_trans_option($type, 'product_types');
                });
                $grid->column('owner');
                $grid->column('currency_name', admin_trans_field('currency'));
                $grid->column('unit_price', admin_trans_field('price'));
                $grid->column('updated_at_formatted');
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
        return Show::make($id, Product::query(), function (Show $show) {
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('name');
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
        return Form::make(Product::query(),
            function (Form $form) {
                $form->hidden('id');
                $form->text('name')->required();
                $form->textarea('description')->required();
                $form->text('order_num_allowance', admin_trans_label('order_num_allowance_nil'));

                $form->switch('is_active');
                $form->selectTable('user_id', admin_trans_field('user'))
                    ->help(admin_trans_label('platform_product_leave_empty'))
                    ->title(admin_trans_label('select_user'))
                    ->from(UserTable::make())
                    ->model(User::class, 'id', 'nickname');

                $form->radio('type', admin_trans_field('product_type'))
                    ->options([
                        Product::TYPE_MEMBERSHIP => admin_trans_option('membership', 'product_types'),
                        Product::TYPE_MEDIA => admin_trans_option('media', 'product_types'),
                        Product::TYPE_SUBSCRIPTION => admin_trans_option('subscription', 'product_types'),
                        Product::TYPE_GENERAL => admin_trans_option('general', 'product_types'),
                    ])
                    ->required()
                    ->when(Product::TYPE_MEMBERSHIP, function(Form $form) {
                        $form->radio('role_id', admin_trans_field('roles'))
                            ->options([
                                Role::ROLE_MEMBERSHIP_ID => admin_trans_option('membership', 'roles'),
                                Role::ROLE_AGENT_ID => admin_trans_option('agent', 'roles'),
                            ]);
                    })
                    ->when(Product::TYPE_MEDIA, function(Form $form) {
                        $form->selectTable('media_id', admin_trans_field('media'))
                            ->title(admin_trans_label('select_media'))
                            ->from(MediaTable::make())
                            ->model(Media::class, 'id', 'name');
                    })
                    ->when(Product::TYPE_SUBSCRIPTION, function(Form $form) {
                        $form->selectTable('publisher_user_id', admin_trans_field('publisher'))
                            ->title(admin_trans_label('select_publisher'))
                            ->from(UserTable::make())
                            ->model(User::class, 'id', 'nickname');
                    });

                $form->radio('frequency', admin_trans_field('product_frequency'))
                    ->required()
                    ->options([
                        Product::ONETIME => admin_trans_option('onetime', 'subscription_frequency'),
                        Product::MONTHLY => admin_trans_option('monthly', 'subscription_frequency'),
                        Product::QUARTERLY => admin_trans_option('quarterly', 'subscription_frequency'),
                        Product::YEARLY => admin_trans_option('yearly', 'subscription_frequency'),
                    ])
                    ->default(Product::ONETIME);

                $form->select('currency_name', admin_trans_field('currency'))
                    ->options([
                        env('CURRENCY_CASH') => env('CURRENCY_CASH'),
                        env('CURRENCY_POINTS') => env('CURRENCY_POINTS'),
                    ])
                    ->required();

                $form->text('unit_price', admin_trans_field('unit_price'))->required();


                $form->radio('file_type', admin_trans_field('file_type'))
                    ->help(admin_trans_label('upload_cover_cloud'))
                    ->required()
                    ->options([
                        'upload' => admin_trans_option('Upload', 'type'),
                        'cloud' => admin_trans_option('Cloud', 'type'),
                    ])
                    ->default('upload')
                    ->when('upload', function (Form $form) {
                        $form->image('thumbnail_file_path', admin_trans_field('thumbnail_file'))
                            ->url('ajax/upload')
                            ->autoUpload()
                            ->removable();
                        $form->multipleImage('product_image_paths', admin_trans_field('product_image'))
                            ->url('ajax/upload')
                            ->autoUpload()
                            ->removable();
                    })
                    ->when('cloud', function (Form $form) {
                        $form->selectTable('thumbnailFile.id', admin_trans_field('thumbnail_file'))
                            ->title(admin_trans_label('select_thumbnail'))
                            ->from(PublicFileTable::make())
                            ->model(File::class, 'id', 'name');
                        $form->multipleSelectTable('product_image_ids', admin_trans_field('product_image'))
                            ->title(admin_trans_label('select_product_image'))
                            ->from(PublicFileTable::make())
                            ->model(File::class, 'id', 'name');
                    });
            });
    }

    private function save(): JsonResponse {
        try {
            if (empty(request()->input('name'))) {
                return new JsonResponse();
            }

            $productFileDtos = [];

            if (request()->input('file_type') === 'cloud') {
                $thumbnailFile = File::find(request()->input('thumbnailFile')['id']);
                $thumbnailFileDto = new BucketFileDto([
                    'fileId' => $thumbnailFile->id,
                    'bucketFilePath' => $thumbnailFile->bucket_file_path,
                    'bucketName' => $thumbnailFile->bucket_name,
                    'bucketType' => $thumbnailFile->bucket_type,
                ]);

                if (request()->input('product_image_ids')) {
                    foreach (explode(',' , request()->input('product_image_ids')) as $productImageId) {
                        $productFile = File::find($productImageId);
                        $productFileDtos[] = new BucketFileDto([
                            'fileId' => $productImageId,
                            'bucketFilePath' => $productFile->bucket_file_path,
                            'bucketName' => $productFile->bucket_name,
                            'bucketType' => $productFile->bucket_type,
                        ]);
                    }
                }
            } else {
                $thumbnailFileDto = FileDto::createFileDto(request()->input('thumbnail_file_path'), File::TYPE_PUBLIC_BUCKET);
                if (request()->input('product_image_paths')) {
                    foreach (explode(',' , request()->input('product_image_paths')) as $productImagePath) {
                        $productFileDtos[] = FileDto::createFileDto($productImagePath, File::TYPE_PUBLIC_BUCKET);
                    }
                }
            }

            $productDto = ProductDto::create([
                'productId' => request()->input('id') ? request()->input('id') : 0,
                'userId' => request()->input('user_id'),
                'type' => request()->input('type'),
                'name' => request()->input('name'),
                'description' => request()->input('description'),
                'publisherUserId' => request()->input('publisher_user_id'),
                'mediaId' => request()->input('media_id'),
                'roleId' => request()->input('role_id'),
                'currencyName' => request()->input('currency_name'),
                'unitPrice' => request()->input('unit_price'),
                'frequency' => request()->input('frequency'),
                'thumbnailFileDto' => $thumbnailFileDto,
                'imageFileDtos' => $productFileDtos,
                'fileType' => request()->input('file_type'),
                'orderNumAllowance' => request()->input('order_num_allowance'),
                'isActive' => request()->input('is_active'),
            ]);

            $this->productService->updateOrCreateProduct($productDto);
        } catch (\Error | \Exception $e) {
            throw new InvalidArgumentException('Invalid Inputs');
        }

        return $this->form()
            ->response()
            ->redirect('product/')
            ->success(trans('admin.save_succeeded'));
    }

    public function store(): JsonResponse
    {
        return $this->save();
    }

    public function update($id): JsonResponse
    {
        return $this->save();
    }

    public function title(): string
    {
        return admin_trans_label('product');
    }

    public function routeName(): string
    {
        return 'product';
    }
}
