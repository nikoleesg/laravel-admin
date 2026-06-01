<?php

namespace Tests\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Tests\Models\User;

class HelpController extends AdminController
{
    protected $title = 'Help';

    /**
     * Custom help content rendered in the grid help modal.
     *
     * @return string
     */
    protected function helpContent()
    {
        return '<div id="custom-help-content">Custom help HTML</div>';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());

        $grid->id('ID');
        $grid->username();

        if (request('disable_help')) {
            $grid->disableHelpBtn();
        }

        return $grid;
    }
}
