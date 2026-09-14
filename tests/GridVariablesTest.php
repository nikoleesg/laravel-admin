<?php

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Grid;
use Tests\Models\User as UserModel;

/**
 * Grid::with() must merge into the existing view variables, so anything set
 * before a view swap (fixColumns() calls setView() with its own variables)
 * survives.
 */
class GridVariablesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->be(Administrator::first(), 'admin');

        request()->setRouteResolver(function () {
            return null;
        });
    }

    public function testWithMergesVariables()
    {
        $grid = new Grid(new UserModel);

        $grid->with(['foo' => 'bar']);
        $grid->with(['baz' => 'qux']);
        $grid->with(['foo' => 'overridden']);

        $variables = (function () {
            return $this->variables();
        })->call($grid);

        $this->assertSame('overridden', $variables['foo']);
        $this->assertSame('qux', $variables['baz']);
    }

    public function testTitleSurvivesFixColumns()
    {
        $grid = new Grid(new UserModel);

        $grid->setTitle('Users grid title');
        $grid->fixColumns(1);

        $html = $grid->render();

        $this->assertStringContainsString('<h3 class="box-title"> Users grid title</h3>', $html);
        $this->assertStringContainsString('table-fixed', $html);
    }

    public function testFixColumnsVariablesAreStillSet()
    {
        $grid = new Grid(new UserModel);

        $grid->with(['foo' => 'bar']);
        $grid->fixColumns(1);

        $variables = (function () {
            return $this->variables();
        })->call($grid);

        $this->assertSame('bar', $variables['foo']);
        $this->assertArrayHasKey('allName', $variables);
        $this->assertArrayHasKey('rowName', $variables);
    }
}
