<?php

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\GridSortableController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Models\SortableItem;
use Tests\Models\User as UserModel;

class GridSortableControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->be(Administrator::first(), 'admin');

        foreach (['Alpha', 'Bravo', 'Charlie'] as $i => $name) {
            SortableItem::create(['name' => $name, 'order_column' => $i + 1]);
        }
    }

    /**
     * Invoke the endpoint directly, mirroring what a browser POST delivers.
     *
     * @param  mixed  $model
     * @param  mixed  $sort
     * @return JsonResponse
     */
    protected function sort($model, $sort)
    {
        return (new GridSortableController)->sort(new Request(['_model' => $model, '_sort' => $sort]));
    }

    public function test_it_reorders_model_when_class_name_is_signed()
    {
        // Reversed key order is what actually drives the new ordering.
        $ids = SortableItem::orderBy('order_column')->pluck('id')->reverse()->values();

        $sort = $ids->map(fn ($id, $i) => ['key' => $id, 'sort' => $i + 1])->all();

        $response = $this->sort(Crypt::encryptString(SortableItem::class), $sort);

        $this->assertTrue($response->getData()->status);

        $this->assertSame(
            ['Charlie', 'Bravo', 'Alpha'],
            SortableItem::orderBy('order_column')->pluck('name')->all()
        );
    }

    public function test_it_rejects_unsigned_class_name()
    {
        $exception = null;

        try {
            $this->sort(SortableItem::class, [['key' => 1, 'sort' => 1]]);
        } catch (HttpException $exception) {
        }

        $this->assertInstanceOf(HttpException::class, $exception);
        $this->assertSame(400, $exception->getStatusCode());

        // Nothing was written.
        $this->assertSame(
            ['Alpha', 'Bravo', 'Charlie'],
            SortableItem::orderBy('order_column')->pluck('name')->all()
        );
    }

    public function test_it_rejects_signed_non_sortable_class()
    {
        $exception = null;

        try {
            $this->sort(Crypt::encryptString(UserModel::class), [['key' => 1, 'sort' => 1]]);
        } catch (HttpException $exception) {
        }

        $this->assertInstanceOf(HttpException::class, $exception);
        $this->assertSame(422, $exception->getStatusCode());
    }

    public function test_it_rejects_signed_nonexistent_class()
    {
        $exception = null;

        try {
            $this->sort(Crypt::encryptString('Tests\\Models\\DoesNotExist'), [['key' => 1, 'sort' => 1]]);
        } catch (HttpException $exception) {
        }

        $this->assertInstanceOf(HttpException::class, $exception);
        $this->assertSame(422, $exception->getStatusCode());
    }
}
