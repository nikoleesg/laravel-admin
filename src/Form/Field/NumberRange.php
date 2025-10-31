<?php

namespace Encore\Admin\Form\Field;

use Encore\Admin\Form\Field;

class NumberRange extends Field
{
    protected static $css = [];

    protected static $js = [];

    /**
     * Column name.
     *
     * @var array
     */
    protected $column = [];

    /**
     * Default HTML input attributes.
     * @var string[]
     */
    protected $attributes = [
        'type' => 'number',
        'step' => 'any',
    ];

    /**
     * Create a NumberRange field.
     *
     * Usage:
     *      $form->numberRange('min_', 'max_', 'Number Range');
     *
     * @param $column
     * @param $arguments
     */
    public function __construct($column, $arguments)
    {
        $this->column['min'] = $column;
        $this->column['max'] = $arguments[0];

        array_shift($arguments);
        $this->label = $this->formatLabel($arguments);
        $this->id = $this->formatId($this->column);
    }

    /**
     * Set min value of number field.
     *
     * @param int $value
     *
     * @return $this
     */
    public function min($value)
    {
        $this->attribute('min', $value);

        return $this;
    }

    /**
     * Set max value of number field.
     *
     * @param int $value
     *
     * @return $this
     */
    public function max($value)
    {
        $this->attribute('max', $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare($value)
    {
        if (is_array($value)) {
            foreach ($value as &$v) {
                $v = $v === '' ? null : $v;
            }
        }

        return $value;
    }

    public function render()
    {
        $class = $this->getElementClassSelector();

        return parent::render();
    }
}
