<?php

namespace Encore\Admin\Actions;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;
use Spatie\Tags\Tag;

class TagRowAction extends RowAction
{
    protected string $tag;

    protected string $type = 'Application.EloquentModel';

    protected string $hasTagIcon = "<i class=\"fa fa-star\"></i>";

    protected string $doesntHaveTagIcon = "<i class=\"fa fa-star-o\"></i>";

    public function handle(Model $model)
    {
        if (!isset($this->tag)) {
            // Tag not defined
            return $this->response();
        }

        $tagWithType = Tag::findOrCreate($this->tag, $this->type);

        if ($model->hasTag($this->tag, $this->type)) {
            $model->detachTag($tagWithType);
        } else {
            $model->attachTag($tagWithType);
        }

        $html = $model->hasTag($this->tag, $this->type) ? $this->hasTagIcon : $this->doesntHaveTagIcon;

        return $this->response()->html($html)->refresh();
    }

    public function display($value)
    {
        return $value ? $this->hasTagIcon : $this->doesntHaveTagIcon;
    }
}
