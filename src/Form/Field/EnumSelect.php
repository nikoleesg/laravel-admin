<?php

namespace Encore\Admin\Form\Field;

use Spatie\Enum\Laravel\Enum;

class EnumSelect extends Select
{
    protected $view = 'admin::form.select';

    protected function prepareInputValue($value)
    {
        if ($value instanceof Enum) {
            return $value->value;
        }
    }

    public function render()
    {
        // Possibly override how $value is passed to the view
        $this->value = $this->prepareInputValue($this->value);
        return parent::render();
    }
}
