<?php

namespace Encore\Admin\Form\Field;

use Encore\Admin\Form\Field;
use Encore\Admin\Latlong\Extension;

class Latlong extends Field
{
    protected $autoPosition = false;

    protected $column = [];

    protected $view = 'admin::latlong';

    protected $height = 300;

    protected $zoom = 16;

    public static function getAssets()
    {
        return ['js' => Extension::getProvider()->getAssets()];
    }

    public function __construct($column, $arguments)
    {
        $this->column['lat'] = (string) $column;
        $this->column['lng'] = (string) $arguments[0];

        array_shift($arguments);

        $this->label = $this->formatLabel($arguments);
        $this->id = $this->formatId($this->column);
    }

    public function height(int $height)
    {
        $this->height = $height;

        return $this;
    }

    public function zoom(int $zoom)
    {
        $this->zoom = $zoom;

        return $this;
    }

    public function setAutoPosition($bool)
    {
        $this->autoPosition = $bool;

        return $this;
    }

    public function render()
    {
        $this->script = Extension::getProvider()
            ->setParams([
                'zoom' => $this->zoom,
            ])
            ->setAutoPosition($this->autoPosition)
            ->applyScript($this->id);

        $variables = [
            'height' => $this->height,
            'provider' => Extension::config('default'),
        ];

        $this->addVariables($variables);

        return parent::fieldRender();
    }
}
