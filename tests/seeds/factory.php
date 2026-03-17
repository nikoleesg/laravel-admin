<?php

use Tests\Models\User;
use Tests\Models\Profile;
use Tests\Models\Tag;

if (!function_exists('factory')) {
    function factory($class, $amount = null)
    {
        static $definitions = [];

        if (empty($definitions)) {
            $faker = \Faker\Factory::create();
            $definitions[User::class] = function () use ($faker) {
                return [
                    'username' => $faker->userName,
                    'email'    => $faker->email,
                    'mobile'   => $faker->phoneNumber,
                    'avatar'   => $faker->imageUrl(),
                    'password' => '$2y$10$U2WSLymU6eKJclK06glaF.Gj3Sw/ieDE3n7mJYjKEgDh4nzUiSESO', // bcrypt(123456)
                ];
            };
            $definitions[Profile::class] = function () use ($faker) {
                return [
                    'first_name' => $faker->firstName,
                    'last_name'  => $faker->lastName,
                    'postcode'   => $faker->postcode,
                    'address'    => $faker->address,
                    'latitude'   => $faker->latitude,
                    'longitude'  => $faker->longitude,
                    'color'      => $faker->hexColor,
                    'start_at'   => $faker->dateTime,
                    'end_at'     => $faker->dateTime,
                ];
            };
            $definitions[Tag::class] = function () use ($faker) {
                return [
                    'name' => $faker->word,
                ];
            };
        }

        return new class($class, $amount, $definitions) {
            protected $class;
            protected $amount;
            protected $definitions;

            public function __construct($class, $amount, $definitions) {
                $this->class = $class;
                $this->amount = $amount;
                $this->definitions = $definitions;
            }

            public function make() {
                $times = $this->amount ?: 1;
                $models = collect();
                for ($i=0; $i<$times; $i++) {
                    $attrs = ($this->definitions[$this->class])();
                    $model = new $this->class;
                    $model->forceFill($attrs);
                    $models->push($model);
                }
                return $this->amount === null ? $models->first() : $models;
            }

            public function create() {
                $times = $this->amount ?: 1;
                $models = collect();
                for ($i=0; $i<$times; $i++) {
                    $attrs = ($this->definitions[$this->class])();
                    $model = new $this->class;
                    $model->forceFill($attrs);
                    $model->save();
                    $models->push($model);
                }
                return $this->amount === null ? $models->first() : $models;
            }
        };
    }
}
