<?php

use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProfileColumnsMigrationTest extends TestCase
{
    const MIGRATION = '2026_02_19_094712_add_profile_columns_to_admin_users_table';

    protected $profileColumns = [
        'type', 'first_name', 'last_name', 'preferred_name', 'gender', 'birth_date',
        'nationality', 'id_type', 'id_number', 'photo', 'phone_number', 'email',
        'blk', 'street_name', 'unit', 'postal', 'lat', 'lng', 'preferred_areas', 'description',
    ];

    public function testFreshInstallHasProfileColumns()
    {
        $this->assertProfileColumns(true);

        $this->assertTrue(DB::table('migrations')->where('migration', self::MIGRATION)->exists());
        $this->assertTrue(Schema::hasIndex($this->usersTable(), $this->usersTable().'_email_index'));

        $this->assertEquals(
            $this->profileColumns,
            array_values(array_intersect(Administrator::first()->getFillable(), $this->profileColumns))
        );
    }

    public function testUpgradeFromInstallWithoutProfileColumns()
    {
        $this->simulateInstallWithoutProfileColumns();

        $this->assertProfileColumns(false);

        $this->artisan('migrate');

        $this->assertProfileColumns(true);
        $this->assertTrue(DB::table('migrations')->where('migration', self::MIGRATION)->exists());

        Administrator::first()->update(['first_name' => 'Ada', 'email' => 'ada@example.com']);

        $this->seeInDatabase($this->usersTable(), ['username' => 'admin', 'first_name' => 'Ada', 'email' => 'ada@example.com']);
    }

    public function testMigrationSkipsColumnsThatAlreadyExist()
    {
        $this->simulateInstallWithoutProfileColumns();

        Schema::table($this->usersTable(), function (Blueprint $table) {
            $table->string('email')->nullable();
            $table->string('first_name')->nullable();
        });

        $this->artisan('migrate');

        $this->assertProfileColumns(true);
    }

    public function testRollbackDropsProfileColumns()
    {
        $this->artisan('migrate:rollback', ['--step' => 1]);

        $this->assertTrue(Schema::hasTable($this->usersTable()));
        $this->assertProfileColumns(false);
        $this->assertFalse(Schema::hasIndex($this->usersTable(), $this->usersTable().'_email_index'));
        $this->assertFalse(DB::table('migrations')->where('migration', self::MIGRATION)->exists());
    }

    /**
     * Put the schema in the state an app upgraded from upstream / 1.8.x is in:
     * the 2016 migration recorded as run, profile columns absent.
     */
    protected function simulateInstallWithoutProfileColumns()
    {
        $this->artisan('migrate:rollback', ['--step' => 1]);

        $this->assertProfileColumns(false);
    }

    protected function assertProfileColumns($present)
    {
        foreach ($this->profileColumns as $column) {
            $this->assertSame($present, Schema::hasColumn($this->usersTable(), $column), "column [$column]");
        }
    }

    protected function usersTable()
    {
        return config('admin.database.users_table');
    }
}
