<?php

use App\Constants\RoleConstants;
use App\Models\Role;
use Illuminate\Support\Collection;

class RoleTableSeeder extends BaseSeeder
{
    /**
     * @return void
     */
    public function runAlways()
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Administrator Users',
            ],
            [
                'name' => 'manager',
                'description' => 'Manager Users',
            ],
            [
                'name' => 'business',
                'description' => 'Business Users',
            ],
            [
                'name' => 'worker',
                'description' => 'worker Users',
            ],
            [
                'name' => 'tourist',
                'description' => 'Tourist Users',
            ],
        ];

        DB::table('roles')->insert($roles);
    }

    /**
     * Get a collection of random roles
     * Remove duplicates to prevent SQL errors, also prevent infinite loop in case of not enough roles
     *
     * @param $count int How many roles to get
     *
     * @return Collection
     * @throws \InvalidArgumentException
     */
    public static function getRandomRoles($count): Collection
    {
        $roles = Role::all();
        $fakeRoles = [];
        $i = 0;
        do {
            ++$i;
            $fakeRoles[] = $roles->whereNotIn('name', [RoleConstants::ROLE_ADMIN])->random();
            $fakeRoles = array_unique($fakeRoles);
        } while (count($fakeRoles) < $count && $i < 50); // Iteration limit

        return collect($fakeRoles);
    }
}
