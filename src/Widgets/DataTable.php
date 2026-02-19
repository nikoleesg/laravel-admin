<?php

namespace Encore\Admin\Widgets;

use Admin;
use Illuminate\Support\Arr;

class DataTable extends Widget
{
    protected $view = 'admin::datatables.index';

    protected $headers = [];

    protected $rows = [];

    protected $style = [];

    protected $options = [];

    protected static $loaded = false;

    public function __construct($headers = [], $rows = [], $style = [], $options = [])
    {
        $globalOptions = config('admin.extensions.data-table.options', []);
        $options = array_merge($globalOptions, $options);
        $options = $this->loadLanguage($options);
        $this->setHeaders($headers);
        $this->setRows($rows);
        $this->setStyle($style);
        $this->setOptions($options);
        $this->class('table dataTable '.implode(' ', $this->style));
    }

    protected static function loadAssets()
    {
        if (self::$loaded) {
            return;
        }

        Admin::css('vendor/laravel-admin/datatables/dataTables-1.10.19/dataTables.bootstrap.min.css');
        Admin::js('vendor/laravel-admin/datatables/dataTables-1.10.19/jquery.dataTables.min.js');
        Admin::js('vendor/laravel-admin/datatables/dataTables-1.10.19/dataTables.bootstrap.min.js');

        Admin::css('vendor/laravel-admin/datatables/dataTables-1.10.19/plugins/buttons/buttons.dataTables.min.css');
        Admin::js('vendor/laravel-admin/datatables/dataTables-1.10.19/plugins/buttons/dataTables.buttons.min.js');
        Admin::js('vendor/laravel-admin/datatables/dataTables-1.10.19/libs/jszip/jszip.min.js');
        Admin::js('vendor/laravel-admin/datatables/dataTables-1.10.19/libs/pdfmake/pdfmake.min.js');
        Admin::js('vendor/laravel-admin/datatables/dataTables-1.10.19/libs/pdfmake/vfs_fonts.js');
        Admin::js('vendor/laravel-admin/datatables/dataTables-1.10.19/plugins/buttons/buttons.html5.min.js');
        Admin::js('vendor/laravel-admin/datatables/dataTables-1.10.19/plugins/buttons/buttons.print.min.js');

        self::$loaded = true;
    }

    public function setHeaders($headers = [])
    {
        $this->headers = $headers;

        return $this;
    }

    public function setRows($rows = [])
    {
        if (Arr::isAssoc($rows)) {
            foreach ($rows as $key => $item) {
                $this->rows[] = [$key, $item];
            }

            return $this;
        }
        $this->rows = $rows;

        return $this;
    }

    public function setStyle($style = [])
    {
        $this->style = $style;

        return $this;
    }

    public function setOptions($options = [])
    {
        $this->options = $options;

        return $this;
    }

    public function render()
    {
        static::loadAssets();

        $vars = [
            'headers' => $this->headers,
            'rows' => $this->rows,
            'style' => $this->style,
            'attributes' => $this->formatAttributes(),
            'options' => json_encode($this->options),
        ];

        return view($this->view, $vars)->render();
    }

    protected function loadLanguage($options)
    {
        if (isset($options['language'])) {
            $language = ucfirst($options['language']);
            $file = __DIR__."/../../resources/assets/datatables/plugins/i18n/{$language}.lang";
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $content = substr($content, strpos($content, '{'));
                $language = json_decode($content, true);
                $options['language'] = $language;
            } else {
                unset($options['language']);
            }
        }

        return $options;
    }
}
