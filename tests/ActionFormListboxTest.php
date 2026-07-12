<?php

use Encore\Admin\Actions\Action;
use Encore\Admin\Admin;
use Encore\Admin\Form\Field\Listbox;

class ActionFormListboxTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Admin::$html = [];
        Admin::$script = [];
    }

    public function testListboxIsCallableAndReturnsListboxField()
    {
        $action = $this->makeAction();

        $field = $action->listbox('roles', 'Roles')->options([1 => 'Admin', 'editor' => 'Editor']);

        $this->assertInstanceOf(Listbox::class, $field);
    }

    public function testListboxDoesNotThrowBadMethodCallException()
    {
        $action = $this->makeAction();

        try {
            $field = $action->listbox('roles', 'Roles')->options([1 => 'Admin', 'editor' => 'Editor']);
            $this->assertInstanceOf(Listbox::class, $field);
        } catch (BadMethodCallException $exception) {
            $this->fail('Action form listbox should be callable without BadMethodCallException: '.$exception->getMessage());
        }
    }

    public function testListboxRendersMultipleSelectAndHiddenInput()
    {
        $html = $this->renderActionForm($this->makeAction([1 => 'Admin', 'editor' => 'Editor']));

        $this->assertMatchesRegularExpression('/<select\b(?=[^>]*name="roles\[\]")(?=[^>]*\bmultiple\b)/', $html);
        $this->assertMatchesRegularExpression('/<input\b(?=[^>]*type="hidden")(?=[^>]*name="roles\[\]")/', $html);
    }

    public function testListboxPreservesNumericAndStringOptionValuesAndLabels()
    {
        $html = $this->renderActionForm($this->makeAction([1 => 'Admin', 'editor' => 'Editor']));

        $this->assertStringContainsString('<option value="1">Admin</option>', $html);
        $this->assertStringContainsString('<option value="editor">Editor</option>', $html);
    }

    public function testListboxRendersEmptyOptionsWithoutException()
    {
        $html = $this->renderActionForm($this->makeAction([]));

        $this->assertMatchesRegularExpression('/<select\b(?=[^>]*name="roles\[\]")(?=[^>]*\bmultiple\b)/', $html);
        $this->assertStringNotContainsString('<option', $html);
    }

    public function testListboxRendersSelectedValues()
    {
        $html = $this->renderActionForm($this->makeActionWithValues([1 => 'Admin', 'editor' => 'Editor'], [1, 'editor']));

        $this->assertStringContainsString('<option value="1" selected>Admin</option>', $html);
        $this->assertStringContainsString('<option value="editor" selected>Editor</option>', $html);
    }

    public function testListboxRendersDefaultValues()
    {
        $html = $this->renderActionForm($this->makeActionWithDefaults([1 => 'Admin', 'editor' => 'Editor'], [1, 'editor']));

        $this->assertStringContainsString('<option value="1" selected>Admin</option>', $html);
        $this->assertStringContainsString('<option value="editor" selected>Editor</option>', $html);
    }

    public function testUnknownActionFormMethodsStillThrowBadMethodCallException()
    {
        $this->expectException(BadMethodCallException::class);

        $this->makeAction()->unknownListboxField('roles', 'Roles');
    }

    protected function renderActionForm(Action $action)
    {
        Admin::$html = [];
        Admin::$script = [];

        $action->render();

        return implode('', Admin::$html);
    }

    protected function makeAction(array $options = [1 => 'Admin', 'editor' => 'Editor'])
    {
        return new class($options) extends Action {
            protected $options;

            public function __construct(array $options)
            {
                $this->options = $options;
                $this->selector = '.action-listbox-test-'.spl_object_id($this);

                parent::__construct();
            }

            public function form()
            {
                return $this->listbox('roles', 'Roles')->options($this->options);
            }

            public function html()
            {
                $class = ltrim($this->selector($this->selectorPrefix), '.');

                return '<a class="'.$class.'">Listbox</a>';
            }
        };
    }

    protected function makeActionWithValues(array $options, array $values)
    {
        return new class($options, $values) extends Action {
            protected $options;
            protected $values;

            public function __construct(array $options, array $values)
            {
                $this->options = $options;
                $this->values = $values;
                $this->selector = '.action-listbox-test-'.spl_object_id($this);

                parent::__construct();
            }

            public function form()
            {
                return $this->listbox('roles', 'Roles')->options($this->options)->value($this->values);
            }

            public function html()
            {
                $class = ltrim($this->selector($this->selectorPrefix), '.');

                return '<a class="'.$class.'">Listbox</a>';
            }
        };
    }

    protected function makeActionWithDefaults(array $options, array $defaults)
    {
        return new class($options, $defaults) extends Action {
            protected $options;
            protected $defaults;

            public function __construct(array $options, array $defaults)
            {
                $this->options = $options;
                $this->defaults = $defaults;
                $this->selector = '.action-listbox-test-'.spl_object_id($this);

                parent::__construct();
            }

            public function form()
            {
                return $this->listbox('roles', 'Roles')->options($this->options)->default($this->defaults);
            }

            public function html()
            {
                $class = ltrim($this->selector($this->selectorPrefix), '.');

                return '<a class="'.$class.'">Listbox</a>';
            }
        };
    }
}
