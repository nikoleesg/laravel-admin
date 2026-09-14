<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class SortableItem extends Model implements Sortable
{
    use SortableTrait;

    protected $table = 'test_sortable_items';

    protected $guarded = [];

    public $sortable = [
        'order_column_name' => 'order_column',
        'sort_when_creating' => true,
    ];
}
