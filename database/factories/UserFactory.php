<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Constants\RoleConstants;
use App\Models\Role;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    return [
        'first_name'        => $faker->firstName,
        'last_name'         => $faker->lastName,
        'username'          => $faker->userName,
        'email'             => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'is_active'         => true,
        'password'          => 'password', // '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'remember_token'    => Str::random(10),
        'primary_role_id'   => Role::where('name', RoleConstants::ROLE_TOURIST)->first(),
        'manager_id'        => null,
        'business_id'       => null,
        'phone'             => $faker->phoneNumber,
        'birthday'          => null,
    ];
});
