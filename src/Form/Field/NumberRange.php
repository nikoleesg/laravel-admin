<?php

namespace Encore\Admin\Form\Field;

use Encore\Admin\Form\Field;

class NumberRange extends Field
{
    /**
     * Column name.
     *
     * @var array
     */
    protected $column = [];

    public function __construct($column, $arguments)
    {
        $this->column['start'] = $column;
        $this->column['end'] = $arguments[0];

        array_shift($arguments);
        $this->label = $this->formatLabel($arguments);
        $this->id = $this->formatId($this->column);

        $this->attribute('step', 'any');
    }

    /**
     * {@inheritdoc}
     */
    public function prepare($value)
    {
        if (is_array($value)) {
            return array_map(fn ($item) => $item === '' ? null : $item, $value);
        }

        return $value === '' ? null : $value;
    }

    /**
     * Set the minimum value accepted by both inputs.
     *
     * @param  int|float  $value
     * @return $this
     */
    public function min($value)
    {
        return $this->attribute('min', $value);
    }

    /**
     * Set the maximum value accepted by both inputs.
     *
     * @param  int|float  $value
     * @return $this
     */
    public function max($value)
    {
        return $this->attribute('max', $value);
    }

    /**
     * Set the step increment accepted by both inputs.
     *
     * @param  int|float|string  $value
     * @return $this
     */
    public function step($value)
    {
        return $this->attribute('step', $value);
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $class = $this->getElementClassSelector();

        $this->script = <<<EOT
            $("{$class['start']}").on("change", function () {
                var val = $(this).val();
                if (val !== "") {
                    $('{$class['end']}').attr("min", val);
                }
            });
            $("{$class['end']}").on("change", function () {
                var val = $(this).val();
                if (val !== "") {
                    $('{$class['start']}').attr("max", val);
                }
            });
EOT;

        return parent::render();
    }
}
