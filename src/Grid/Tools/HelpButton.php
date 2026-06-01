<?php

namespace Encore\Admin\Grid\Tools;

use Encore\Admin\Admin;
use Encore\Admin\Grid;

class HelpButton extends AbstractTool
{
    /**
     * Create a new HelpButton instance.
     */
    public function __construct(Grid $grid)
    {
        $this->grid = $grid;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function render()
    {
        if (! $this->grid->showHelpBtn()) {
            return '';
        }

        return Admin::component('admin::components.grid-help', [
            'modal_id' => 'help-modal-'.$this->grid->tableID,
            'title' => trans('admin.help_button'),
            'content' => $this->grid->helpContent(),
        ]);
    }
}
