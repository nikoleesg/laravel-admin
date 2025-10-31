<?php

namespace Encore\Admin\Widgets;

use Illuminate\Contracts\Support\Renderable;

class Button extends Widget implements Renderable
{
    /**
     * @var string
     */
    protected $view = 'admin::widgets.button';

    /**
     * @var string
     */
    protected string $title = '';

    /**
     * Font Awesome icon
     *
     * @var string
     */
    protected string $icon = 'gear';

    /**
     * @var string
     */
    protected string $link = '';

    /**
     * Badge
     *
     * @var string
     */
    protected string $badge = '';

    /**
     * Color of badge
     * @var string
     */
    protected string $style = 'green';

    /**
     * @var string
     */
    protected $script;

    /**
     * Button constructor.
     *
     * @param string $title
     * @param string $icon
     * @param string $style
     * @param string|null $link
     * @param string|null $badge
     */
    public function __construct(
        string $title,
        string $icon,
        string $style = 'green',
        ?string $link = null,
        ?string $badge = null
    ) {
        $this->title = $title;
        $this->icon = $icon;
        $this->style = $style;

        if ($link) {
            $this->link = $link;
        }

        if ($badge) {
            $this->badge = $badge;
        }

        $this->class('btn btn-app');

        parent::__construct();
    }

    /**
     * Set button title
     *
     * @param string $title
     *
     * @return $this
     */
    public function title(string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set button icon
     *
     * @param string $icon
     *
     * @return $this
     */
    public function icon(string $icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Set button link
     *
     * @param string $link
     *
     * @return $this
     */
    public function link(string $link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Set button badge
     *
     * @param string $badge
     *
     * @return $this
     */
    public function badge(string $badge)
    {
        $this->badge = $badge;

        return $this;
    }

    /**
     * Set button style
     *
     * @param string $style
     *
     * @return $this
     */
    public function style(string $style)
    {
        $this->style = $style;

        return $this;
    }

    /**
     * Variables in view.
     *
     * @return array
     */
    protected function variables()
    {
        return [
            'title'      => $this->title,
            'icon'       => $this->icon,
            'link'       => $this->link,
            'badge'      => $this->badge,
            'style'      => $this->style,
            'attributes' => $this->formatAttributes(),
            'script'     => $this->script,
        ];
    }


    /**
     * Render button.
     *
     * @return string
     */
    public function render()
    {
        return view($this->view, $this->variables())->render();
    }
}