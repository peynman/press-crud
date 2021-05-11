<?php

namespace Larapress\CRUD\Factories;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Larapress\Profiles\Flags\UserFlags;

class UserFactory extends Factory {

    /**
     * Create a new factory instance.
     *
     * @param  int|null  $count
     * @param  \Illuminate\Support\Collection|null  $states
     * @param  \Illuminate\Support\Collection|null  $has
     * @param  \Illuminate\Support\Collection|null  $for
     * @param  \Illuminate\Support\Collection|null  $afterMaking
     * @param  \Illuminate\Support\Collection|null  $afterCreating
     * @param  string|null  $connection
     * @return void
     */
    public function __construct($count = null,
                                ?Collection $states = null,
                                ?Collection $has = null,
                                ?Collection $for = null,
                                ?Collection $afterMaking = null,
                                ?Collection $afterCreating = null,
                                $connection = null)
    {
        $this->model = config('larapress.crud.user.class');
        parent::__construct($count, $states, $has, $for, $afterMaking, $afterCreating, $connection);
    }

    public function definition()
    {
        return [
            'name' => $this->faker->userName,
            'password' => $this->faker->password,
            'flags' => UserFlags::VERIFIED_USER,
        ];
    }


}
