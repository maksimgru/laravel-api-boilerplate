<?php

namespace Tests\Api\Auth;

use App\Http\Controllers\Auth\VerificationController;
use App\Models\User;
use Dingo\Api\Http\Response;
use Tests\ApiTestCase;
use UserTableSeeder;

class VerificationControllerTest extends ApiTestCase
{
    public const TEST_USER_CREDENTIALS =
    [
        'email'    => UserTableSeeder::TOURIST_CREDENTIALS_NOT_VERIFIED_EMAIL['email'],
        'password' => UserTableSeeder::AUTH_PASSWORD,
    ];

    /**
     * @covers VerificationController::resend()
     * @method GET
     * @group  email-verification
     */
    public function testResendEmailVerification()
    {
        $jsonResponse = $this->actingAsUser(self::TEST_USER_CREDENTIALS)->getJson($this->apiPrefix . '/email/verify/resend');
        $jsonResponse->assertStatus(Response::HTTP_ACCEPTED);
        $response = $jsonResponse->decodeResponseJson();
        $this->assertArrayHasKey('message', $response);
        $this->assertStringContainsStringIgnoringCase('has been resubmitted', $response['message']);
        $this->assertNotNull($this->authUser()->email_verification_token);
    }

    /**
     * @covers VerificationController::verify()
     * @method GET
     * @group  email-verification
     */
    public function testSuccessMarkEmailAsVerified()
    {
        $this->actingAsUser(self::TEST_USER_CREDENTIALS)->getJson($this->apiPrefix . '/email/verify/resend');
        $jsonResponse = $this->actingAsUser(self::TEST_USER_CREDENTIALS)
            ->getJson($this->apiPrefix . '/email/verify/user/' . $this->authUser()->id . '/' . $this->authUser()->email_verification_token)
        ;

        $jsonResponse->assertStatus(Response::HTTP_ACCEPTED);
        $response = $jsonResponse->decodeResponseJson();
        $this->assertArrayHasKey('message', $response);
        $this->assertStringContainsStringIgnoringCase('email verified', $response['message']);

        $this->actingAsUser(self::TEST_USER_CREDENTIALS)->deleteJson($this->apiPrefix . '/auth/jwt/logout');
        $this->actingAsUser(self::TEST_USER_CREDENTIALS);
        $this->assertNull($this->authUser()->email_verification_token);
        $this->assertNotNull($this->authUser()->email_verified_at);
    }

    /**
     * @covers VerificationController::verify()
     * @method GET
     * @group  email-verification
     */
    public function testErrorMarkEmailAsVerified()
    {
        $this->actingAsUser(self::TEST_USER_CREDENTIALS)->getJson($this->apiPrefix . '/email/verify/resend');
        $jsonResponse = $this->actingAsUser(self::TEST_USER_CREDENTIALS)
            ->getJson($this->apiPrefix . '/email/verify/user/' . $this->authUser()->id . '/wrong_email_verification_token')
        ;

        $jsonResponse->assertStatus(Response::HTTP_BAD_REQUEST);
        $response = $jsonResponse->decodeResponseJson();
        $this->assertArrayHasKey('message', $response);
        $this->assertStringContainsStringIgnoringCase('email not verified', $response['message']);
        $this->assertStringContainsStringIgnoringCase('invalid verification token', $response['message']);

        $this->actingAsUser(self::TEST_USER_CREDENTIALS)->deleteJson($this->apiPrefix . '/auth/jwt/logout');
        $this->actingAsUser(self::TEST_USER_CREDENTIALS);
        $this->assertNotNull($this->authUser()->email_verification_token);
        $this->assertNull($this->authUser()->email_verified_at);
    }

    /**
     * @covers VerificationController::show()
     * @method GET
     * @group  email-verification
     */
    public function testNoticeEmailVerified()
    {
        $this->actingAsUser(self::TEST_USER_CREDENTIALS)->getJson($this->apiPrefix . '/email/verify/resend');
        $this->actingAsUser(self::TEST_USER_CREDENTIALS)
            ->getJson($this->apiPrefix . '/email/verify/user/' . $this->authUser()->id . '/' . $this->authUser()->email_verification_token)
        ;
        $jsonResponse = $this->actingAsUser(self::TEST_USER_CREDENTIALS)->getJson($this->apiPrefix . '/email/verify/notice');

        $jsonResponse->assertStatus(Response::HTTP_ACCEPTED);
        $response = $jsonResponse->decodeResponseJson();
        $this->assertArrayHasKey('message', $response);
        $this->assertStringContainsStringIgnoringCase('email verified', $response['message']);
    }

    /**
     * @covers VerificationController::show()
     * @method GET
     * @group  email-verification
     */
    public function testNoticeEmailNotVerified()
    {
        $this->actingAsUser(self::TEST_USER_CREDENTIALS)->getJson($this->apiPrefix . '/email/verify/resend');
        $jsonResponse = $this->actingAsUser(self::TEST_USER_CREDENTIALS)->getJson($this->apiPrefix . '/email/verify/notice');

        $jsonResponse->assertStatus(Response::HTTP_ACCEPTED);
        $response = $jsonResponse->decodeResponseJson();
        $this->assertArrayHasKey('message', $response);
        $this->assertStringContainsStringIgnoringCase('email not verified', $response['message']);
    }
}
