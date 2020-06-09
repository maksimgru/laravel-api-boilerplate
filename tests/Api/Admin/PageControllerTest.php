<?php

namespace Tests\Api\Admin;

use App\Constants\MediaLibraryConstants;
use App\Http\Controllers\Admin\PageController;
use App\Models\Page;
use App\Constants\RoleConstants;
use Dingo\Api\Http\Response;
use Tests\ApiTestCase;

class PageControllerTest extends ApiTestCase
{
    /**
     * @covers       PageController::index()
     * @method       GET
     * @group        admin-page
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetListPagesAs(
        array $input,
        array $expect
    ) {
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/pages')
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetListPages']);
        if (Response::HTTP_OK === $expect['statusCodeGetListPages']) {
            $jsonResponse->assertJsonStructure(['data' => [self::PAGE_RESPONSE_STRUCTURE]]);
        }
    }

    /**
     * @covers       PageController::getSinglePage()
     * @method       GET
     * @group        admin-page
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetSinglePageAs(
        array $input,
        array $expect
    ) {
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/pages/' . self::TEST_PAGE_ID)
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetSinglePage']);
        if (Response::HTTP_OK === $expect['statusCodeGetSinglePage']) {
            $jsonResponse->assertJsonStructure(['data' => self::PAGE_RESPONSE_STRUCTURE]);
            $response = $jsonResponse->decodeResponseJson();
            // Check default(placeholder) media URLs
            $this->assertEquals(url(self::TEST_IMAGE_PLACEHOLDER_PATH), $response['data']['media']['main_image']['origin']);
            foreach (self::PAGE_RESPONSE_STRUCTURE['media']['main_image']['thumbs'] as $thumbSizeAlias) {
                $this->assertEquals(url(self::TEST_IMAGE_PLACEHOLDER_PATH), $response['data']['media']['main_image']['thumbs'][$thumbSizeAlias]);
            }
        }
    }

    /**
     * @covers       PageController::getSoftDeletedPages()
     * @method       GET
     * @group        admin-page
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetListSoftDeletedPagesAs(
        array $input,
        array $expect
    ) {
        // Soft Delete
        $this->actingAsAlias($input['actingAs'])
            ->deleteJson($this->apiPrefix . '/admin/pages/' . self::TEST_PAGE_ID)
        ;
        // Get Trashed (soft deleted)
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/pages/trash')
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetListSoftDeletedPages']);
        if (Response::HTTP_OK === $expect['statusCodeGetListSoftDeletedPages']) {
            $jsonResponse->assertJsonStructure(['data' => [self::PAGE_RESPONSE_STRUCTURE]]);
            $response = $jsonResponse->decodeResponseJson();
            $this->assertCount(1, $response['data']);
            $this->assertEquals(self::TEST_PAGE_ID, $response['data'][0]['id']);
        }
    }

    /**
     * @covers       PageController::getSingleSoftDeletedPage()
     * @method       GET
     * @group        admin-page
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetSingleSoftDeletedPageAs(
        array $input,
        array $expect
    ) {
        // Soft Delete
        $this->actingAsAlias($input['actingAs'])
            ->deleteJson($this->apiPrefix . '/admin/pages/' . self::TEST_PAGE_ID)
        ;
        // Get single soft deleted
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/pages/trash/' . self::TEST_PAGE_ID)
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetSingleSoftDeletedPage']);
        if (Response::HTTP_OK === $expect['statusCodeGetSingleSoftDeletedPage']) {
            $jsonResponse->assertJsonStructure(['data' => self::PAGE_RESPONSE_STRUCTURE]);
            $response = $jsonResponse->decodeResponseJson();
            $this->assertEquals(self::TEST_PAGE_ID, $response['data']['id']);
        }
    }

    /**
     * @covers       PageController::postPage()
     * @method       POST
     * @group        admin-page
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testCreatePageAs(
        array $input,
        array $expect
    ) {
        $testPage = factory(Page::class)->make()->getAttributes();
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->postJson($this->apiPrefix . '/admin/pages', $testPage)
        ;
        // Check status, structure and data
        $jsonResponse->assertStatus($expect['statusCodeCreatePage']);
        if (Response::HTTP_CREATED === $expect['statusCodeCreatePage']) {
            $jsonResponse->assertJsonStructure(['data' => self::PAGE_RESPONSE_STRUCTURE]);
            $response = $jsonResponse->decodeResponseJson();
            // Check default(placeholder) media URLs
            $this->assertEquals(url(self::TEST_IMAGE_PLACEHOLDER_PATH), $response['data']['media']['main_image']['origin']);
            foreach (self::PAGE_RESPONSE_STRUCTURE['media']['main_image']['thumbs'] as $thumbSizeAlias) {
                $this->assertEquals(url(self::TEST_IMAGE_PLACEHOLDER_PATH), $response['data']['media']['main_image']['thumbs'][$thumbSizeAlias]);
            }
        }
    }

    /**
     * @covers       PageController::updatePage()
     * @method       POST
     * @group        admin-page
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testUpdatePageAs(
        array $input,
        array $expect
    ) {
        $fieldToUpdate = $input['testPageFieldToUpdate'];
        $updatedPageResponse = $this->actingAsAlias($input['actingAs'])
            ->postJson($this->apiPrefix . '/admin/pages/' . self::TEST_PAGE_ID, $fieldToUpdate)
        ;
        // Check status, structure and updated data
        $updatedPageResponse->assertStatus($expect['statusCodeUpdatePage']);
        if (Response::HTTP_OK === $expect['statusCodeUpdatePage']) {
            $updatedPageResponse->assertJsonStructure(['data' => self::PAGE_RESPONSE_STRUCTURE]);
            $updatedPageResponseArr = $updatedPageResponse->decodeResponseJson();
            // Check Updated fields value
            foreach ($fieldToUpdate as $key => $value) {
                $this->assertArrayHasKey($key, $updatedPageResponseArr['data']);
                $this->assertEquals($value, $updatedPageResponseArr['data'][$key]);
            }
            // Check default(placeholder) media URLs
            $this->assertEquals(url(self::TEST_IMAGE_PLACEHOLDER_PATH), $updatedPageResponseArr['data']['media']['main_image']['origin']);
            foreach (self::PAGE_RESPONSE_STRUCTURE['media']['main_image']['thumbs'] as $thumbSizeAlias) {
                $this->assertEquals(url(self::TEST_IMAGE_PLACEHOLDER_PATH), $updatedPageResponseArr['data']['media']['main_image']['thumbs'][$thumbSizeAlias]);
            }
        }
    }

    /**
     * @covers       PageController::deletePage()
     * @method       DELETE
     * @group        admin-page
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testSoftDeletePageAs(
        array $input,
        array $expect
    ) {
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->deleteJson($this->apiPrefix . '/admin/pages/' . self::TEST_PAGE_ID)
        ;
        $jsonResponse->assertStatus($expect['statusCodeDeletePage']);
    }

    /**
     * @covers       PageController::forceDelete()
     * @method       DELETE
     * @group        admin-page
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testForceDeletePageAs(
        array $input,
        array $expect
    ) {
        // Force Delete user
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->deleteJson($this->apiPrefix . '/admin/pages/force/' . self::TEST_PAGE_ID)
        ;
        // Check status
        $jsonResponse->assertStatus($expect['statusCodeForceDeletePage']);
        if (Response::HTTP_NO_CONTENT === $expect['statusCodeForceDeletePage']) {
            // Check that user NOT exists in DB
            $forceDeletedUser = Page::withTrashed()->find(self::TEST_PAGE_ID);
            $this->assertNull($forceDeletedUser);
        }
    }

    /**
     * @covers       PageController::restorePage()
     * @method       GET
     * @group        admin-page
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testRestoreSoftDeletedPageAs(
        array $input,
        array $expect
    ) {
        // Soft Delete
        $this->actingAsAlias($input['actingAs'])
            ->deleteJson($this->apiPrefix . '/admin/pages/' . self::TEST_PAGE_ID)
        ;
        // Restore soft deleted
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/admin/pages/restore/' . self::TEST_PAGE_ID)
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeRestoreSoftDeletePage']);
        if (Response::HTTP_OK === $expect['statusCodeRestoreSoftDeletePage']) {
            $jsonResponse->assertJsonStructure(['data' => self::PAGE_RESPONSE_STRUCTURE]);
            $response = $jsonResponse->decodeResponseJson();
            $this->assertEquals(self::TEST_PAGE_ID, $response['data']['id']);
            $restoredUser = Page::withTrashed()->find(self::TEST_PAGE_ID);
            $this->assertNull($restoredUser->deleted_at);
        }
    }

    /**
     * @covers       PageController::updatePage()
     * @method       POST
     * @group        admin-page
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testUploadAndDeleteMainImageOfPageAs(
        array $input,
        array $expect
    )  {
        $image = $this->createUploadedFile();
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->postJson(
                $this->apiPrefix . '/admin/pages/' . self::TEST_PAGE_ID,
                [MediaLibraryConstants::REQUEST_FIELD_NAME_MAIN_IMAGE => $image]
            )
        ;
        // Check status upload file
        $jsonResponse->assertStatus($expect['statusCodeUpdatePage']);
        if (Response::HTTP_OK === $expect['statusCodeUpdatePage']) {
            // Check structure
            $jsonResponse->assertJsonStructure(['data' => self::PAGE_RESPONSE_STRUCTURE]);
            $response = $jsonResponse->decodeResponseJson();
            $mediaId = $response['data']['media']['main_image']['id'];
            $this->assertNotNull($mediaId);
            $mediaPaths = $this->buildOriginAndConversionPathsForTestUploadedImage(
                $mediaId,
                self::TEST_PAGE_ID,
                Page::class
            );
            // Check uploaded file URLs and its thumbs URLs
            $this->assertEquals($mediaPaths['origin']['url'], $response['data']['media']['main_image']['origin']);
            $this->assertEquals($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_SMALL]['url'], $response['data']['media']['main_image']['thumbs'][MediaLibraryConstants::THUMB_SMALL]);
            $this->assertEquals($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_MEDIUM]['url'], $response['data']['media']['main_image']['thumbs'][MediaLibraryConstants::THUMB_MEDIUM]);
            $this->assertEquals($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_LARGE]['url'], $response['data']['media']['main_image']['thumbs'][MediaLibraryConstants::THUMB_LARGE]);
            // Check uploaded file exists and its thumbs
            $this->assertFileExists($mediaPaths['origin']['storagePath']);
            $this->assertFileExists($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_SMALL]['storagePath']);
            $this->assertFileExists($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_MEDIUM]['storagePath']);
            $this->assertFileExists($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_LARGE]['storagePath']);

            // Check Delete uploaded file and its thumbs
            $jsonResponse = $this->actingAsAlias($input['actingAs'])
                ->deleteJson($this->apiPrefix . '/admin/media/' . $mediaId)
            ;
            $jsonResponse->assertStatus($expect['statusCodeDeleteMedia']);
            $this->assertFileNotExists($mediaPaths['origin']['storagePath']);
            $this->assertFileNotExists($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_SMALL]['storagePath']);
            $this->assertFileNotExists($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_MEDIUM]['storagePath']);
            $this->assertFileNotExists($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_LARGE]['storagePath']);
        }
    }

    /**
     * @covers       PageController::updatePage()
     * @method       POST
     * @group        admin-page
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testUploadAndDeleteGalleryImagesOfPageAs(
        array $input,
        array $expect
    )  {
        $image = $this->createUploadedFile();
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->postJson(
                $this->apiPrefix . '/admin/pages/' . self::TEST_PAGE_ID,
                [MediaLibraryConstants::REQUEST_FIELD_NAME_GALLERY => [$image]]
            )
        ;

        // Check status upload file
        $jsonResponse->assertStatus($expect['statusCodeUpdatePage']);
        if (Response::HTTP_OK === $expect['statusCodeUpdatePage']) {
            // Check structure
            $jsonResponse->assertJsonStructure(['data' => self::PAGE_RESPONSE_STRUCTURE_WITH_GALLERY]);
            $response = $jsonResponse->decodeResponseJson();
            $galleryResponse = $response['data']['media']['gallery'];

            foreach ($galleryResponse as $galleryItemResponse) {
                $mediaId = $galleryItemResponse['id'];
                $this->assertNotNull($mediaId);
                $mediaPaths = $this->buildOriginAndConversionPathsForTestUploadedImage(
                    $mediaId,
                    self::TEST_PAGE_ID,
                    Page::class
                );
                // Check uploaded file URLs and its thumbs URLs
                $this->assertEquals($mediaPaths['origin']['url'], $galleryItemResponse['origin']);
                $this->assertEquals($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_SMALL]['url'], $galleryItemResponse['thumbs'][MediaLibraryConstants::THUMB_SMALL]);
                $this->assertEquals($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_MEDIUM]['url'], $galleryItemResponse['thumbs'][MediaLibraryConstants::THUMB_MEDIUM]);
                $this->assertEquals($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_LARGE]['url'], $galleryItemResponse['thumbs'][MediaLibraryConstants::THUMB_LARGE]);
                // Check uploaded file exists and its thumbs
                $this->assertFileExists($mediaPaths['origin']['storagePath']);
                $this->assertFileExists($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_SMALL]['storagePath']);
                $this->assertFileExists($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_MEDIUM]['storagePath']);
                $this->assertFileExists($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_LARGE]['storagePath']);

                // Check Delete uploaded file and its thumbs
                $jsonResponse = $this->actingAsAlias($input['actingAs'])
                    ->deleteJson($this->apiPrefix . '/admin/media/' . $mediaId)
                ;
                $jsonResponse->assertStatus($expect['statusCodeDeleteMedia']);
                $this->assertFileNotExists($mediaPaths['origin']['storagePath']);
                $this->assertFileNotExists($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_SMALL]['storagePath']);
                $this->assertFileNotExists($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_MEDIUM]['storagePath']);
                $this->assertFileNotExists($mediaPaths['thumbs'][MediaLibraryConstants::THUMB_LARGE]['storagePath']);
            }
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
                array_merge(
                    ['actingAs' => ''],
                    ['testPageFieldToUpdate' => self::TEST_PAGE_FIELD_TO_UPDATE]
                ),
                [
                    'statusCodeGetListPages'             => Response::HTTP_UNAUTHORIZED,
                    'statusCodeGetSinglePage'            => Response::HTTP_UNAUTHORIZED,
                    'statusCodeCreatePage'               => Response::HTTP_UNAUTHORIZED,
                    'statusCodeUpdatePage'               => Response::HTTP_UNAUTHORIZED,
                    'statusCodeDeletePage'               => Response::HTTP_UNAUTHORIZED,
                    'statusCodeDeleteMedia'              => Response::HTTP_UNAUTHORIZED,
                    'statusCodeForceDeletePage'          => Response::HTTP_UNAUTHORIZED,
                    'statusCodeRestoreSoftDeletePage'    => Response::HTTP_UNAUTHORIZED,
                    'statusCodeGetListSoftDeletedPages'  => Response::HTTP_UNAUTHORIZED,
                    'statusCodeGetSingleSoftDeletedPage' => Response::HTTP_UNAUTHORIZED,
                ],
            ],
            // Admin
            [
                array_merge(
                    ['actingAs' => RoleConstants::ROLE_ADMIN],
                    ['testPageFieldToUpdate' => self::TEST_PAGE_FIELD_TO_UPDATE]
                ),
                [
                    'statusCodeGetListPages'             => Response::HTTP_OK,
                    'statusCodeGetSinglePage'            => Response::HTTP_OK,
                    'statusCodeCreatePage'               => Response::HTTP_CREATED,
                    'statusCodeUpdatePage'               => Response::HTTP_OK,
                    'statusCodeDeletePage'               => Response::HTTP_NO_CONTENT,
                    'statusCodeDeleteMedia'              => Response::HTTP_NO_CONTENT,
                    'statusCodeForceDeletePage'          => Response::HTTP_NO_CONTENT,
                    'statusCodeRestoreSoftDeletePage'    => Response::HTTP_OK,
                    'statusCodeGetListSoftDeletedPages'  => Response::HTTP_OK,
                    'statusCodeGetSingleSoftDeletedPage' => Response::HTTP_OK,
                ],
            ],
            // Manager
            [
                array_merge(
                    ['actingAs' => RoleConstants::ROLE_MANAGER],
                    ['testPageFieldToUpdate' => self::TEST_PAGE_FIELD_TO_UPDATE]
                ),
                [
                    'statusCodeGetListPages'             => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSinglePage'            => Response::HTTP_FORBIDDEN,
                    'statusCodeCreatePage'               => Response::HTTP_FORBIDDEN,
                    'statusCodeUpdatePage'               => Response::HTTP_FORBIDDEN,
                    'statusCodeDeletePage'               => Response::HTTP_FORBIDDEN,
                    'statusCodeDeleteMedia'              => Response::HTTP_FORBIDDEN,
                    'statusCodeForceDeletePage'          => Response::HTTP_FORBIDDEN,
                    'statusCodeRestoreSoftDeletePage'    => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListSoftDeletedPages'  => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleSoftDeletedPage' => Response::HTTP_FORBIDDEN,
                ],
            ],
            // Business
            [
                array_merge(
                    ['actingAs' => RoleConstants::ROLE_BUSINESS],
                    ['testPageFieldToUpdate' => self::TEST_PAGE_FIELD_TO_UPDATE]
                ),
                [
                    'statusCodeGetListPages'             => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSinglePage'            => Response::HTTP_FORBIDDEN,
                    'statusCodeCreatePage'               => Response::HTTP_FORBIDDEN,
                    'statusCodeUpdatePage'               => Response::HTTP_FORBIDDEN,
                    'statusCodeDeletePage'               => Response::HTTP_FORBIDDEN,
                    'statusCodeDeleteMedia'              => Response::HTTP_FORBIDDEN,
                    'statusCodeForceDeletePage'          => Response::HTTP_FORBIDDEN,
                    'statusCodeRestoreSoftDeletePage'    => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListSoftDeletedPages'  => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleSoftDeletedPage' => Response::HTTP_FORBIDDEN,
                ],
            ],
            // Worker
            [
                array_merge(
                    ['actingAs' => RoleConstants::ROLE_WORKER],
                    ['testPageFieldToUpdate' => self::TEST_PAGE_FIELD_TO_UPDATE]
                ),
                [
                    'statusCodeGetListPages'             => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSinglePage'            => Response::HTTP_FORBIDDEN,
                    'statusCodeCreatePage'               => Response::HTTP_FORBIDDEN,
                    'statusCodeUpdatePage'               => Response::HTTP_FORBIDDEN,
                    'statusCodeDeletePage'               => Response::HTTP_FORBIDDEN,
                    'statusCodeDeleteMedia'              => Response::HTTP_FORBIDDEN,
                    'statusCodeForceDeletePage'          => Response::HTTP_FORBIDDEN,
                    'statusCodeRestoreSoftDeletePage'    => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListSoftDeletedPages'  => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleSoftDeletedPage' => Response::HTTP_FORBIDDEN,
                ],
            ],
            // Tourist
            [
                array_merge(
                    ['actingAs' => RoleConstants::ROLE_TOURIST],
                    ['testPageFieldToUpdate' => self::TEST_PAGE_FIELD_TO_UPDATE]
                ),
                [
                    'statusCodeGetListPages'             => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSinglePage'            => Response::HTTP_FORBIDDEN,
                    'statusCodeCreatePage'               => Response::HTTP_FORBIDDEN,
                    'statusCodeUpdatePage'               => Response::HTTP_FORBIDDEN,
                    'statusCodeDeletePage'               => Response::HTTP_FORBIDDEN,
                    'statusCodeDeleteMedia'              => Response::HTTP_FORBIDDEN,
                    'statusCodeForceDeletePage'          => Response::HTTP_FORBIDDEN,
                    'statusCodeRestoreSoftDeletePage'    => Response::HTTP_FORBIDDEN,
                    'statusCodeGetListSoftDeletedPages'  => Response::HTTP_FORBIDDEN,
                    'statusCodeGetSingleSoftDeletedPage' => Response::HTTP_FORBIDDEN,
                ],
            ],
        ];
    }
}
