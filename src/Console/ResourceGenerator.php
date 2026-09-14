<?php

namespace Encore\Admin\Console;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ResourceGenerator
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $formats = [
        'form_field'  => "\$form->%s('%s', __('%s'))",
        'show_field'  => "\$show->field('%s', __('%s'))",
        'grid_column' => "\$grid->column('%s', __('%s'))",
    ];

    /**
     * @var array
     */
    protected $fieldTypeMapping = [
        'ip'       => 'ip',
        'email'    => 'email|mail',
        'password' => 'password|pwd',
        'url'      => 'url|link|src|href',
        'mobile'   => 'mobile|phone',
        'color'    => 'color|rgb',
        'image'    => 'image|img|avatar|pic|picture|cover',
        'file'     => 'file|attachment',
    ];

    /**
     * Driver column types folded into the generic types used by generateForm().
     *
     * @var array
     */
    protected $typeMapping = [
        'boolean'  => ['boolean', 'bool', 'bit'],
        'json'     => ['json', 'jsonb'],
        'string'   => [
            'string', 'varchar', 'char', 'nvarchar', 'nchar', 'bpchar', 'character varying',
            'character', 'enum', 'set', 'uuid', 'uniqueidentifier',
        ],
        'integer'  => [
            'integer', 'int', 'bigint', 'mediumint', 'smallint', 'tinyint',
            'int2', 'int4', 'int8', 'serial', 'bigserial', 'year',
        ],
        'decimal'  => ['decimal', 'numeric', 'float', 'double', 'real', 'float4', 'float8', 'double precision', 'money'],
        'datetime' => ['datetime', 'datetime2', 'smalldatetime', 'timestamp', 'timestamptz'],
        'date'     => ['date'],
        'time'     => ['time', 'timetz'],
        'text'     => [
            'text', 'tinytext', 'mediumtext', 'longtext', 'ntext',
            'blob', 'tinyblob', 'mediumblob', 'longblob', 'bytea', 'binary', 'varbinary',
        ],
    ];

    /**
     * ResourceGenerator constructor.
     *
     * @param mixed $model
     */
    public function __construct($model)
    {
        $this->model = $this->getModel($model);
    }

    /**
     * @param mixed $model
     *
     * @return mixed
     */
    protected function getModel($model)
    {
        if ($model instanceof Model) {
            return $model;
        }

        if (!class_exists($model) || !is_string($model) || !is_subclass_of($model, Model::class)) {
            throw new \InvalidArgumentException("Invalid model [$model] !");
        }

        return new $model();
    }

    /**
     * @return string
     */
    public function generateForm()
    {
        $reservedColumns = $this->getReservedColumns();

        $output = '';

        foreach ($this->getTableColumns() as $column) {
            $name = $column['name'];
            if (in_array($name, $reservedColumns)) {
                continue;
            }
            $type = $this->normalizeType($column);
            $default = $this->normalizeDefault($column['default']);

            $defaultValue = '';

            // set column fieldType and defaultValue
            switch ($type) {
                case 'boolean':
                    $fieldType = 'switch';
                    break;
                case 'json':
                    $fieldType = 'text';
                    break;
                case 'string':
                    $fieldType = 'text';
                    foreach ($this->fieldTypeMapping as $type => $regex) {
                        if (preg_match("/^($regex)$/i", $name) !== 0) {
                            $fieldType = $type;
                            break;
                        }
                    }
                    $defaultValue = "'{$default}'";
                    break;
                case 'integer':
                    $fieldType = 'number';
                    break;
                case 'decimal':
                    $fieldType = 'decimal';
                    break;
                case 'datetime':
                    $fieldType = 'datetime';
                    $defaultValue = "date('Y-m-d H:i:s')";
                    break;
                case 'date':
                    $fieldType = 'date';
                    $defaultValue = "date('Y-m-d')";
                    break;
                case 'time':
                    $fieldType = 'time';
                    $defaultValue = "date('H:i:s')";
                    break;
                case 'text':
                    $fieldType = 'textarea';
                    break;
                default:
                    $fieldType = 'text';
                    $defaultValue = "'{$default}'";
            }

            $defaultValue = $defaultValue ?: $default;

            $label = $this->formatLabel($name);

            $output .= sprintf($this->formats['form_field'], $fieldType, $name, $label);

            if (trim($defaultValue, "'\"")) {
                $output .= "->default({$defaultValue})";
            }

            $output .= ";\r\n";
        }

        return $output;
    }

    public function generateShow()
    {
        $output = '';

        foreach ($this->getTableColumns() as $column) {
            $name = $column['name'];

            // set column label
            $label = $this->formatLabel($name);

            $output .= sprintf($this->formats['show_field'], $name, $label);

            $output .= ";\r\n";
        }

        return $output;
    }

    public function generateGrid()
    {
        $output = '';

        foreach ($this->getTableColumns() as $column) {
            $name = $column['name'];
            $label = $this->formatLabel($name);

            $output .= sprintf($this->formats['grid_column'], $name, $label);
            $output .= ";\r\n";
        }

        return $output;
    }

    protected function getReservedColumns()
    {
        return [
            $this->model->getKeyName(),
            $this->model->getCreatedAtColumn(),
            $this->model->getUpdatedAtColumn(),
            'deleted_at',
        ];
    }

    /**
     * Get columns of a giving model.
     *
     * @return array[] Laravel schema column descriptors: `name`, `type_name`,
     *                 `type`, `nullable`, `default`, `auto_increment`, `comment`
     */
    protected function getTableColumns()
    {
        $connection = $this->model->getConnection();

        return Schema::connection($connection->getName())->getColumns($this->model->getTable());
    }

    /**
     * Normalize the driver specific column type to a generic one.
     *
     * The native schema API returns the raw driver type (`varchar`, `int4`,
     * `tinyint`, ...), so the driver families are folded together here.
     *
     * @param array $column
     *
     * @return string
     */
    protected function normalizeType(array $column)
    {
        $type = strtolower($column['type_name']);
        $fullType = strtolower($column['type']);

        // MySQL and SQLite create `boolean()` columns as `tinyint(1)`.
        if ($fullType === 'tinyint(1)') {
            return 'boolean';
        }

        foreach ($this->typeMapping as $generic => $types) {
            if (in_array($type, $types)) {
                return $generic;
            }
        }

        return $type;
    }

    /**
     * Strip driver decorations (quotes, Postgres casts) from a column default.
     *
     * @param mixed $default
     *
     * @return string
     */
    protected function normalizeDefault($default)
    {
        if (is_null($default)) {
            return '';
        }

        $default = (string) $default;

        // Postgres: 'value'::character varying
        $default = preg_replace('/::[a-z ]+$/i', '', $default);

        return trim($default, "'\"");
    }

    /**
     * Format label.
     *
     * @param string $value
     *
     * @return string
     */
    protected function formatLabel($value)
    {
        return ucfirst(str_replace(['-', '_'], ' ', $value));
    }
}
