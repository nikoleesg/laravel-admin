<?php

namespace Encore\Admin\Form\Field;

use Encore\Admin\Form\Field;
use Illuminate\Support\Arr;

class DateRangePicker extends Field
{
    protected $view = 'admin::daterangepicker.daterangepicker';

    protected static $css = [
        'vendor/laravel-admin/daterangepicker/daterangepicker.css',
    ];

    protected static $js = [
        'vendor/laravel-admin/daterangepicker/daterangepicker.js',
    ];

    protected $format = 'YYYY-MM-DD';

    protected $multiple = false;

    public function __construct($column, $arguments = [])
    {
        if (is_string($column)) {
            parent::__construct($column, $arguments);

            return;
        }

        if (is_array($column)) {
            $this->column = [];
            $this->column['start'] = $column[0];
            $this->column['end'] = $column[1];

            $this->label = $this->formatLabel($arguments);

            $this->id = $this->formatId($this->column);

            $this->multiple = true;
        }
    }

    public function ranges($ranges = [])
    {
        return $this->options(compact('ranges'));
    }

    public function format($format)
    {
        $this->format = $format;

        return $this;
    }

    public function render()
    {
        Arr::set($this->options, 'locale.format', $this->format);

        $config = config('admin.extensions.daterangepicker.config', []);

        $options = json_encode(array_merge($config, $this->options));

        $locale = config('app.locale');

        $classSelector = implode('_', $this->getElementClass());

        $this->script = <<<SCRIPT

moment.locale('$locale');

$('.{$classSelector}').daterangepicker($options);

SCRIPT;

        if ($this->multiple) {
            $this->script .= <<<SCRIPT
$('.{$classSelector}').on('apply.daterangepicker', function(ev, picker) {
  var range = $('.{$classSelector}').val().split(' - ');
  $('#{$this->id['start']}').val(range[0]);
  $('#{$this->id['end']}').val(range[1]);
});
SCRIPT;
        }

        $this->value['range'] = implode(' - ', $this->value());
        $this->column['range'] = implode('_', $this->column);

        return parent::render()->with(['multiple' => $this->multiple]);
    }
}
