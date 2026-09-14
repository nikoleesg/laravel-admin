<?php

namespace Encore\Admin\Traits;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Tree;

/**
 * @deprecated
 */
trait AdminBuilder
{
    /**
     * @return Grid
     */
    public static function grid(\Closure $callback)
    {
        return new Grid(new static, $callback);
    }

    /**
     * @return Form
     */
    public static function form(\Closure $callback)
    {
        return new Form(new static, $callback);
    }

    /**
     * @return Tree
     */
    public static function tree(?\Closure $callback = null)
    {
        return new Tree(new static, $callback);
    }
}
