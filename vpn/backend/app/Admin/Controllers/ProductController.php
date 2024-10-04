<?php

namespace App\Admin\Controllers;

use App\Admin\Components\Tables\CategoryTable;
use App\Dtos\FileDto;
use App\Dtos\ProductDto;
use App\Models\Category;
use App\Models\File;
use App\Models\Product;
use App\Services\ProductService;
use Dcat\Admin\Exception\InvalidArgumentException;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Show;

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
                $grid->column('category.name', 'Category');
                $grid->column('name', 'Name');
                $grid->column('order_num_allowance', 'Orders Allowance');
                $grid->column('frequency', 'Frequency');
                $grid->column('unit_price', 'Price');
                $grid->column('currency_name', 'Currency');
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
        return Show::make($id, Product::query(), function (Show $show) {
                $show->disableDeleteButton();

                $show->field('id');
                $show->field('name', 'Name');
                $show->field('created_at', 'Created');
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

                $form->selectTable('category_id', 'Category')
                    ->title('Please select a category')
                    ->from(CategoryTable::make())
                    ->model(Category::class, 'id', 'name')
                    ->required();

                $form->text('name', 'Name')->required();
                $form->textarea('description', 'Description')->required();
                $form->text('order_num_allowance', 'Order Number Allowance (Null = Infinite)');
                $form->radio('frequency', 'Frequency')
                    ->required()
                    ->options([
                        Product::WEEKLY => Product::WEEKLY,
                        Product::MONTHLY => Product::MONTHLY,
                        Product::QUARTERLY => Product::QUARTERLY,
                        Product::YEARLY => Product::YEARLY
                    ]);

                $form->select('currency_name', 'Currencies')
                    ->options([
                        env('CURRENCY_CASH') => env('CURRENCY_CASH'),
                        env('CURRENCY_POINTS') => env('CURRENCY_POINTS'),
                    ])
                    ->required();

                $form->text('unit_price', 'Unit Price')->required();

                $form->image('thumbnail_file_path', 'Thumbnail Image')
                    ->required()
                    ->url('ajax/upload')
                    ->autoUpload()
                    ->removable();

                $form->multipleImage('product_image_paths', 'Product Images')
                    ->required()
                    ->url('ajax/upload')
                    ->autoUpload()
                    ->removable();
            });
    }

    private function save(): JsonResponse {
        try {
            if (empty(request()->input('name'))) {
                return new JsonResponse();
            }

            $productFileDtos = [];

            $thumbnailFileDto = FileDto::createFileDto(request()->input('thumbnail_file_path'), File::TYPE_PUBLIC_BUCKET);

            foreach (explode(',' , request()->input('product_image_paths')) as $productImagePath) {
                $productFileDtos[] = FileDto::createFileDto($productImagePath, File::TYPE_PUBLIC_BUCKET);
            }

            $productDto = new ProductDto([
                'productId' => request()->input('id') ? request()->input('id') : 0,
                'name' => request()->input('name'),
                'description' => request()->input('description'),
                'currencyName' => request()->input('currency_name'),
                'unitPrice' => request()->input('unit_price'),
                'frequency' => request()->input('frequency'),
                'thumbnailFileDto' => $thumbnailFileDto,
                'imageFileDtos' => $productFileDtos,
                'categoryId' => request()->input('category_id'),
                'orderNumAllowance' => request()->input('order_num_allowance')
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
        return 'Product';
    }

    public function routeName(): string
    {
        return 'product';
    }
}
