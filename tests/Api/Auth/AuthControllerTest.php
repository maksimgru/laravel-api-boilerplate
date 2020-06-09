<?php

namespace Tests\Api\Auth;

use App\Http\Controllers\Auth\AuthController;
use Dingo\Api\Http\Response;
use Tests\ApiTestCase;
use UserTableSeeder;

class AuthControllerTest extends ApiTestCase
{
    public const AUTH_RESPONSE_STRUCTURE =
    [
        'jwt',
        'token_type',
        'expires_in',
    ];

    public const AUTH_CREDENTIALS =
    [
        'email'    => UserTableSeeder::ADMIN_CREDENTIALS['email'],
        'password' => UserTableSeeder::AUTH_PASSWORD,
    ];

    public const AUTH_CREDENTIALS_NOT_ACTIVE_USERS =
    [
        [
            'email'    => UserTableSeeder::ADMIN_CREDENTIALS_NOT_ACTIVE['email'],
            'password' => UserTableSeeder::AUTH_PASSWORD,
        ],
        [
            'email'    => UserTableSeeder::MANAGER_CREDENTIALS_NOT_ACTIVE['email'],
            'password' => UserTableSeeder::AUTH_PASSWORD,
        ],
        [
            'email'    => UserTableSeeder::BUSINESS_CREDENTIALS_NOT_ACTIVE['email'],
            'password' => UserTableSeeder::AUTH_PASSWORD,
        ],
        [
            'email'    => UserTableSeeder::WORKER_CREDENTIALS_NOT_ACTIVE['email'],
            'password' => UserTableSeeder::AUTH_PASSWORD,
        ],
        [
            'email'    => UserTableSeeder::TOURIST_CREDENTIALS_NOT_ACTIVE['email'],
            'password' => UserTableSeeder::AUTH_PASSWORD,
        ],
    ];

    public const RESET_PASSWORD_CREDENTIALS =
    [
        'email' => UserTableSeeder::ADMIN_CREDENTIALS['email'],
    ];

    public const RESET_PASSWORD_CREDENTIALS_FAKE =
    [
        'email' => 'fake@example.com',
    ];

    /**
     * @covers       AuthController::getToken()
     * @method       GET
     * @group        auth
     */
    public function testAuthViaBasic()
    {
        $headers = [
            'Authorization' => 'Basic ' . base64_encode(self::AUTH_CREDENTIALS['email'] . ':' . self::AUTH_CREDENTIALS['password'])
        ];
        $jsonResponse = $this->getJson($this->apiPrefix . '/auth/jwt/token', $headers);
        // Check status and structure
        $jsonResponse
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['data' => self::AUTH_RESPONSE_STRUCTURE])
        ;
    }

    /**
     * @covers       AuthController::login()
     * @method       POST
     * @group        auth
     */
    public function testAuthLogin()
    {
        $jsonResponse = $this->postJson($this->apiPrefix . '/auth/jwt/login', self::AUTH_CREDENTIALS);
        // Check status, structure and data
        $jsonResponse
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['data' => self::AUTH_RESPONSE_STRUCTURE])
        ;
    }

    /**
     * @covers       AuthController::login()
     * @method       POST
     * @group        auth
     */
    public function testCanNotLoginAsNotActiveUser()
    {
        foreach (self::AUTH_CREDENTIALS_NOT_ACTIVE_USERS as $credentials) {
            $jsonResponse = $this->postJson($this->apiPrefix . '/auth/jwt/login', $credentials);
            // Check status
            $jsonResponse->assertStatus(Response::HTTP_FORBIDDEN);
            // Check response message
            $response = $jsonResponse->decodeResponseJson();
            $this->assertArrayHasKey('message', $response);
            $this->assertStringContainsStringIgnoringCase('is not activated', $response['message']);
        }
    }

    /**
     * @covers       AuthController::refreshToken()
     * @method       GET
     * @group        auth
     */
    public function testAuthRefreshToken()
    {
        $jsonResponse = $this->actingAsAdmin()->getJson($this->apiPrefix . '/auth/jwt/refresh');
        // Check status, structure and data
        $jsonResponse
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['data' => self::AUTH_RESPONSE_STRUCTURE])
        ;
    }

    /**
     * @covers       AuthController::invalidateToken()
     * @method       GET
     * @group        auth
     */
    public function testAuthLogout()
    {
        $jsonResponse = $this->actingAsAdmin()->deleteJson($this->apiPrefix . '/auth/jwt/logout');
        // Check status, structure and data
        $jsonResponse->assertStatus(Response::HTTP_NO_CONTENT);
    }

    /**
     * @covers       AuthController::resetPassword()
     * @method       POST
     * @group        auth
     * @dataProvider dataSetResetPassword
     *
     * @param array $input
     * @param array $expect
     */
    public function testAuthResetPassword(
        array $input,
        array $expect
    ) {
        $jsonResponse = $this->postJson($this->apiPrefix . '/auth/jwt/password/reset', $input['credentials']);
        // Check status
        $jsonResponse->assertStatus($expect['statusCode']);
    }

    /**
     * @return array
     */
    public function dataSetResetPassword(): array
    {
        return [
            // Invalid Credentials
            [
                ['credentials' => self::RESET_PASSWORD_CREDENTIALS_FAKE],
                ['statusCode' => Response::HTTP_BAD_REQUEST],
            ],
            // Valid Credentials
            [
                ['credentials' => self::RESET_PASSWORD_CREDENTIALS],
                ['statusCode' => Response::HTTP_ACCEPTED],
            ],
        ];
    }
}
