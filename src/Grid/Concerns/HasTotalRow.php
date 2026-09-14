<?php

namespace Encore\Admin\Grid\Concerns;

use Closure;
use Encore\Admin\Grid\Tools\TotalRow;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;

trait HasTotalRow
{
    /**
     * @var array
     */
    protected $totalRowColumns = [];

    /**
     * @param  string  $column
     * @param  Closure  $callback
     * @return $this
     */
    public function addTotalRow($column, $callback)
    {
        $this->totalRowColumns[$column] = $callback;

        return $this;
    }

    /**
     * @return Factory|View|string
     */
    public function renderTotalRow($columns = null)
    {
        if (empty($this->totalRowColumns)) {
            return '';
        }

        $query = $this->model()->getQueryBuilder();

        $totalRow = new TotalRow($query, $this->totalRowColumns);

        $totalRow->setGrid($this);

        if ($columns) {
            $totalRow->setVisibleColumns($columns);
        }

        return $totalRow->render();
    }
}
