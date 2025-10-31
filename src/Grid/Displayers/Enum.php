<?php

namespace Encore\Admin\Grid\Displayers;

class Enum extends AbstractDisplayer
{
    public function display()
    {
        return $this->value?->label;
    }
}