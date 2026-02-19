<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Hash;

class UserController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('admin.administrator');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $userModel = config('admin.database.users_model');

        $grid = new Grid(new $userModel);

        $grid->column('id', 'ID')->sortable();
        $grid->column('username', trans('admin.username'));
        $grid->column('name', trans('admin.name'));
        $grid->column('first_name', 'First Name');
        $grid->column('last_name', 'Last Name');
        $grid->column('email', 'Email');
        $grid->column('phone_number', 'Phone');
        $grid->column('roles', trans('admin.roles'))->pluck('name')->label();
        $grid->column('created_at', trans('admin.created_at'));
        $grid->column('updated_at', trans('admin.updated_at'));

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            if ($actions->getKey() == 1) {
                $actions->disableDelete();
            }
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param  mixed  $id
     * @return Show
     */
    protected function detail($id)
    {
        $userModel = config('admin.database.users_model');

        $show = new Show($userModel::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('username', trans('admin.username'));
        $show->field('name', trans('admin.name'));

        $show->divider('Personal Information');
        $show->field('first_name', 'First Name');
        $show->field('last_name', 'Last Name');
        $show->field('preferred_name', 'Preferred Name');
        $show->field('gender', 'Gender')->using([1 => 'Male', 2 => 'Female', 3 => 'Other']);
        $show->field('birth_date', 'Birth Date');
        $show->field('nationality', 'Nationality');
        $show->field('id_type', 'ID Type')->using([1 => 'NRIC', 2 => 'Passport', 3 => 'FIN', 4 => 'Other']);
        $show->field('id_number', 'ID Number');
        $show->field('photo', 'Photo')->image();

        $show->divider('Contact Information');
        $show->field('phone_number', 'Phone Number');
        $show->field('email', 'Email');

        $show->divider('Address');
        $show->field('blk', 'Block');
        $show->field('street_name', 'Street Name');
        $show->field('unit', 'Unit');
        $show->field('postal', 'Postal Code');
        $show->field('lat', 'Latitude');
        $show->field('lng', 'Longitude');

        $show->divider('Additional Information');
        $show->field('preferred_areas', 'Preferred Areas');
        $show->field('description', 'Description');

        $show->field('roles', trans('admin.roles'))->as(function ($roles) {
            return $roles->pluck('name');
        })->label();
        $show->field('permissions', trans('admin.permissions'))->as(function ($permission) {
            return $permission->pluck('name');
        })->label();
        $show->field('created_at', trans('admin.created_at'));
        $show->field('updated_at', trans('admin.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $userModel = config('admin.database.users_model');
        $permissionModel = config('admin.database.permissions_model');
        $roleModel = config('admin.database.roles_model');

        $form = new Form(new $userModel);

        $userTable = config('admin.database.users_table');
        $connection = config('admin.database.connection');

        $form->tab('Basic Info', function ($form) use ($connection, $userTable, $roleModel, $permissionModel) {
            $form->display('id', 'ID');
            $form->text('username', trans('admin.username'))
                ->creationRules(['required', "unique:{$connection}.{$userTable}"])
                ->updateRules(['required', "unique:{$connection}.{$userTable},username,{{id}}"]);
            $form->text('name', trans('admin.name'))->rules('required');
            $form->password('password', trans('admin.password'))->rules('required|confirmed');
            $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
                ->default(function ($form) {
                    return $form->model()->password;
                });
            $form->multipleSelect('roles', trans('admin.roles'))->options($roleModel::all()->pluck('name', 'id'));
            $form->multipleSelect('permissions', trans('admin.permissions'))->options($permissionModel::all()->pluck('name', 'id'));
            $form->display('created_at', trans('admin.created_at'));
            $form->display('updated_at', trans('admin.updated_at'));

            $form->ignore(['password_confirmation']);
        });

        $form->tab('Personal', function ($form) {
            $form->text('first_name', 'First Name');
            $form->text('last_name', 'Last Name');
            $form->text('preferred_name', 'Preferred Name');
            $form->select('gender', 'Gender')->options([
                1 => 'Male',
                2 => 'Female',
                3 => 'Other',
            ]);
            $form->date('birth_date', 'Birth Date');
            $form->text('nationality', 'Nationality');
            $form->select('id_type', 'ID Type')->options([
                1 => 'NRIC',
                2 => 'Passport',
                3 => 'FIN',
                4 => 'Other',
            ]);
            $form->text('id_number', 'ID Number');
            $form->image('photo', 'Photo')->move('users/photos')->uniqueName();
        });

        $form->tab('Contact & Address', function ($form) {
            $form->text('phone_number', 'Phone Number');
            $form->email('email', 'Email');
            $form->text('blk', 'Block');
            $form->text('street_name', 'Street Name');
            $form->text('unit', 'Unit');
            $form->text('postal', 'Postal Code');
            $form->decimal('lat', 'Latitude');
            $form->decimal('lng', 'Longitude');
        });

        $form->tab('Additional', function ($form) {
            $form->text('preferred_areas', 'Preferred Areas');
            $form->textarea('description', 'Description');
        });

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });

        return $form;
    }
}
