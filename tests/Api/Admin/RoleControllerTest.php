<?php

namespace Tests\Api\Admin;

use App\Constants\RoleConstants;
use App\Http\Controllers\Admin\RoleController;
use Dingo\Api\Http\Response;
use Tests\ApiTestCase;

class RoleControllerTest extends ApiTestCase
{
    /**
     * @covers       RoleController::index()
     * @method       GET
     * @group        admin-role
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetAllRolesAs(
        array $input,
        array $expect
    ) {
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/roles')
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetAllRoles']);
        if (Response::HTTP_OK === $expect['statusCodeGetAllRoles']) {
            $jsonResponse->assertJsonStructure(
                ['data' => [self::ROLE_RESPONSE_STRUCTURE]]
            );
        }
    }

    /**
     * @return array
     */
    public function dataSet(): array
    {
        return [
            // Invalid Credentials
            [
                ['actingAs' => ''],
                ['statusCodeGetAllRoles' => Response::HTTP_UNAUTHORIZED],
            ],
            // Admin
            [
                ['actingAs' => RoleConstants::ROLE_ADMIN],
                ['statusCodeGetAllRoles' => Response::HTTP_OK],
            ],
            // Manager
            [
                ['actingAs' => RoleConstants::ROLE_MANAGER],
                ['statusCodeGetAllRoles' => Response::HTTP_FORBIDDEN],
            ],
            // Business
            [
                ['actingAs' => RoleConstants::ROLE_BUSINESS],
                ['statusCodeGetAllRoles' => Response::HTTP_FORBIDDEN],
            ],
            // Worker
            [
                ['actingAs' => RoleConstants::ROLE_WORKER],
                ['statusCodeGetAllRoles' => Response::HTTP_FORBIDDEN],
            ],
            // Tourist
            [
                ['actingAs' => RoleConstants::ROLE_TOURIST],
                ['statusCodeGetAllRoles' => Response::HTTP_FORBIDDEN],
            ],
        ];
    }
}
