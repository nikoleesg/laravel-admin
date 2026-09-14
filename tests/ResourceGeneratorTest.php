<?php

use Encore\Admin\Console\ResourceGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\Models\Profile;

class ResourceGeneratorTest extends TestCase
{
    public function testMakeCommandOutputsResourceCode()
    {
        Artisan::call('admin:make', [
            'name' => 'ProfileController',
            '--model' => Profile::class,
            '--output' => true,
        ]);

        $output = Artisan::output();

        $this->assertStringContainsString("\$grid->column('id', __('Id'));", $output);
        $this->assertStringContainsString("\$grid->column('first_name', __('First name'));", $output);
        $this->assertStringContainsString("\$show->field('start_at', __('Start at'));", $output);

        $this->assertStringContainsString("\$form->text('first_name', __('First name'));", $output);
        $this->assertStringContainsString("\$form->color('color', __('Color'));", $output);
        $this->assertStringContainsString("\$form->datetime('start_at', __('Start at'))->default(date('Y-m-d H:i:s'));", $output);

        // Primary key and timestamps are not form fields.
        $this->assertStringNotContainsString("\$form->text('id'", $output);
        $this->assertStringNotContainsString("\$form->number('id'", $output);
        $this->assertStringNotContainsString("'created_at'", substr($output, strpos($output, '$form->')));
    }

    public function testMakeCommandWritesController()
    {
        $path = app_path('Admin/Controllers/ProfileController.php');

        Artisan::call('admin:make', ['name' => 'ProfileController', '--model' => Profile::class]);

        $this->assertFileExists($path);

        $contents = file_get_contents($path);

        $this->assertStringContainsString('class ProfileController extends AdminController', $contents);
        $this->assertStringContainsString("\$grid->column('first_name', __('First name'));", $contents);
        $this->assertStringContainsString("\$form->color('color', __('Color'));", $contents);
        $this->assertStringContainsString("\$router->resource('profiles', ProfileController::class);", Artisan::output());

        unlink($path);
    }

    public function testMakeCommandRejectsUnknownModel()
    {
        Artisan::call('admin:make', ['name' => 'FooController', '--model' => 'App\Models\Nope', '--output' => true]);

        $this->assertStringContainsString('Model does not exists', Artisan::output());
    }

    public function testFieldTypesAreMappedFromNativeSchema()
    {
        Schema::create('test_generator_columns', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('active')->default(true);
            $table->integer('qty');
            $table->bigInteger('views');
            $table->decimal('price', 8, 2);
            $table->float('ratio');
            $table->date('born_on');
            $table->time('opens_at');
            $table->dateTime('published_at');
            $table->text('body');
            $table->string('title')->default('Untitled');
            $table->string('email');
            $table->string('avatar')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $model = new class extends Model
        {
            protected $table = 'test_generator_columns';
        };

        $form = (new ResourceGenerator($model))->generateForm();

        $this->assertStringContainsString("\$form->switch('active', __('Active'))", $form);
        $this->assertStringContainsString("\$form->number('qty', __('Qty'));", $form);
        $this->assertStringContainsString("\$form->number('views', __('Views'));", $form);
        $this->assertStringContainsString("\$form->decimal('price', __('Price'));", $form);
        $this->assertStringContainsString("\$form->decimal('ratio', __('Ratio'));", $form);
        $this->assertStringContainsString("\$form->date('born_on', __('Born on'))->default(date('Y-m-d'));", $form);
        $this->assertStringContainsString("\$form->time('opens_at', __('Opens at'))->default(date('H:i:s'));", $form);
        $this->assertStringContainsString("\$form->datetime('published_at', __('Published at'))->default(date('Y-m-d H:i:s'));", $form);
        $this->assertStringContainsString("\$form->textarea('body', __('Body'));", $form);
        $this->assertStringContainsString("\$form->text('title', __('Title'))->default('Untitled');", $form);
        $this->assertStringContainsString("\$form->email('email', __('Email'));", $form);
        $this->assertStringContainsString("\$form->image('avatar', __('Avatar'));", $form);

        foreach (['id', 'created_at', 'updated_at', 'deleted_at'] as $reserved) {
            $this->assertStringNotContainsString("'{$reserved}'", $form);
        }

        $grid = (new ResourceGenerator($model))->generateGrid();
        $this->assertStringContainsString("\$grid->column('deleted_at', __('Deleted at'));", $grid);

        Schema::drop('test_generator_columns');
    }
}
