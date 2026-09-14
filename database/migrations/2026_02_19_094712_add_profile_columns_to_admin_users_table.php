<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfileColumnsToAdminUsersTable extends Migration
{
    /**
     * Profile columns keyed by name, each with its Blueprint method and args.
     *
     * @var array
     */
    protected $columns = [
        'type' => ['string'],
        'first_name' => ['string'],
        'last_name' => ['string'],
        'preferred_name' => ['string'],
        'gender' => ['unsignedTinyInteger'],
        'birth_date' => ['date'],
        'nationality' => ['string'],
        'id_type' => ['unsignedTinyInteger'],
        'id_number' => ['string'],
        'photo' => ['string'],
        'phone_number' => ['string'],
        'email' => ['string'],
        'blk' => ['string'],
        'street_name' => ['string'],
        'unit' => ['string'],
        'postal' => ['string'],
        'lat' => ['decimal', 10, 7],
        'lng' => ['decimal', 10, 7],
        'preferred_areas' => ['text'],
        'description' => ['text'],
    ];

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return config('admin.database.connection') ?: config('database.default');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $usersTable = config('admin.database.users_table');

        Schema::table($usersTable, function (Blueprint $table) use ($usersTable) {
            foreach ($this->columns as $name => $definition) {
                if (Schema::hasColumn($usersTable, $name)) {
                    continue;
                }

                $method = array_shift($definition);

                $table->{$method}($name, ...$definition)->nullable();
            }
        });

        if (! Schema::hasIndex($usersTable, $this->emailIndex($usersTable))) {
            Schema::table($usersTable, function (Blueprint $table) {
                $table->index('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $usersTable = config('admin.database.users_table');

        if (Schema::hasIndex($usersTable, $this->emailIndex($usersTable))) {
            Schema::table($usersTable, function (Blueprint $table) {
                $table->dropIndex(['email']);
            });
        }

        $columns = array_filter(array_keys($this->columns), function ($name) use ($usersTable) {
            return Schema::hasColumn($usersTable, $name);
        });

        if (empty($columns)) {
            return;
        }

        Schema::table($usersTable, function (Blueprint $table) use ($columns) {
            $table->dropColumn(array_values($columns));
        });
    }

    /**
     * The conventional name Laravel gives the `email` index on the users table.
     *
     * @param  string  $usersTable
     * @return string
     */
    protected function emailIndex($usersTable)
    {
        return "{$usersTable}_email_index";
    }
}
