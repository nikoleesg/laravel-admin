<?php

use Encore\Admin\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Column;
use Encore\Admin\Grid\Displayers\Modal;
use Illuminate\Contracts\Support\Renderable;
use Tests\Models\User;

/**
 * Package route names are always `admin.*`; only the URL changes with
 * `ADMIN_ROUTE_PREFIX`. Covers issue #12 with a non-default prefix.
 */
class RoutePrefixTest extends TestCase
{
    protected $adminRoutePrefix = 'backend';

    /**
     * The URL path the admin is served under for this prefix.
     *
     * @param  string  $path
     * @return string
     */
    protected function adminPath($path = '')
    {
        return admin_base_path($path);
    }

    public function testRouteNamesAreNotCoupledToUrlPrefix()
    {
        $this->assertSame('admin.handle-selectable', admin_get_route('handle-selectable'));
        $this->assertSame('admin.handle-renderable', admin_get_route('handle-renderable'));
    }

    public function testPackageRoutesResolveUnderPrefix()
    {
        $this->assertSame(
            url($this->adminPath('_handle_selectable_')),
            route(admin_get_route('handle-selectable'))
        );

        $this->assertSame(
            url($this->adminPath('_handle_renderable_')),
            route(admin_get_route('handle-renderable'))
        );

        $this->assertSame(url($this->adminPath('auth/login')), route('admin.login'));
        $this->assertSame(url($this->adminPath('auth/users')), route('admin.auth.users.index'));
    }

    public function testPublishedRoutesFileNamesRoutesWithAdminPrefix()
    {
        $this->assertSame(url($this->adminPath()), route('admin.home'));
    }

    public function testModalDisplayerRendersUnderPrefix()
    {
        $grid = new Grid(new User);
        $column = new Column('id', 'ID');
        $column->setGrid($grid);

        $displayer = new Modal('1', $grid, $column, User::first() ?: new User);

        $displayer->display(RoutePrefixRenderable::class);

        // The load URL is emitted into the page script, not the markup.
        $this->assertStringContainsString(
            $this->adminPath('_handle_renderable_'),
            Admin::script()->render()
        );
    }

    public function testAdminIsServedUnderPrefix()
    {
        $this->visit($this->adminPath('auth/login'))
            ->see('login')
            ->submitForm('Login', ['username' => 'admin', 'password' => 'admin'])
            ->seeIsAuthenticated('admin')
            ->seePageIs($this->adminPath())
            ->see('Dashboard');

        $this->visit($this->adminPath('auth/users'))
            ->see('Administrator');
    }
}

class RoutePrefixRenderable implements Renderable
{
    public function render()
    {
        return 'rendered';
    }
}
