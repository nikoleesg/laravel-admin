<?php

require_once __DIR__.'/RoutePrefixTest.php';

/**
 * `ADMIN_ROUTE_PREFIX=` serves the admin from the site root; route names must
 * still be `admin.*` (and not `.home` from the published routes file).
 */
class EmptyRoutePrefixTest extends RoutePrefixTest
{
    protected $adminRoutePrefix = '';

    public function testAdminIsServedFromRoot()
    {
        $this->assertSame('/', admin_base_path());
        $this->assertSame(url('/'), route('admin.home'));
        $this->assertSame(url('/_handle_selectable_'), route(admin_get_route('handle-selectable')));
    }
}
