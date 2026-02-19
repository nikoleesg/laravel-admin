<?php

namespace Encore\Admin\Grid\Displayers;

class EnumDisplayer extends AbstractDisplayer
{
    public function display()
    {
        if (is_object($this->value) && method_exists($this->value, 'label')) {
            return $this->value->label();
        }

        return $this->value;
    }
}
