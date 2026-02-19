<?php

namespace Encore\Admin\Latlong\Map;

abstract class AbstractMap
{
    protected $autoPosition = false;

    protected $api;

    protected $params;

    public function __construct($key = '')
    {
        if ($key) {
            $this->api = sprintf($this->api, $key);
        }
    }

    public function getAssets()
    {
        return [$this->api];
    }

    public function getParams($field = null)
    {
        if ($field) {
            return isset($this->params[$field]) ? $this->params[$field] : null;
        }

        return $this->params;
    }

    public function setAutoPosition($bool)
    {
        $this->autoPosition = $bool;

        return $this;
    }

    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    abstract public function applyScript(array $id);
}
