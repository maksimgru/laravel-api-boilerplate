<?php

namespace Tests\Api\Front;

use App\Constants\MediaLibraryConstants;
use App\Http\Controllers\Front\UserController;
use App\Constants\RoleConstants;
use App\Models\User;
use Dingo\Api\Http\Response;
use Tests\ApiTestCase;

class UserControllerTest extends ApiTestCase
{
    /**
     * @covers       UserController::register()
     * @method       POST
     * @group        front-user
     */
    public function testRegisterUserAsTourist()
    {
        $testUser = factory(User::class)->make()->getAttributes();
        $jsonResponse = $this->postJson($this->apiPrefix . '/users/register', $testUser);
        // Check status, structure and data
        $jsonResponse
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(['data' => self::USER_RESPONSE_STRUCTURE])
        ;
        // Check role as default "tourist"
        $response = $jsonResponse->decodeResponseJson();
        $this->assertArrayHasKey('primary_role', $response['data']);
        $this->assertArrayHasKey('name', $response['data']['primary_role']);
        $this->assertEquals(RoleConstants::ROLE_TOURIST, $response['data']['primary_role']['name']);
        $this->assertResponseNotContainsHiddenAttributes(User::class, $response['data']);

        // Check default(placeholder) media URLs
        $this->assertEquals(url(self::TEST_AVATAR_PLACEHOLDER_PATH), $response['data']['media']['avatar_urls']['origin']);
        foreach (self::USER_RESPONSE_STRUCTURE['media']['avatar_urls']['thumbs'] as $thumbSizeAlias) {
            $this->assertEquals(url(self::TEST_AVATAR_PLACEHOLDER_PATH), $response['data']['media']['avatar_urls']['thumbs'][$thumbSizeAlias]);
        }
    }

    /**
     * @covers       UserController::getCurrentUser()
     * @method       GET
     * @group        front-user
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetOwnProfileAs(
        array $input,
        array $expect
    )  {
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/users/me')
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCode']);
        if (Response::HTTP_OK === $expect['statusCode']) {
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

    /**
     * @covers       UserController::updateCurrentUser()
     * @method       POST
     * @group        front-user
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testUpdateOwnProfileAs(
        array $input,
        array $expect
    )  {
        $fieldToUpdate = $input['fieldsToUpdate'];
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->postJson($this->apiPrefix . '/users/me', $fieldToUpdate)
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCode']);
        if (Response::HTTP_OK === $expect['statusCode']) {
            $jsonResponse->assertJsonStructure(['data' => self::USER_RESPONSE_STRUCTURE]);
            $response = $jsonResponse->decodeResponseJson();
            // Check Updated fields value
            foreach ($fieldToUpdate as $key => $value) {
                $this->assertArrayHasKey($key, $response['data']);
                $this->assertEquals($value, $response['data'][$key]);
            }
            $this->assertResponseNotContainsHiddenAttributes(User::class, $response['data']);
            // Check default(placeholder) media URLs
            $this->assertEquals(url(self::TEST_AVATAR_PLACEHOLDER_PATH), $response['data']['media']['avatar_urls']['origin']);
            foreach (self::USER_RESPONSE_STRUCTURE['media']['avatar_urls']['thumbs'] as $thumbSizeAlias) {
                $this->assertEquals(url(self::TEST_AVATAR_PLACEHOLDER_PATH), $response['data']['media']['avatar_urls']['thumbs'][$thumbSizeAlias]);
            }
        }
    }

    /**
     * @covers       UserController::updateCurrentUser()
     * @method       POST
     * @group        front-user
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testUploadAndDeleteAvatarInUserProfileAs(
        array $input,
        array $expect
    )  {
        $image = $this->createUploadedFile();
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->postJson(
                $this->apiPrefix . '/users/me',
                [MediaLibraryConstants::REQUEST_FIELD_NAME_AVATAR => $image]
            )
        ;
        // Check status upload file
        $jsonResponse->assertStatus($expect['statusCodeUploadAvatar']);
        if (Response::HTTP_OK === $expect['statusCodeUploadAvatar']) {
            // Check structure
            $jsonResponse->assertJsonStructure(['data' => self::USER_RESPONSE_STRUCTURE]);
            $response = $jsonResponse->decodeResponseJson();
            $mediaId = $response['data']['media']['avatar_urls']['id'];
            $this->assertNotNull($mediaId);
            $mediaPaths = $this->buildOriginAndConversionPathsForTestUploadedImage(
                $mediaId,
                $this->authUserId(),
                User::class
            );
            // Check uploaded file URLs and its thumbs URLs
            $this->assertEquals($mediaPaths['origin']['url'], $response['data']['media']['avatar_urls']['origin']);
            $this->assertEquals($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_SMALL]['url'], $response['data']['media']['avatar_urls']['thumbs'][MediaLibraryConstants::THUMB_SMALL]);
            $this->assertEquals($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_MEDIUM]['url'], $response['data']['media']['avatar_urls']['thumbs'][MediaLibraryConstants::THUMB_MEDIUM]);
            // Check uploaded file exists and its thumbs
            $this->assertFileExists($mediaPaths['origin']['storagePath']);
            $this->assertFileExists($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_SMALL]['storagePath']);
            $this->assertFileExists($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_MEDIUM]['storagePath']);

            // Check Delete uploaded file and its thumbs
            $jsonResponse = $this->actingAsAlias($input['actingAs'])
                ->deleteJson($this->apiPrefix . '/users/me/media/' . $mediaId)
            ;
            $jsonResponse->assertStatus($expect['statusCodeDeleteAvatar']);
            $this->assertFileNotExists($mediaPaths['origin']['storagePath']);
            $this->assertFileNotExists($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_SMALL]['storagePath']);
            $this->assertFileNotExists($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_MEDIUM]['storagePath']);
        }
    }

    /**
     * @covers       UserController::getFavoriteVisitPlaces()
     * @method       POST
     * @group        front-user
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetFavoriteVisitPlacesInOwnProfileAs(
        array $input,
        array $expect
    ) {
        $fieldToUpdate = ['favorite_visit_places' => self::TEST_FAVORITE_VISIT_PLACES_IDS];
        $this->actingAsAlias($input['actingAs'])
            ->postJson($this->apiPrefix . '/users/me/add_favorite_visit_places', $fieldToUpdate)
        ;

        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/users/me/get_favorite_visit_places')
        ;

        // Check status
        $jsonResponse->assertStatus($expect['statusCodeAddFavoriteVisitPlaces']);
        if (Response::HTTP_OK === $expect['statusCodeAddFavoriteVisitPlaces']) {
            $jsonResponse->assertJsonStructure(['data' => [self::VISIT_PLACE_RESPONSE_STRUCTURE]]);
            $response = $jsonResponse->decodeResponseJson();
            $this->assertCount(\count($fieldToUpdate['favorite_visit_places']), $response['data']);
        }
    }

    /**
     * @covers       UserController::addFavoriteVisitPlaces()
     * @method       POST
     * @group        front-user
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testAddFavoriteVisitPlacesInOwnProfileAs(
        array $input,
        array $expect
    ) {
        $fieldToUpdate = ['favorite_visit_places' => self::TEST_FAVORITE_VISIT_PLACES_IDS];
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->postJson($this->apiPrefix . '/users/me/add_favorite_visit_places', $fieldToUpdate)
        ;
        // Check status
        $jsonResponse->assertStatus($expect['statusCodeAddFavoriteVisitPlaces']);
        $response = $jsonResponse->decodeResponseJson();
        if (Response::HTTP_OK === $expect['statusCodeAddFavoriteVisitPlaces']) {
            $this->assertEquals($fieldToUpdate['favorite_visit_places'], $response['data']['properties']['favorite_visit_places']);
        }
    }

    /**
     * @covers       UserController::removeFavoriteVisitPlace()
     * @method       POST
     * @group        front-user
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testRemoveFavoriteVisitPlaceInOwnProfileAs(
        array $input,
        array $expect
    ) {
        $fieldToUpdate = ['favorite_visit_places' => self::TEST_FAVORITE_VISIT_PLACES_IDS];
        $this->actingAsAlias($input['actingAs'])
            ->postJson($this->apiPrefix . '/users/me/add_favorite_visit_places', $fieldToUpdate)
        ;

        $visitPlaceIdToRemove = array_pop($fieldToUpdate['favorite_visit_places']);
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->deleteJson($this->apiPrefix . '/users/me/remove_favorite_visit_place/' . $visitPlaceIdToRemove)
        ;

        // Check status
        $jsonResponse->assertStatus($expect['statusCodeRemoveFavoriteVisitPlace']);
        //$response = $jsonResponse->decodeResponseJson();
        if (Response::HTTP_NO_CONTENT === $expect['statusCodeRemoveFavoriteVisitPlace']) {
            $this->assertEquals($fieldToUpdate['favorite_visit_places'], User::find($this->authUserId())->favorite_visit_places);
        }
    }

    /**
     * @return array
     */
    public function dataSet(): array
    {
        return [
            // Guest\NotAuth
            [
                [
                    'actingAs'       => '',
                    'fieldsToUpdate' => self::TEST_USER_FIELD_TO_UPDATE,
                ],
                [
                    'statusCode'                         => Response::HTTP_UNAUTHORIZED,
                    'statusCodeUploadAvatar'             => Response::HTTP_UNAUTHORIZED,
                    'statusCodeDeleteAvatar'             => Response::HTTP_UNAUTHORIZED,
                    'statusCodeAddFavoriteVisitPlaces'   => Response::HTTP_UNAUTHORIZED,
                    'statusCodeRemoveFavoriteVisitPlace' => Response::HTTP_UNAUTHORIZED,
                ],
            ],
            // Admin
            [
                [
                    'actingAs'       => RoleConstants::ROLE_ADMIN,
                    'fieldsToUpdate' => self::TEST_USER_FIELD_TO_UPDATE,
                ],
                [
                    'statusCode'                         => Response::HTTP_OK,
                    'statusCodeUploadAvatar'             => Response::HTTP_OK,
                    'statusCodeDeleteAvatar'             => Response::HTTP_NO_CONTENT,
                    'statusCodeAddFavoriteVisitPlaces'   => Response::HTTP_OK,
                    'statusCodeRemoveFavoriteVisitPlace' => Response::HTTP_NO_CONTENT,
                ],
            ],
            // Manager
            [
                [
                    'actingAs'       => RoleConstants::ROLE_MANAGER,
                    'fieldsToUpdate' => self::TEST_USER_FIELD_TO_UPDATE,
                ],
                [
                    'statusCode'                         => Response::HTTP_OK,
                    'statusCodeUploadAvatar'             => Response::HTTP_OK,
                    'statusCodeDeleteAvatar'             => Response::HTTP_NO_CONTENT,
                    'statusCodeAddFavoriteVisitPlaces'   => Response::HTTP_OK,
                    'statusCodeRemoveFavoriteVisitPlace' => Response::HTTP_NO_CONTENT,
                ],
            ],
            // Business
            [
                [
                    'actingAs'       => RoleConstants::ROLE_BUSINESS,
                    'fieldsToUpdate' => self::TEST_USER_FIELD_TO_UPDATE,
                ],
                [
                    'statusCode'                         => Response::HTTP_OK,
                    'statusCodeUploadAvatar'             => Response::HTTP_OK,
                    'statusCodeDeleteAvatar'             => Response::HTTP_NO_CONTENT,
                    'statusCodeAddFavoriteVisitPlaces'   => Response::HTTP_OK,
                    'statusCodeRemoveFavoriteVisitPlace' => Response::HTTP_NO_CONTENT,
                ],
            ],
            // Worker
            [
                [
                    'actingAs'       => RoleConstants::ROLE_WORKER,
                    'fieldsToUpdate' => self::TEST_USER_FIELD_TO_UPDATE,
                ],
                [
                    'statusCode'                         => Response::HTTP_OK,
                    'statusCodeUploadAvatar'             => Response::HTTP_OK,
                    'statusCodeDeleteAvatar'             => Response::HTTP_NO_CONTENT,
                    'statusCodeAddFavoriteVisitPlaces'   => Response::HTTP_OK,
                    'statusCodeRemoveFavoriteVisitPlace' => Response::HTTP_NO_CONTENT,
                ],
            ],
            // Tourist
            [
                [
                    'actingAs'       => RoleConstants::ROLE_TOURIST,
                    'fieldsToUpdate' => self::TEST_USER_FIELD_TO_UPDATE,
                ],
                [
                    'statusCode'                         => Response::HTTP_OK,
                    'statusCodeUploadAvatar'             => Response::HTTP_OK,
                    'statusCodeDeleteAvatar'             => Response::HTTP_NO_CONTENT,
                    'statusCodeAddFavoriteVisitPlaces'   => Response::HTTP_OK,
                    'statusCodeRemoveFavoriteVisitPlace' => Response::HTTP_NO_CONTENT,
                ],
            ],
        ];
    }
}
