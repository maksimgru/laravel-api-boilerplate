<?php

namespace Tests\Api\Admin;

use App\Http\Controllers\Admin\UserController;
use App\Models\Role;
use App\Models\User;
use App\Constants\RoleConstants;
use Dingo\Api\Http\Response;
use Tests\ApiTestCase;

class UserControllerTest extends ApiTestCase
{
    /**
     * @covers       UserController::index()
     * @method       GET
     * @group        admin-user
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetListAnyUsersAs(
        array $input,
        array $expect
    ) {
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/users')
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetListAnyUsers']);
        if (Response::HTTP_OK === $expect['statusCodeGetListAnyUsers']) {
            $jsonResponse->assertJsonStructure(
                ['data' => [self::USER_RESPONSE_STRUCTURE]]
            );
        }
    }

    /**
     * @covers       UserController::getSingleUser()
     * @method       GET
     * @group        admin-user
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetSingleAnyUserAs(
        array $input,
        array $expect
    ) {
        foreach ($input['testUserIds'] as $userId) {
            // Get single user
            $jsonResponse = $this->actingAsAlias($input['actingAs'])
                ->getJson($this->apiPrefix . '/admin/users/' . $userId)
            ;
            // Check status and structure
            $jsonResponse->assertStatus($expect['statusCodeGetSingleAnyUser']);
            if (Response::HTTP_OK === $expect['statusCodeGetSingleAnyUser']) {
                $jsonResponse->assertJsonStructure(['data' => self::USER_RESPONSE_STRUCTURE]);
                $response = $jsonResponse->decodeResponseJson();
                // Check default(placeholder) media URLs
                $this->assertEquals(url(self::TEST_AVATAR_PLACEHOLDER_PATH), $response['data']['media']['avatar_urls']['origin']);
                foreach (self::USER_RESPONSE_STRUCTURE['media']['avatar_urls']['thumbs'] as $thumbSizeAlias) {
                    $this->assertEquals(url(self::TEST_AVATAR_PLACEHOLDER_PATH), $response['data']['media']['avatar_urls']['thumbs'][$thumbSizeAlias]);
                }
            }
        }
    }

    /**
     * @covers       UserController::postUser()
     * @method       POST
     * @group        admin-user
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testCreateAnyUserAs(
        array $input,
        array $expect
    ) {
        $allRolesNames = RoleConstants::ALL_ROLES;
        foreach ($allRolesNames as $roleName) {
            $fieldToCreate = ['primary_role_id' => Role::getIdByRoleName($roleName)];
            $testUser = factory(User::class)->make($fieldToCreate)->getAttributes();
            $jsonResponse = $this->actingAsAlias($input['actingAs'])
                ->postJson($this->apiPrefix . '/admin/users', $testUser)
            ;
            // Check status, structure and data
            $jsonResponse->assertStatus($expect['statusCodeCreateAnyUser']);
            if (Response::HTTP_CREATED === $expect['statusCodeCreateAnyUser']) {
                $jsonResponse->assertJsonStructure(['data' => self::USER_RESPONSE_STRUCTURE]);
                $response = $jsonResponse->decodeResponseJson();
                $this->assertResponseNotContainsHiddenAttributes(User::class, $response['data']);
                // Check default(placeholder) media URLs
                $this->assertEquals(url(self::TEST_AVATAR_PLACEHOLDER_PATH), $response['data']['media']['avatar_urls']['origin']);
                foreach (self::USER_RESPONSE_STRUCTURE['media']['avatar_urls']['thumbs'] as $thumbSizeAlias) {
                    $this->assertEquals(url(self::TEST_AVATAR_PLACEHOLDER_PATH), $response['data']['media']['avatar_urls']['thumbs'][$thumbSizeAlias]);
                }
            }
        }
    }

    /**
     * @covers       UserController::updateUser()
     * @method       POST
     * @group        admin-user
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testUpdateAnyUserAs(
        array $input,
        array $expect
    ) {
        $fieldToUpdate = $input['testUserFieldToUpdate'];
        foreach ($input['testUserIds'] as $userId) {
            // Update user
            $updatedUserResponse = $this->actingAsAlias($input['actingAs'])
                ->postJson($this->apiPrefix . '/admin/users/' . $userId, $fieldToUpdate)
            ;
            // Check status, structure and updated data
            $updatedUserResponse->assertStatus($expect['statusCodeUpdateAnyUser']);
            if (Response::HTTP_OK === $expect['statusCodeUpdateAnyUser']) {
                $updatedUserResponse->assertJsonStructure(['data' => self::USER_RESPONSE_STRUCTURE]);
                $updatedUserResponseArr = $updatedUserResponse->decodeResponseJson();
                // Check Updated fields value
                foreach ($fieldToUpdate as $key => $value) {
                    $this->assertArrayHasKey($key, $updatedUserResponseArr['data']);
                    $this->assertEquals($value, $updatedUserResponseArr['data'][$key]);
                }
                $this->assertResponseNotContainsHiddenAttributes(User::class, $updatedUserResponseArr['data']);
                // Check default(placeholder) media URLs
                $this->assertEquals(url(self::TEST_AVATAR_PLACEHOLDER_PATH), $updatedUserResponseArr['data']['media']['avatar_urls']['origin']);
                foreach (self::USER_RESPONSE_STRUCTURE['media']['avatar_urls']['thumbs'] as $thumbSizeAlias) {
                    $this->assertEquals(url(self::TEST_AVATAR_PLACEHOLDER_PATH), $updatedUserResponseArr['data']['media']['avatar_urls']['thumbs'][$thumbSizeAlias]);
                }
            }
        }
    }

    /**
     * @covers       UserController::updateUser()
     * @method       POST
     * @group        admin-user
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testPossibilityChangeBalanceForChildUserByUpdateProfileAs(
        array $input,
        array $expect
    )  {
        $fieldToUpdate = ['balance' => 99];
        foreach ($input['testUserIdsToUpdateBalance'] as $userId) {
            $jsonResponse = $this->actingAsAlias($input['actingAs'])
                ->postJson($this->apiPrefix . '/admin/users/' . $userId, $fieldToUpdate)
            ;
            // Check status
            $jsonResponse->assertStatus($expect['statusCodeUpdateBalanceChildUser']);
            $response = $jsonResponse->decodeResponseJson();
            if (Response::HTTP_OK === $expect['statusCodeUpdateBalanceChildUser']) {
                // Check updated value in response
                $this->assertArrayHasKey('properties', $response['data']);
                $this->assertArrayHasKey('balance', $response['data']['properties']);
                $this->assertEquals($fieldToUpdate['balance'], $response['data']['properties']['balance']);
            }
        }
    }

    /**
     * @covers       UserController::updateUser()
     * @method       POST
     * @group        admin-user
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testPossibilityChangeCommissionForChildUserByUpdateProfileAs(
        array $input,
        array $expect
    )  {
        $fieldToUpdate = ['commission' => 2.5];
        foreach ($input['testUserIdsToUpdateCommission'] as $userId) {
            $jsonResponse = $this->actingAsAlias($input['actingAs'])
                ->postJson($this->apiPrefix . '/admin/users/' . $userId, $fieldToUpdate)
            ;
            // Check status
            $jsonResponse->assertStatus($expect['statusCodeUpdateCommissionChildUser']);
            $response = $jsonResponse->decodeResponseJson();
            if (Response::HTTP_UNPROCESSABLE_ENTITY === $expect['statusCodeUpdateCommissionChildUser']) {
                // Check response message
                $this->assertArrayHasKey('message', $response);
                $this->assertStringContainsStringIgnoringCase('Updating the "commission" attribute is not allowed', $response['message']);
            }
            if (Response::HTTP_OK === $expect['statusCodeUpdateCommissionChildUser']) {
                // Check updated value in response
                $this->assertArrayHasKey('properties', $response['data']);
                $this->assertArrayHasKey('commission', $response['data']['properties']);
                $this->assertEquals($fieldToUpdate['commission'], $response['data']['properties']['commission']);
            }
        }
    }

    /**
     * @covers       UserController::deleteUser()
     * @method       DELETE
     * @group        admin-user
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testSoftDeleteAnyUserAs(
        array $input,
        array $expect
    ) {
        // Dont delete Admin user
        unset($input['testUserIds']['admin']);
        foreach ($input['testUserIds'] as $userId) {
            // Soft Delete user
            $jsonResponse = $this->actingAsAlias($input['actingAs'])
                ->deleteJson($this->apiPrefix . '/admin/users/' . $userId)
            ;
            // Check status
            $jsonResponse->assertStatus($expect['statusCodeDeleteAnyUser']);
            if (Response::HTTP_NO_CONTENT === $expect['statusCodeDeleteAnyUser']) {
                // Check that user exists in DB with deleted_at NOT NULL
                $softDeletedUser = User::onlyTrashed()->find($userId);
                $this->assertNotNull($softDeletedUser);
                $this->assertNotNull($softDeletedUser->deleted_at);
                $this->assertEquals($userId, $softDeletedUser->getKey());
            }
        }
    }

    /**
     * @covers       UserController::forceDelete()
     * @method       DELETE
     * @group        admin-user
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testForceDeleteUserAs(
        array $input,
        array $expect
    ) {
        // Force Delete user
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->deleteJson($this->apiPrefix . '/admin/users/force/' . self::TEST_FOR_DELETE_USER_ID)
        ;
        // Check status
        $jsonResponse->assertStatus($expect['statusCodeForceDeleteUser']);
        if (Response::HTTP_NO_CONTENT === $expect['statusCodeForceDeleteUser']) {
            // Check that user NOT exists in DB
            $forceDeletedUser = User::withTrashed()->find(self::TEST_FOR_DELETE_USER_ID);
            $this->assertNull($forceDeletedUser);
        }
    }

    /**
     * @covers       UserController::restoreUser()
     * @method       GET
     * @group        admin-user
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testRestoreSoftDeletedUserAs(
        array $input,
        array $expect
    ) {
        // Soft Delete user
        $this->actingAsAlias($input['actingAs'])
            ->deleteJson($this->apiPrefix . '/admin/users/' . self::TEST_FOR_DELETE_USER_ID)
        ;
        // Restore soft deleted user
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/users/restore/' . self::TEST_FOR_DELETE_USER_ID)
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeRestoreSoftDeleteUser']);
        if (Response::HTTP_OK === $expect['statusCodeRestoreSoftDeleteUser']) {
            $jsonResponse->assertJsonStructure(['data' => self::USER_RESPONSE_STRUCTURE]);
            $response = $jsonResponse->decodeResponseJson();
            $this->assertEquals(self::TEST_FOR_DELETE_USER_ID, $response['data']['id']);
            $restoredUser = User::withTrashed()->find(self::TEST_FOR_DELETE_USER_ID);
            $this->assertNull($restoredUser->deleted_at);
        }
    }

    /**
     * @covers       UserController::getSoftDeletedUsers()
     * @method       GET
     * @group        admin-user
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetListSoftDeletedUsersAs(
        array $input,
        array $expect
    ) {
        // Soft Delete user
        $this->actingAsAlias($input['actingAs'])
            ->deleteJson($this->apiPrefix . '/admin/users/' . self::TEST_FOR_DELETE_USER_ID)
        ;
        // Get Trashed Users (soft deleted)
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/users/trash')
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetListSoftDeletedUsers']);
        if (Response::HTTP_OK === $expect['statusCodeGetListSoftDeletedUsers']) {
            $jsonResponse->assertJsonStructure(['data' => [self::USER_RESPONSE_STRUCTURE]]);
            $response = $jsonResponse->decodeResponseJson();
            $this->assertCount(1, $response['data']);
            $this->assertEquals(self::TEST_FOR_DELETE_USER_ID, $response['data'][0]['id']);
        }
    }

    /**
     * @covers       UserController::getSingleSoftDeletedUser()
     * @method       GET
     * @group        admin-user
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetSingleSoftDeletedUserAs(
        array $input,
        array $expect
    ) {
        // Soft Delete user
        $this->actingAsAlias($input['actingAs'])
            ->deleteJson($this->apiPrefix . '/admin/users/' . self::TEST_FOR_DELETE_USER_ID)
        ;
        // Get single soft deleted user
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/users/trash/' . self::TEST_FOR_DELETE_USER_ID)
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetSingleSoftDeletedUser']);
        if (Response::HTTP_OK === $expect['statusCodeGetSingleSoftDeletedUser']) {
            $jsonResponse->assertJsonStructure(['data' => self::USER_RESPONSE_STRUCTURE]);
            $response = $jsonResponse->decodeResponseJson();
            $this->assertEquals(self::TEST_FOR_DELETE_USER_ID, $response['data']['id']);
        }
    }

    /**
     * @covers       UserController::getOwnBusinessUsers()
     * @method       GET
     * @group        admin-manager
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetListOwnBusinessUsersAs(
        array $input,
        array $expect
    ) {
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/manager/businesses')
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetListOwnBusinessUsers']);
        if (Response::HTTP_OK === $expect['statusCodeGetListOwnBusinessUsers']) {
            $jsonResponse->assertJsonStructure(
                ['data' => [self::USER_RESPONSE_STRUCTURE]]
            );
        }
    }

    /**
     * @covers       UserController::getOwnBusinessWorkerUsers()
     * @method       GET
     * @group        admin-manager
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetListOwnBusinessWorkerUsersAs(
        array $input,
        array $expect
    ) {
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/manager/workers')
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetListOwnBusinessUsers']);
        if (Response::HTTP_OK === $expect['statusCodeGetListOwnBusinessUsers']) {
            $jsonResponse->assertJsonStructure(
                ['data' => [self::USER_RESPONSE_STRUCTURE]]
            );
        }
    }

    /**
     * @covers       UserController::getSingleOwnBusinessUser()
     * @method       GET
     * @group        admin-manager
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetSingleOwnBusinessUserAs(
        array $input,
        array $expect
    ) {
        // Get single user
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/manager/businesses/' . $input['testUserIds']['business'])
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetSingleOwnBusinessUser']);
        if (Response::HTTP_OK === $expect['statusCodeGetSingleOwnBusinessUser']) {
            $jsonResponse->assertJsonStructure(
                ['data' => self::USER_RESPONSE_STRUCTURE]
            );
            $jsonResponseArr = $jsonResponse->decodeResponseJson();
            $this->assertEquals(RoleConstants::ROLE_BUSINESS, $jsonResponseArr['data']['primary_role']['name']);
        }
    }

    /**
     * @covers       UserController::getSingleOwnBusinessWorkerUser()
     * @method       GET
     * @group        admin-manager
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetSingleOwnBusinessWorkerUserAs(
        array $input,
        array $expect
    ) {
        // Get single user
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/manager/workers/' . $input['testUserIds']['worker'])
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetSingleOwnBusinessUser']);
        if (Response::HTTP_OK === $expect['statusCodeGetSingleOwnBusinessUser']) {
            $jsonResponse->assertJsonStructure(
                ['data' => self::USER_RESPONSE_STRUCTURE]
            );
            $jsonResponseArr = $jsonResponse->decodeResponseJson();
            $this->assertEquals(RoleConstants::ROLE_WORKER, $jsonResponseArr['data']['primary_role']['name']);
        }
    }

    /**
     * @covers       UserController::postOwnBusinessUser()
     * @method       POST
     * @group        admin-manager
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testCreateBusinessUserAs(
        array $input,
        array $expect
    ) {
        $fieldToCreate = ['primary_role_id' => Role::getIdByRoleName(RoleConstants::ROLE_BUSINESS)];
        $testUser = factory(User::class)->make($fieldToCreate)->getAttributes();
        $actingAs = $this->actingAsAlias($input['actingAs']);
        $testUser['manager_id'] = $actingAs->authUserId();
        $testUser['balance'] = 12.99;
        $testUser['commission'] = 2.5;
        $jsonResponse = $actingAs->postJson($this->apiPrefix . '/admin/manager/businesses', $testUser);
        $jsonResponse->assertStatus($expect['statusCodeCreateBusinessUser']);
        if (Response::HTTP_CREATED === $expect['statusCodeCreateBusinessUser']) {
            $jsonResponse->assertJsonStructure(['data' => self::USER_RESPONSE_STRUCTURE]);
            // Check Role and Related Manager of created business user
            $jsonResponseArr = $jsonResponse->decodeResponseJson();
            $this->assertEquals(RoleConstants::ROLE_BUSINESS, $jsonResponseArr['data']['primary_role']['name']);
            $this->assertEquals($actingAs->authUserId(), $jsonResponseArr['data']['manager_id']);
            $this->assertNull($jsonResponseArr['data']['business_id']);
            $this->assertEquals($testUser['balance'], $jsonResponseArr['data']['properties']['balance']);
            $this->assertEquals($testUser['commission'], $jsonResponseArr['data']['properties']['commission']);
        }
    }

    /**
     * @covers       UserController::getManagerVisitedTouristUsers()
     * @method       GET
     * @group        admin-business
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetListVisitedTouristUsersForManagerAs(
        array $input,
        array $expect
    ) {
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/manager/tourists/visited');
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetListVisitedTouristForManagerUsers']);
        if (Response::HTTP_OK === $expect['statusCodeGetListVisitedTouristForManagerUsers']) {
            $jsonResponse->assertJsonStructure(['data' => [self::USER_RESPONSE_STRUCTURE]]);
            $response = $jsonResponse->decodeResponseJson();
            $this->assertCount($expect['totalVisitedTourists'], $response['data']);
        }
    }

    /**
     * @covers       UserController::getManagerReferralTouristUsers()
     * @method       GET
     * @group        admin-business
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetListReferralTouristUsersForManagerAs(
        array $input,
        array $expect
    ) {
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/manager/tourists/referrals');
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetListReferralTouristForManagerUsers']);
        if (Response::HTTP_OK === $expect['statusCodeGetListReferralTouristForManagerUsers']) {
            $jsonResponse->assertJsonStructure(['data' => [self::USER_RESPONSE_STRUCTURE]]);
            $response = $jsonResponse->decodeResponseJson();
            $this->assertCount($expect['totalReferralsTourists'], $response['data']);
        }
    }

    /**
     * @covers       UserController::getOwnWorkerUsers()
     * @method       GET
     * @group        admin-business
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetListOwnWorkerUsersAs(
        array $input,
        array $expect
    ) {
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/business/workers')
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetListOwnWorkerUsers']);
        if (Response::HTTP_OK === $expect['statusCodeGetListOwnWorkerUsers']) {
            $jsonResponse->assertJsonStructure(
                ['data' => [self::USER_RESPONSE_STRUCTURE]]
            );
        }
    }

    /**
     * @covers       UserController::getSingleOwnWorkerUser()
     * @method       GET
     * @group        admin-business
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetSingleOwnWorkerUserAs(
        array $input,
        array $expect
    ) {
        // Get single user
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/business/workers/' . $input['testUserIds']['worker'])
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetSingleOwnWorkerUser']);
        if (Response::HTTP_OK === $expect['statusCodeGetSingleOwnWorkerUser']) {
            $jsonResponse->assertJsonStructure(
                ['data' => self::USER_RESPONSE_STRUCTURE]
            );
            $jsonResponseArr = $jsonResponse->decodeResponseJson();
            $this->assertEquals(RoleConstants::ROLE_WORKER, $jsonResponseArr['data']['primary_role']['name']);
        }
    }

    /**
     * @covers       UserController::postOwnWorkerUser()
     * @method       POST
     * @group        admin-business
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testCreateWorkerUserAs(
        array $input,
        array $expect
    ) {
        $fieldToCreate = ['primary_role_id' => Role::getIdByRoleName(RoleConstants::ROLE_WORKER)];
        $testUser = factory(User::class)->make($fieldToCreate)->getAttributes();
        $actingAs = $this->actingAsAlias($input['actingAs']);
        $testUser['business_id'] = $actingAs->authUserId();
        $jsonResponse = $actingAs->postJson($this->apiPrefix . '/admin/business/workers', $testUser);
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeCreateWorkerUser']);
        if (Response::HTTP_CREATED === $expect['statusCodeCreateWorkerUser']) {
            $jsonResponse->assertJsonStructure(['data' => self::USER_RESPONSE_STRUCTURE]);
            // Check Role and Related Business of created worker user
            $jsonResponseArr = $jsonResponse->decodeResponseJson();
            $this->assertEquals(RoleConstants::ROLE_WORKER, $jsonResponseArr['data']['primary_role']['name']);
            $this->assertEquals($actingAs->authUserId(), $jsonResponseArr['data']['business_id']);
            $this->assertNull($jsonResponseArr['data']['manager_id']);
        }
    }

    /**
     * @covers       UserController::getBusinessVisitedTouristUsers()
     * @method       GET
     * @group        admin-business
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetListVisitedTouristUsersForBusinessAs(
        array $input,
        array $expect
    ) {
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/business/tourists/visited');
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetListVisitedTouristForBusinessUsers']);
        if (Response::HTTP_OK === $expect['statusCodeGetListVisitedTouristForBusinessUsers']) {
            $jsonResponse->assertJsonStructure(['data' => [self::USER_RESPONSE_STRUCTURE]]);
            $response = $jsonResponse->decodeResponseJson();
            $this->assertCount($expect['totalVisitedTourists'], $response['data']);
        }
    }

    /**
     * @covers       UserController::getBusinessReferralTouristUsers()
     * @method       GET
     * @group        admin-business
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetListReferralTouristUsersForBusinessAs(
        array $input,
        array $expect
    ) {
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/business/tourists/referrals');
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetListReferralTouristForBusinessUsers']);
        if (Response::HTTP_OK === $expect['statusCodeGetListReferralTouristForBusinessUsers']) {
            $jsonResponse->assertJsonStructure(['data' => [self::USER_RESPONSE_STRUCTURE]]);
            $response = $jsonResponse->decodeResponseJson();
            $this->assertCount($expect['totalReferralsTourists'], $response['data']);
        }
    }

    /**
     * @covers       UserController::getActiveTouristUsers()
     * @method       GET
     * @group        admin-user
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetListActiveTouristUsersAs(
        array $input,
        array $expect
    ) {
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/users/tourists');
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetListActiveTouristUsers']);
        if (Response::HTTP_OK === $expect['statusCodeGetListActiveTouristUsers']) {
            $jsonResponse->assertJsonStructure(
                ['data' => [self::USER_RESPONSE_STRUCTURE]]
            );
            $response = $jsonResponse->decodeResponseJson();
            foreach ($response['data'] as $user) {
                $this->assertTrue($user['is_active']);
            }
        }
    }

    /**
     * @covers       UserController::getSingleActiveTouristUser()
     * @method       GET
     * @group        admin-user
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetSingleActiveTouristUserAs(
        array $input,
        array $expect
    ) {
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/users/tourists/' . self::TEST_TOURIST_ID)
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetSingleActiveTouristUser']);
        if (Response::HTTP_OK === $expect['statusCodeGetSingleActiveTouristUser']) {
            $jsonResponse->assertJsonStructure(
                ['data' => self::USER_RESPONSE_STRUCTURE]
            );
            $response = $jsonResponse->decodeResponseJson();
            $this->assertEquals(RoleConstants::ROLE_TOURIST, $response['data']['primary_role']['name']);
            $this->assertTrue($response['data']['is_active']);
        }
    }

    /**
     * @covers       UserController::getSingleActiveTouristUser()
     * @method       GET
     * @group        admin-user
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetSingleNotActiveTouristUserAs(
        array $input,
        array $expect
    ) {
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/users/tourists/' . self::TEST_NOT_ACTIVE_TOURIST_ID)
        ;
        // Check status
        $jsonResponse->assertStatus($expect['statusCodeGetSingleNotActiveTouristUser']);
    }

    /**
     * @return array
     */
    public function dataSet(): array
    {
        return [
            // Guest\NotAuth
            [
                array_merge(
                    ['actingAs' => ''],
                    ['testUserIds' => self::TEST_USERS_IDS],
                    ['testUserFieldToUpdate' => self::TEST_USER_FIELD_TO_UPDATE],
                    ['testUserIdsToUpdateBalance' => self::TEST_USERS_IDS],
                    ['testUserIdsToUpdateCommission' => self::TEST_USERS_IDS]
                ),
                [
                    'statusCodeGetListAnyUsers'                         => Response::HTTP_UNAUTHORIZED,
                    'statusCodeGetSingleAnyUser'                        => Response::HTTP_UNAUTHORIZED,
                    'statusCodeCreateAnyUser'                           => Response::HTTP_UNAUTHORIZED,
                    'statusCodeUpdateAnyUser'                           => Response::HTTP_UNAUTHORIZED,
                    'statusCodeDeleteAnyUser'                           => Response::HTTP_UNAUTHORIZED,
                    'statusCodeForceDeleteUser'                         => Response::HTTP_UNAUTHORIZED,
                    'statusCodeRestoreSoftDeleteUser'                   => Response::HTTP_UNAUTHORIZED,
                    'statusCodeGetListSoftDeletedUsers'                 => Response::HTTP_UNAUTHORIZED,
                    'statusCodeGetSingleSoftDeletedUser'                => Response::HTTP_UNAUTHORIZED,
                    'statusCodeGetListOwnBusinessUsers'                 => Response::HTTP_UNAUTHORIZED,
                    'statusCodeGetSingleOwnBusinessUser'                => Response::HTTP_UNAUTHORIZED,
                    'statusCodeCreateBusinessUser'                      => Response::HTTP_UNAUTHORIZED,
                    'statusCodeGetListOwnWorkerUsers'                   => Response::HTTP_UNAUTHORIZED,
                    'statusCodeGetSingleOwnWorkerUser'                  => Response::HTTP_UNAUTHORIZED,
                    'statusCodeCreateWorkerUser'                        => Response::HTTP_UNAUTHORIZED,
                    'statusCodeUpdateBalanceChildUser'                  => Response::HTTP_UNAUTHORIZED,
                    'statusCodeUpdateCommissionChildUser'               => Response::HTTP_UNAUTHORIZED,
                    'statusCodeGetListVisitedTouristForManagerUsers'    => Response::HTTP_UNAUTHORIZED,
                    'statusCodeGetListVisitedTouristForBusinessUsers'   => Response::HTTP_UNAUTHORIZED,
                    'statusCodeGetListReferralTouristForManagerUsers'   => Response::HTTP_UNAUTHORIZED,
                    'statusCodeGetListReferralTouristForBusinessUsers'  => Response::HTTP_UNAUTHORIZED,
                    'statusCodeGetListActiveTouristUsers'               => Response::HTTP_UNAUTHORIZED,
                    'statusCodeGetSingleActiveTouristUser'              => Response::HTTP_UNAUTHORIZED,
                    'statusCodeGetSingleNotActiveTouristUser'           => Response::HTTP_UNAUTHORIZED,
                    'totalVisitedTourists'                              => 0,
                    'totalReferralsTourists'                            => 0,
                ],
            ],
            // Admin
            [
                array_merge(
                    ['actingAs' => RoleConstants::ROLE_ADMIN],
                    ['testUserIds' => self::TEST_USERS_IDS],
                    ['testUserFieldToUpdate' => self::TEST_USER_FIELD_TO_UPDATE],
                    ['testUserIdsToUpdateBalance' => self::TEST_USERS_IDS],
                    ['testUserIdsToUpdateCommission' => self::TEST_USERS_IDS]
                ),
                [
                    'statusCodeGetListAnyUsers'                         => Response::HTTP_OK,
                    'statusCodeGetSingleAnyUser'                        => Response::HTTP_OK,
                    'statusCodeCreateAnyUser'                           => Response::HTTP_CREATED,
                    'statusCodeUpdateAnyUser'                           => Response::HTTP_OK,
                    'statusCodeDeleteAnyUser'                           => Response::HTTP_NO_CONTENT,
                    'statusCodeForceDeleteUser'                         => Response::HTTP_NO_CONTENT,
                    'statusCodeRestoreSoftDeleteUser'                   => Response::HTTP_OK,
                    'statusCodeGetListSoftDeletedUsers'                 => Response::HTTP_OK,
                    'statusCodeGetSingleSoftDeletedUser'                => Response::HTTP_OK,
                    'statusCodeGetListOwnBusinessUsers'                 => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleOwnBusinessUser'                => Response::HTTP_FORBIDDEN,
                    'statusCodeCreateBusinessUser'                      => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListOwnWorkerUsers'                   => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleOwnWorkerUser'                  => Response::HTTP_FORBIDDEN,
                    'statusCodeCreateWorkerUser'                        => Response::HTTP_FORBIDDEN,
                    'statusCodeUpdateBalanceChildUser'                  => Response::HTTP_OK,
                    'statusCodeUpdateCommissionChildUser'               => Response::HTTP_OK,
                    'statusCodeGetListVisitedTouristForManagerUsers'    => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListVisitedTouristForBusinessUsers'   => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListReferralTouristForManagerUsers'   => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListReferralTouristForBusinessUsers'  => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListActiveTouristUsers'               => Response::HTTP_OK,
                    'statusCodeGetSingleActiveTouristUser'              => Response::HTTP_OK,
                    'statusCodeGetSingleNotActiveTouristUser'           => Response::HTTP_BAD_REQUEST,
                    'totalVisitedTourists'                              => 0,
                    'totalReferralsTourists'                            => 0,
                ],
            ],
            // Manager
            [
                array_merge(
                    ['actingAs' => RoleConstants::ROLE_MANAGER],
                    ['testUserIds' => self::TEST_USERS_IDS],
                    ['testUserFieldToUpdate' => self::TEST_USER_FIELD_TO_UPDATE],
                    ['testUserIdsToUpdateBalance' => [self::TEST_BUSINESS_ID]],
                    ['testUserIdsToUpdateCommission' => [self::TEST_BUSINESS_ID]]
                ),
                [
                    'statusCodeGetListAnyUsers'                         => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleAnyUser'                        => Response::HTTP_FORBIDDEN,
                    'statusCodeCreateAnyUser'                           => Response::HTTP_FORBIDDEN,
                    'statusCodeUpdateAnyUser'                           => Response::HTTP_FORBIDDEN,
                    'statusCodeDeleteAnyUser'                           => Response::HTTP_FORBIDDEN,
                    'statusCodeForceDeleteUser'                         => Response::HTTP_FORBIDDEN,
                    'statusCodeRestoreSoftDeleteUser'                   => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListSoftDeletedUsers'                 => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleSoftDeletedUser'                => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListOwnBusinessUsers'                 => Response::HTTP_OK,
                    'statusCodeGetSingleOwnBusinessUser'                => Response::HTTP_OK,
                    'statusCodeCreateBusinessUser'                      => Response::HTTP_CREATED,
                    'statusCodeGetListOwnWorkerUsers'                   => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleOwnWorkerUser'                  => Response::HTTP_FORBIDDEN,
                    'statusCodeCreateWorkerUser'                        => Response::HTTP_FORBIDDEN,
                    'statusCodeUpdateBalanceChildUser'                  => Response::HTTP_FORBIDDEN,
                    'statusCodeUpdateCommissionChildUser'               => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListVisitedTouristForManagerUsers'    => Response::HTTP_OK,
                    'statusCodeGetListVisitedTouristForBusinessUsers'   => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListReferralTouristForManagerUsers'   => Response::HTTP_OK,
                    'statusCodeGetListReferralTouristForBusinessUsers'  => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListActiveTouristUsers'               => Response::HTTP_OK,
                    'statusCodeGetSingleActiveTouristUser'              => Response::HTTP_OK,
                    'statusCodeGetSingleNotActiveTouristUser'           => Response::HTTP_BAD_REQUEST,
                    'totalVisitedTourists'                              => 2,
                    'totalReferralsTourists'                            => 1,
                ],
            ],
            // Business
            [
                array_merge(
                    ['actingAs' => RoleConstants::ROLE_BUSINESS],
                    ['testUserIds' => self::TEST_USERS_IDS],
                    ['testUserFieldToUpdate' => self::TEST_USER_FIELD_TO_UPDATE],
                    ['testUserIdsToUpdateBalance' => [self::TEST_WORKER_ID]],
                    ['testUserIdsToUpdateCommission' => [self::TEST_WORKER_ID]]
                ),
                [
                    'statusCodeGetListAnyUsers'                         => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleAnyUser'                        => Response::HTTP_FORBIDDEN,
                    'statusCodeCreateAnyUser'                           => Response::HTTP_FORBIDDEN,
                    'statusCodeUpdateAnyUser'                           => Response::HTTP_FORBIDDEN,
                    'statusCodeDeleteAnyUser'                           => Response::HTTP_FORBIDDEN,
                    'statusCodeForceDeleteUser'                         => Response::HTTP_FORBIDDEN,
                    'statusCodeRestoreSoftDeleteUser'                   => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListSoftDeletedUsers'                 => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleSoftDeletedUser'                => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListOwnBusinessUsers'                 => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleOwnBusinessUser'                => Response::HTTP_FORBIDDEN,
                    'statusCodeCreateBusinessUser'                      => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListOwnWorkerUsers'                   => Response::HTTP_OK,
                    'statusCodeGetSingleOwnWorkerUser'                  => Response::HTTP_OK,
                    'statusCodeCreateWorkerUser'                        => Response::HTTP_CREATED,
                    'statusCodeUpdateBalanceChildUser'                  => Response::HTTP_FORBIDDEN,
                    'statusCodeUpdateCommissionChildUser'               => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListVisitedTouristForManagerUsers'    => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListVisitedTouristForBusinessUsers'   => Response::HTTP_OK,
                    'statusCodeGetListReferralTouristForManagerUsers'   => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListReferralTouristForBusinessUsers'  => Response::HTTP_OK,
                    'statusCodeGetListActiveTouristUsers'               => Response::HTTP_OK,
                    'statusCodeGetSingleActiveTouristUser'              => Response::HTTP_OK,
                    'statusCodeGetSingleNotActiveTouristUser'           => Response::HTTP_BAD_REQUEST,
                    'totalVisitedTourists'                              => 2,
                    'totalReferralsTourists'                            => 1,
                ],
            ],
            // Worker
            [
                array_merge(
                    ['actingAs' => RoleConstants::ROLE_WORKER],
                    ['testUserIds' => self::TEST_USERS_IDS],
                    ['testUserFieldToUpdate' => self::TEST_USER_FIELD_TO_UPDATE],
                    ['testUserIdsToUpdateBalance' => self::TEST_USERS_IDS],
                    ['testUserIdsToUpdateCommission' => self::TEST_USERS_IDS]
                ),
                [
                    'statusCodeGetListAnyUsers'                         => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleAnyUser'                        => Response::HTTP_FORBIDDEN,
                    'statusCodeCreateAnyUser'                           => Response::HTTP_FORBIDDEN,
                    'statusCodeUpdateAnyUser'                           => Response::HTTP_FORBIDDEN,
                    'statusCodeDeleteAnyUser'                           => Response::HTTP_FORBIDDEN,
                    'statusCodeForceDeleteUser'                         => Response::HTTP_FORBIDDEN,
                    'statusCodeRestoreSoftDeleteUser'                   => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListSoftDeletedUsers'                 => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleSoftDeletedUser'                => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListOwnBusinessUsers'                 => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleOwnBusinessUser'                => Response::HTTP_FORBIDDEN,
                    'statusCodeCreateBusinessUser'                      => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListOwnWorkerUsers'                   => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleOwnWorkerUser'                  => Response::HTTP_FORBIDDEN,
                    'statusCodeCreateWorkerUser'                        => Response::HTTP_FORBIDDEN,
                    'statusCodeUpdateBalanceChildUser'                  => Response::HTTP_FORBIDDEN,
                    'statusCodeUpdateCommissionChildUser'               => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListVisitedTouristForManagerUsers'    => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListVisitedTouristForBusinessUsers'   => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListReferralTouristForManagerUsers'   => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListReferralTouristForBusinessUsers'  => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListActiveTouristUsers'               => Response::HTTP_OK,
                    'statusCodeGetSingleActiveTouristUser'              => Response::HTTP_OK,
                    'statusCodeGetSingleNotActiveTouristUser'           => Response::HTTP_BAD_REQUEST,
                    'totalVisitedTourists'                              => 2,
                    'totalReferralsTourists'                            => 1,
                ],
            ],
            // Tourist
            [
                array_merge(
                    ['actingAs' => RoleConstants::ROLE_TOURIST],
                    ['testUserIds' => self::TEST_USERS_IDS],
                    ['testUserFieldToUpdate' => self::TEST_USER_FIELD_TO_UPDATE],
                    ['testUserIdsToUpdateBalance' => self::TEST_USERS_IDS],
                    ['testUserIdsToUpdateCommission' => self::TEST_USERS_IDS]
                ),
                [
                    'statusCodeGetListAnyUsers'                         => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleAnyUser'                        => Response::HTTP_FORBIDDEN,
                    'statusCodeCreateAnyUser'                           => Response::HTTP_FORBIDDEN,
                    'statusCodeUpdateAnyUser'                           => Response::HTTP_FORBIDDEN,
                    'statusCodeDeleteAnyUser'                           => Response::HTTP_FORBIDDEN,
                    'statusCodeForceDeleteUser'                         => Response::HTTP_FORBIDDEN,
                    'statusCodeRestoreSoftDeleteUser'                   => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListSoftDeletedUsers'                 => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleSoftDeletedUser'                => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListOwnBusinessUsers'                 => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleOwnBusinessUser'                => Response::HTTP_FORBIDDEN,
                    'statusCodeCreateBusinessUser'                      => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListOwnWorkerUsers'                   => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleOwnWorkerUser'                  => Response::HTTP_FORBIDDEN,
                    'statusCodeCreateWorkerUser'                        => Response::HTTP_FORBIDDEN,
                    'statusCodeUpdateBalanceChildUser'                  => Response::HTTP_FORBIDDEN,
                    'statusCodeUpdateCommissionChildUser'               => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListVisitedTouristForManagerUsers'    => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListVisitedTouristForBusinessUsers'   => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListReferralTouristForManagerUsers'   => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListReferralTouristForBusinessUsers'  => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListActiveTouristUsers'               => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleActiveTouristUser'              => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleNotActiveTouristUser'           => Response::HTTP_FORBIDDEN,
                    'totalVisitedTourists'                              => 2,
                    'totalReferralsTourists'                            => 1,
                ],
            ],
        ];
    }
}
