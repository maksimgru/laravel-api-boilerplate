<?php

use App\Models\Role;
use App\Models\User;
use App\Constants\RoleConstants;

class UserTableSeeder extends BaseSeeder
{
    public const AUTH_PASSWORD = 'password';

    public const ADMIN_CREDENTIALS = [
        'first_name'        => 'Admin',
        'last_name'         => 'Power',
        'username'          => 'admin',
        'email'             => 'admin-loyalty@yopmail.com',
        'primary_role_name' => RoleConstants::ROLE_ADMIN,
    ];

    public const ADMIN_CREDENTIALS_NOT_ACTIVE = [
        'first_name'        => 'Admin-na',
        'last_name'         => 'Power-na',
        'username'          => 'admin-na',
        'email'             => 'admin-na-loyalty@yopmail.com',
        'is_active'         => false,
        'primary_role_name' => RoleConstants::ROLE_ADMIN,
    ];

    public const MANAGER_CREDENTIALS = [
        'first_name'        => 'Marry',
        'last_name'         => 'Smith',
        'username'          => 'marry',
        'email'             => 'marry-loyalty@yopmail.com',
        'primary_role_name' => RoleConstants::ROLE_MANAGER,
    ];

    public const MANAGER_CREDENTIALS_2 = [
        'first_name'        => 'Larry',
        'last_name'         => 'Smith',
        'username'          => 'larry',
        'email'             => 'larry-loyalty@yopmail.com',
        'primary_role_name' => RoleConstants::ROLE_MANAGER,
    ];

    public const MANAGER_CREDENTIALS_NOT_ACTIVE = [
        'first_name'        => 'Larry-na',
        'last_name'         => 'Smith-na',
        'username'          => 'larry-na',
        'email'             => 'larry-na-loyalty@yopmail.com',
        'is_active'         => false,
        'primary_role_name' => RoleConstants::ROLE_MANAGER,
    ];

    public const BUSINESS_CREDENTIALS = [
        'first_name'        => 'Billy',
        'last_name'         => 'Ford',
        'username'          => 'billy',
        'email'             => 'billy-loyalty@yopmail.com',
        'manager_email'     => self::MANAGER_CREDENTIALS['email'],
        'balance'           => 500,
        'primary_role_name' => RoleConstants::ROLE_BUSINESS,
    ];

    public const BUSINESS_CREDENTIALS_2 = [
        'first_name'        => 'Dilly',
        'last_name'         => 'Ford',
        'username'          => 'dilly',
        'email'             => 'dilly-loyalty@yopmail.com',
        'manager_email'     => self::MANAGER_CREDENTIALS_2['email'],
        'primary_role_name' => RoleConstants::ROLE_BUSINESS,
    ];

    public const BUSINESS_CREDENTIALS_NOT_ACTIVE = [
        'first_name'        => 'Dilly-na',
        'last_name'         => 'Ford-na',
        'username'          => 'dilly-na',
        'email'             => 'dilly-na-loyalty@yopmail.com',
        'is_active'         => false,
        'manager_email'     => self::MANAGER_CREDENTIALS['email'],
        'primary_role_name' => RoleConstants::ROLE_BUSINESS,
    ];

    public const WORKER_CREDENTIALS = [
        'first_name'        => 'Walt',
        'last_name'         => 'Paton',
        'username'          => 'walt',
        'email'             => 'walt-loyalty@yopmail.com',
        'business_email'    => self::BUSINESS_CREDENTIALS['email'],
        'primary_role_name' => RoleConstants::ROLE_WORKER,
    ];

    public const WORKER_CREDENTIALS_2 = [
        'first_name'        => 'Zalt',
        'last_name'         => 'Paton',
        'username'          => 'zalt',
        'email'             => 'zalt-loyalty@yopmail.com',
        'business_email'    => self::BUSINESS_CREDENTIALS_2['email'],
        'primary_role_name' => RoleConstants::ROLE_WORKER,
    ];

    public const WORKER_CREDENTIALS_NOT_ACTIVE = [
        'first_name'        => 'Zalt-na',
        'last_name'         => 'Paton-na',
        'username'          => 'zalt-na',
        'email'             => 'zalt-na-loyalty@yopmail.com',
        'is_active'         => false,
        'business_email'    => self::BUSINESS_CREDENTIALS['email'],
        'primary_role_name' => RoleConstants::ROLE_WORKER,
    ];

    public const TOURIST_CREDENTIALS = [
        'first_name'        => 'Tom',
        'last_name'         => 'Soap',
        'username'          => 'tom',
        'email'             => 'tom-loyalty@yopmail.com',
        'business_email'    => self::BUSINESS_CREDENTIALS_2['email'],
        'primary_role_name' => RoleConstants::ROLE_TOURIST,
    ];

    public const TOURIST_CREDENTIALS_2 = [
        'first_name'        => 'Rom',
        'last_name'         => 'Soap',
        'username'          => 'rom',
        'email'             => 'rom-loyalty@yopmail.com',
        'primary_role_name' => RoleConstants::ROLE_TOURIST,
    ];

    public const TOURIST_CREDENTIALS_NOT_ACTIVE = [
        'first_name'        => 'Rom-na',
        'last_name'         => 'Soap-na',
        'username'          => 'rom-na',
        'email'             => 'rom-na-loyalty@yopmail.com',
        'is_active'         => false,
        'business_email'    => self::BUSINESS_CREDENTIALS['email'],
        'primary_role_name' => RoleConstants::ROLE_TOURIST,
    ];

    public const TOURIST_CREDENTIALS_NOT_VERIFIED_EMAIL = [
        'email'                    => 'rom-nve-loyalty@yopmail.com',
        'email_verified_at'        => null,
        'email_verification_token' => null,
    ];

    public const ALL_USER_CREDENTIALS = [
        self::ADMIN_CREDENTIALS,
        self::ADMIN_CREDENTIALS_NOT_ACTIVE,
        self::MANAGER_CREDENTIALS,
        self::MANAGER_CREDENTIALS_2,
        self::MANAGER_CREDENTIALS_NOT_ACTIVE,
        self::BUSINESS_CREDENTIALS,
        self::BUSINESS_CREDENTIALS_2,
        self::BUSINESS_CREDENTIALS_NOT_ACTIVE,
        self::WORKER_CREDENTIALS,
        self::WORKER_CREDENTIALS_2,
        self::WORKER_CREDENTIALS_NOT_ACTIVE,
        self::TOURIST_CREDENTIALS,
        self::TOURIST_CREDENTIALS_2,
        self::TOURIST_CREDENTIALS_NOT_ACTIVE,
    ];

    /**
     * @return void
     * @throws \InvalidArgumentException
     */
    public function runFake()
    {
        // Grab all roles for reference
        $roles = Role::all();

        // Create predefined users
        foreach (self::ALL_USER_CREDENTIALS as $userData) {
            $userData = array_merge(
                $userData,
                [
                    'primary_role_id' => $roles
                        ->where('name', $userData['primary_role_name'])
                        ->first()
                        ->id,
                    'manager_id' => isset($userData['manager_email'])
                        ? User::where(['email' => $userData['manager_email']])
                            ->first()
                            ->id
                        : null,
                    'business_id' => isset($userData['business_email'])
                        ? User::where(['email' => $userData['business_email']])
                            ->first()
                            ->id
                        : null,
                ]
            );
            unset($userData['primary_role_name'], $userData['manager_email'], $userData['business_email']);
            factory(User::class)->create($userData);
            if (App::environment() == 'local') {sleep(1);}
        }

        // Create Users with Not Verified Email
        factory(User::class)->create(self::TOURIST_CREDENTIALS_NOT_VERIFIED_EMAIL);
    }

    /**
     * @return void
     */
    public function runProduction()
    {
        $userData = array_merge(
            self::ADMIN_CREDENTIALS,
            ['primary_role_id' => Role::getIdByRoleName(RoleConstants::ROLE_ADMIN)]
        );
        unset($userData['primary_role_name']);
        factory(User::class)->create($userData);
    }
}
