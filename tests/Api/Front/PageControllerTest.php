<?php

namespace Tests\Api\Front;

use App\Http\Controllers\Front\PageController;
use App\Constants\RoleConstants;
use Dingo\Api\Http\Response;
use Tests\ApiTestCase;

class PageControllerTest extends ApiTestCase
{
    /**
     * @covers       PageController::index()
     * @method       GET
     * @group        front-page
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetListPagesOnFrontAs(
        array $input,
        array $expect
    ) {
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/pages')
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetListPagesOnFront']);
        if (Response::HTTP_OK === $expect['statusCodeGetListPagesOnFront']) {
            $jsonResponse->assertJsonStructure(['data' => [self::PAGE_RESPONSE_STRUCTURE]]);
        }
    }

    /**
     * @covers       PageController::getSinglePage()
     * @method       GET
     * @group        front-page
     * @dataProvider dataSet
     *
     * @param array $input
     * @param array $expect
     */
    public function testGetSinglePageOnFrontAs(
        array $input,
        array $expect
    ) {
        $jsonResponse = $this->actingAsAlias($input['actingAs'])
            ->getJson($this->apiPrefix . '/pages/' . self::TEST_PAGE_ID)
        ;
        // Check status and structure
        $jsonResponse->assertStatus($expect['statusCodeGetSinglePageOnFront']);
        if (Response::HTTP_OK === $expect['statusCodeGetSinglePageOnFront']) {
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
     * @return array
     */
    public function dataSet(): array
    {
        return [
            // Guest\NotAuth
            [
                ['actingAs' => ''],
                [
                    'statusCodeGetListPagesOnFront'  => Response::HTTP_OK,
                    'statusCodeGetSinglePageOnFront' => Response::HTTP_OK,
                ],
            ],
            // Admin
            [
                ['actingAs' => RoleConstants::ROLE_ADMIN],
                [
                    'statusCodeGetListPagesOnFront'  => Response::HTTP_OK,
                    'statusCodeGetSinglePageOnFront' => Response::HTTP_OK,
                ],
            ],
            // Manager
            [
                ['actingAs' => RoleConstants::ROLE_MANAGER],
                [
                    'statusCodeGetListPagesOnFront'  => Response::HTTP_OK,
                    'statusCodeGetSinglePageOnFront' => Response::HTTP_OK,
                ],
            ],
            // Business
            [
                ['actingAs' => RoleConstants::ROLE_BUSINESS],
                [
                    'statusCodeGetListPagesOnFront'  => Response::HTTP_OK,
                    'statusCodeGetSinglePageOnFront' => Response::HTTP_OK,
                ],
            ],
            // Worker
            [
                ['actingAs' => RoleConstants::ROLE_WORKER],
                [
                    'statusCodeGetListPagesOnFront'  => Response::HTTP_OK,
                    'statusCodeGetSinglePageOnFront' => Response::HTTP_OK,
                ],
            ],
            // Tourist
            [
                ['actingAs' => RoleConstants::ROLE_TOURIST],
                [
                    'statusCodeGetListPagesOnFront'  => Response::HTTP_OK,
                    'statusCodeGetSinglePageOnFront' => Response::HTTP_OK,
                ],
            ],
        ];
    }
}
