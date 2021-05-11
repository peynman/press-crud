<?php

namespace Larapress\CRUD\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Larapress\CRUD\Models\Role;

class RoleFactory extends Factory {
    protected $model = Role::class;

    public function definition()
    {
        $title = $this->faker->words(5, true);
        return [
            'name' => str_replace(' ', '-', strtolower($title)),
            'title' => $title,
            'priority' => 1,
        ];
    }
}
