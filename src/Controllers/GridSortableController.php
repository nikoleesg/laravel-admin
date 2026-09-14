<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Grid\Tools\SaveOrderBtn;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Spatie\EloquentSortable\Sortable;
use Throwable;

class GridSortableController extends Controller
{
    public function sort(Request $request)
    {
        $modelClass = $this->resolveModelClass($request);

        $sorts = collect($request->get('_sort'))
            ->pluck('key')
            ->combine(
                collect($request->get('_sort'))
                    ->pluck('sort')
                    ->map(fn ($sort) => (int) $sort)
                    ->sort()
            );

        $status = true;
        $message = trans('admin.save_succeeded');

        try {
            /** @var Collection $models */
            $models = $modelClass::find($sorts->keys());

            foreach ($models as $model) {
                $column = data_get($model->sortable, 'order_column_name', 'order_column');

                $model->{$column} = $sorts->get($model->getKey());
                $model->save();
            }
        } catch (Throwable $exception) {
            report($exception);

            $status = false;
            $message = trans('admin.save_failed');
        }

        return response()->json(compact('status', 'message'));
    }

    /**
     * Resolve and validate the sortable model class from the signed request payload.
     *
     * The class name is sent encrypted by {@see SaveOrderBtn},
     * so the client can only ever submit a class the server itself signed into a grid
     * it rendered. We still validate the decrypted value as defence in depth.
     *
     * @return class-string<Model&Sortable>
     */
    protected function resolveModelClass(Request $request): string
    {
        try {
            $modelClass = Crypt::decryptString($request->get('_model'));
        } catch (DecryptException $exception) {
            abort(400, 'Invalid sortable payload.');
        }

        if (! class_exists($modelClass)
            || ! is_subclass_of($modelClass, Model::class)
            || ! in_array(Sortable::class, class_implements($modelClass), true)
        ) {
            abort(422, 'Model is not sortable.');
        }

        return $modelClass;
    }
}
