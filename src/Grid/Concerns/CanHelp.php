<?php

namespace Encore\Admin\Grid\Concerns;

use Closure;
use Encore\Admin\Grid;

trait CanHelp
{
    /**
     * Resolved help content cache. `false` means "not resolved yet".
     *
     * @var string|null|false
     */
    protected $helpContentCache = false;

    /**
     * Remove help button on grid.
     *
     * @return Grid|mixed
     */
    public function disableHelpBtn(bool $disable = true)
    {
        return $this->option('show_help_btn', ! $disable);
    }

    /**
     * If grid show help button.
     *
     * The button only shows when the controller provides help content
     * (non-null) and it has not been explicitly disabled.
     *
     * @return bool
     */
    public function showHelpBtn()
    {
        return $this->option('show_help_btn') && ! is_null($this->helpContent());
    }

    /**
     * Get the help content for the current resource, resolved (once) from the
     * controller's `helpContent()` method.
     *
     * @return string|null
     */
    public function helpContent()
    {
        if ($this->helpContentCache === false) {
            $this->helpContentCache = $this->resolveHelpContent();
        }

        return $this->helpContentCache;
    }

    /**
     * Resolve help content from the current route's controller.
     *
     * @return string|null
     */
    protected function resolveHelpContent()
    {
        $controller = optional(request()->route())->getController();

        if ($controller && method_exists($controller, 'helpContent')) {
            return Closure::bind(function () {
                return $this->helpContent();
            }, $controller, $controller)();
        }

        return null;
    }

    /**
     * Render help button.
     *
     * @return string
     */
    public function renderHelpButton()
    {
        return (new Grid\Tools\HelpButton($this))->render();
    }
}
