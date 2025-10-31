<?php

namespace Encore\Admin\Grid\Displayers;

class ConditionalFormatting extends AbstractDisplayer
{
    public function display(string $colour = '#FF0000', $conditionalCallBack = null)
    {
        $model = $this->row;

        if (is_callable($conditionalCallBack) && call_user_func($conditionalCallBack, $model)) {
            return "<span style='color: $colour'>{$this->value}</span>";
        }

        return $this->value;
    }
}