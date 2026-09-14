<?php

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Grid;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Models\Profile as ProfileModel;
use Tests\Models\User as UserModel;

class GridSortTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->be(Administrator::first(), 'admin');
    }

    /**
     * Build the grid SQL for a given `_sort` request input.
     *
     * @param mixed $sort
     *
     * @return string
     */
    protected function sortSql($sort)
    {
        $this->app['request']->replace(['_sort' => $sort]);

        $grid = new Grid(new UserModel());
        $grid->model()->with('profile');

        return $grid->model()->getQueryBuilder()->toSql();
    }

    public function testPlainColumnSort()
    {
        $sql = $this->sortSql(['column' => 'username', 'type' => 'DESC']);

        $this->assertStringEndsWith('order by "username" desc', $sql);
    }

    public function testCastSortQuotesColumnAndNormalisesCast()
    {
        $sql = $this->sortSql(['column' => 'username', 'type' => 'asc', 'cast' => ' decimal(10, 2) ']);

        $this->assertStringEndsWith('order by CAST("username" AS DECIMAL(10, 2)) asc', $sql);
    }

    public function testJsonSortQuotesColumn()
    {
        $sql = $this->sortSql(['column' => 'data.json.field', 'type' => 'desc', 'cast' => 'unsigned']);

        $this->assertStringEndsWith('order by CAST(JSON_EXTRACT("data", \'$.json.field\') AS UNSIGNED) desc', $sql);
    }

    public function testRelationSortStillWorks()
    {
        $sql = $this->sortSql(['column' => 'profile.postcode', 'type' => 'asc']);

        $this->assertStringEndsWith('order by "test_user_profiles"."postcode" asc', $sql);
    }

    /**
     * @return array
     */
    public static function malformedSortProvider()
    {
        return [
            'raw type in cast path'   => [['column' => 'id', 'type' => 'asc, (SELECT 1)', 'cast' => 'unsigned']],
            'raw cast'                => [['column' => 'id', 'type' => 'asc', 'cast' => 'unsigned) asc, (SELECT 1']],
            'unknown cast'            => [['column' => 'id', 'type' => 'asc', 'cast' => 'blob']],
            'raw json column'         => [['column' => "data') AS x, (SELECT 1) --", 'type' => 'asc']],
            'raw json path'           => [['column' => "data.a') --", 'type' => 'asc']],
            'quoted column'           => [['column' => 'user"name', 'type' => 'asc']],
            'array column'            => [['column' => ['id'], 'type' => 'asc']],
            'array type'              => [['column' => 'id', 'type' => ['asc']]],
            'array cast'              => [['column' => 'id', 'type' => 'asc', 'cast' => ['unsigned']]],
            'scalar sort'             => ['id'],
            'missing type'            => [['column' => 'id']],
            'empty column'            => [['column' => '', 'type' => 'asc']],
        ];
    }

    /**
     * @param mixed $sort
     */
    #[DataProvider('malformedSortProvider')]
    public function testMalformedSortIsIgnored($sort)
    {
        $sql = $this->sortSql($sort);

        $this->assertStringNotContainsString('order by', $sql);
        $this->assertStringNotContainsString('SELECT 1', $sql);
    }

    public function testMalformedSortRendersDefaultOrder()
    {
        factory(UserModel::class, 3)->create()->each(function ($user) {
            $user->profile()->save(factory(ProfileModel::class)->make());
        });

        $this->visit('admin/users?'.http_build_query([
            '_sort' => ['column' => 'id', 'type' => 'desc; DROP TABLE test_users; --', 'cast' => 'unsigned'],
        ]))->see('Users');

        $ids = $this->crawler()->filter('table.table tbody tr[data-key]')->each(function ($tr) {
            return (int) $tr->attr('data-key');
        });

        $this->assertSame([1, 2, 3], $ids);
        $this->assertCount(3, UserModel::all());
    }
}
