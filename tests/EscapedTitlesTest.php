<?php

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Column;
use Encore\Admin\Grid\Displayers\Lightbox;
use Encore\Admin\Widgets\Box;
use Illuminate\Support\HtmlString;
use Tests\Models\User as UserModel;

/**
 * Titles must go through Blade's escaping so model data can never inject
 * markup; callers that intentionally pass HTML opt in with an HtmlString.
 */
class EscapedTitlesTest extends TestCase
{
    protected $payload = '<img src=x onerror="alert(1)">';

    protected $escaped = '&lt;img src=x onerror=&quot;alert(1)&quot;&gt;';

    protected $safeHtml = '<i class="fa fa-user"></i> Users';

    protected function setUp(): void
    {
        parent::setUp();

        $this->be(Administrator::first(), 'admin');

        request()->setRouteResolver(function () {
            return null;
        });
    }

    /**
     * Views that render a `$title` variable directly.
     */
    public static function titleViews()
    {
        return [
            'widgets/box' => ['admin::widgets.box', ['content' => '', 'footer' => '', 'attributes' => '', 'tools' => [], 'script' => '']],
            'widgets/alert' => ['admin::widgets.alert', ['content' => '', 'attributes' => '', 'icon' => 'ban']],
            'widgets/callout' => ['admin::widgets.callout', ['content' => '', 'attributes' => '']],
            'widgets/tab' => ['admin::widgets.tab', ['attributes' => '', 'tabs' => [], 'dropDown' => [], 'active' => 0]],
            'show/panel' => ['admin::show.panel', ['fields' => [], 'tools' => '', 'style' => 'info']],
            'components/column-modal' => ['admin::components.column-modal', ['name' => 'n', 'key' => 1, 'value' => 'v', 'html' => '', 'grid' => false, 'async' => false, 'url' => '']],
            'actions/form/modal' => ['admin::actions.form.modal', ['modal_id' => 'm', 'modal_size' => '', 'fields' => [], 'disable_close' => false, 'disable_submit' => false]],
        ];
    }

    /**
     * @dataProvider titleViews
     */
    public function testTitleIsEscaped($view, array $data)
    {
        $html = view($view, $data + ['title' => $this->payload])->render();

        $this->assertStringContainsString($this->escaped, $html);
        $this->assertStringNotContainsString($this->payload, $html);
    }

    /**
     * @dataProvider titleViews
     */
    public function testHtmlStringTitleIsLeftIntact($view, array $data)
    {
        $html = view($view, $data + ['title' => new HtmlString($this->safeHtml)])->render();

        $this->assertStringContainsString($this->safeHtml, $html);
    }

    public function testTableDisplayerEscapesColumnTitles()
    {
        $html = view('admin::grid.displayer.table', [
            'titles' => ['x' => $this->payload, 'y' => new HtmlString($this->safeHtml)],
            'data' => [],
        ])->render();

        $this->assertStringContainsString($this->escaped, $html);
        $this->assertStringNotContainsString($this->payload, $html);
        $this->assertStringContainsString($this->safeHtml, $html);
    }

    public function testBoxWidgetEscapesTitleByDefault()
    {
        $html = (new Box($this->payload, 'body'))->render();

        $this->assertStringContainsString($this->escaped, $html);
        $this->assertStringNotContainsString($this->payload, $html);
    }

    /**
     * @dataProvider gridViews
     */
    public function testGridTitleIsEscaped($fixed)
    {
        $grid = new Grid(new UserModel);

        if ($fixed) {
            $grid->fixColumns(1);
        }

        $grid->setTitle($this->payload);

        // Admin::component() re-serialises the view through DOMDocument, which
        // turns `&quot;` in text nodes back into `"`; only the tag matters here.
        $html = $grid->render();

        $this->assertStringContainsString('&lt;img src=x onerror=', $html);
        $this->assertStringNotContainsString('<img src=x', $html);
    }

    public static function gridViews()
    {
        return [
            'grid/table' => [false],
            'grid/fixed-table' => [true],
        ];
    }

    public function testLightboxEscapesSrcAttribute()
    {
        $path = "x' onerror='alert(1)";
        $grid = new Grid(new UserModel);
        $column = new Column('avatar', 'Avatar');
        $column->setGrid($grid);

        $html = (new Lightbox($path, $grid, $column, new UserModel))->display(['server' => 'https://cdn.test']);

        $this->assertStringNotContainsString("x' onerror='alert(1)", $html);
        $this->assertStringContainsString('https://cdn.test/x&#039; onerror=&#039;alert(1)', $html);
    }
}
