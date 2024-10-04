<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;

class DashboardController extends AdminController
{

    public function index(Content $content)
    {
        return response()->redirectTo('/admin/landing/');
    }


    public function title(): string
    {
        return 'Dashboard';
    }

    public function routeName(): string
    {
        return 'dashboard';
    }
}
