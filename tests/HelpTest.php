<?php

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Grid;
use Tests\Models\User as UserModel;

class HelpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->be(Administrator::first(), 'admin');
    }

    public function testHelpButtonHiddenWhenNoHelpContent()
    {
        // UserController does not override helpContent(), so it stays null.
        $this->visit('admin/users')
            ->dontSeeElement('.grid-help-btn');
    }

    public function testHelpButtonShownWithOverriddenContent()
    {
        $this->visit('admin/help-test')
            ->see('Custom help HTML')
            ->seeElement('.grid-help-btn button[data-toggle=modal]')
            ->seeElement('#custom-help-content');
    }

    public function testHelpButtonRendersClosableModal()
    {
        $this->visit('admin/help-test')
            ->seeElement('.modal .modal-header button.close[data-dismiss=modal]')
            ->seeElement('.modal .modal-body');
    }

    public function testDisableHelpBtnHidesButtonEvenWithContent()
    {
        // helpContent() returns HTML, but the grid calls disableHelpBtn().
        $this->visit('admin/help-test?disable_help=1')
            ->dontSeeElement('.grid-help-btn')
            ->dontSee('Custom help HTML');
    }

    public function testHelpButtonHiddenWithoutControllerContext()
    {
        // No controller resolves help content (e.g. a grid outside an admin
        // resource), so the button stays hidden.
        request()->setRouteResolver(function () {
            return null;
        });

        $grid = new Grid(new UserModel());

        $this->assertNull($grid->helpContent());
        $this->assertFalse($grid->showHelpBtn());
        $this->assertEmpty($grid->renderHelpButton());
    }
}
