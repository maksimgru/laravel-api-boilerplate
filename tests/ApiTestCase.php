<?php

namespace Tests;

use App\Models\User;
use App\Constants\MediaLibraryConstants;
use App\Constants\FileSystemConstants;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JWTAuth;
use UserTableSeeder;

abstract class ApiTestCase extends TestCase
{
    /**
     * COMMON test constants
     */
    public const TEST_AVATAR_PLACEHOLDER_PATH = '/img/avatar-placeholder.jpg';
    public const TEST_IMAGE_PLACEHOLDER_PATH = '/img/image-placeholder.jpg';
    public const TEST_IMAGE_FILE_NAME = 'image.jpg';
    public const TEST_PDF_FILE_NAME = 'example.pdf';

    /**
     * HIDDEN model attributes
     */
    public const HIDDEN_MODEL_ATTRIBUTES = [
        User::class => [
            'password',
            'remember_token',
            'rememberToken',
            'email_verification_token',
            'emailVerificationToken',
            'email_verified_at',
            'emailVerifiedAt',
        ]
    ];

    /**
     * ROLE test constants
     */
    public const ROLE_RESPONSE_STRUCTURE =
    [
        'id',
        'name',
        'description',
    ];

    /**
     * USER test constants
     */
    public const TEST_ADMIN_ID = 1;
    public const TEST_MANAGER_ID = 3;
    public const TEST_BUSINESS_ID = 6;
    public const TEST_WORKER_ID = 9;
    public const TEST_TOURIST_ID = 12;
    public const TEST_TOURIST_ID_REFERRAL = 13;
    public const TEST_TOURIST_ID_WITHOUT_REFERRAL_BUSINESS = 13;
    public const TEST_NOT_ACTIVE_ADMIN_ID = 2;
    public const TEST_NOT_ACTIVE_MANAGER_ID = 5;
    public const TEST_NOT_ACTIVE_BUSINESS_ID = 8;
    public const TEST_NOT_ACTIVE_WORKER_ID = 11;
    public const TEST_NOT_ACTIVE_TOURIST_ID = 14;
    public const TEST_FOR_DELETE_USER_ID = 15;
    public const TEST_USERS_IDS = [
        'admin'    => self::TEST_ADMIN_ID,
        'manager'  => self::TEST_MANAGER_ID,
        'business' => self::TEST_BUSINESS_ID,
        'worker'   => self::TEST_WORKER_ID,
        'tourist'  => self::TEST_TOURIST_ID,
    ];
    public const TEST_NOT_ACTIVE_USERS_IDS = [
        'admin'    => self::TEST_NOT_ACTIVE_ADMIN_ID,
        'manager'  => self::TEST_NOT_ACTIVE_MANAGER_ID,
        'business' => self::TEST_NOT_ACTIVE_BUSINESS_ID,
        'worker'   => self::TEST_NOT_ACTIVE_WORKER_ID,
        'tourist'  => self::TEST_NOT_ACTIVE_TOURIST_ID,
    ];
    public const TEST_USER_FIELD_TO_UPDATE = [
        'first_name' => 'Updated Value',
        'last_name'  => 'Updated Value',
    ];
    public const USER_RESPONSE_STRUCTURE =
    [
        'id',
        'email',
        'username',
        'first_name',
        'last_name',
        'is_active',
        'manager_id',
        'business_id',
        'created_at',
        'updated_at',
        'birthday',
        'properties' => [
            'phone',
            'balance',
            'commission',
            'favorite_visit_places',
        ],
        'primary_role' => [
            'id',
            'name',
            'description',
        ],
        'media' => [
            'avatar_urls' => [
                'id',
                'origin',
                'thumbs' => [
                    MediaLibraryConstants::THUMB_SMALL,
                    MediaLibraryConstants::THUMB_MEDIUM,
                ],
            ],
        ],
    ];

    /**
     * PAGE test constants
     */
    public const TEST_PAGE_ID = 1;
    public const TEST_PAGE_FIELD_TO_UPDATE = [
        'title' => 'Title Edit!',
        'content' => 'Content Edit!',
    ];
    public const PAGE_RESPONSE_STRUCTURE =
    [
        'id',
        'title',
        'slug',
        'content',
        'media' => [
            'main_image' => [
                'id',
                'origin',
                'thumbs' => [
                    MediaLibraryConstants::THUMB_SMALL,
                    MediaLibraryConstants::THUMB_MEDIUM,
                    MediaLibraryConstants::THUMB_LARGE,
                ],
            ],
            'gallery',
        ],
    ];
    public const PAGE_RESPONSE_STRUCTURE_WITH_GALLERY =
    [
        'id',
        'title',
        'slug',
        'content',
        'media' => [
            'main_image' => [
                'id',
                'origin',
                'thumbs' => [
                    MediaLibraryConstants::THUMB_SMALL,
                    MediaLibraryConstants::THUMB_MEDIUM,
                    MediaLibraryConstants::THUMB_LARGE,
                ],
            ],
            'gallery' => [
                [
                    'id',
                    'origin',
                    'thumbs' => [
                        MediaLibraryConstants::THUMB_SMALL,
                        MediaLibraryConstants::THUMB_MEDIUM,
                        MediaLibraryConstants::THUMB_LARGE,
                    ],
                ],
            ],
        ],
    ];


    /**
     * @return mixed
     */
    public function authUser()
    {
        return $this->app->auth->user();
    }

    /**
     * @return int|null
     */
    public function authUserId(): ?int
    {
        $currentUser = $this->authUser();

        return $currentUser ? $currentUser->id : null;
    }

    /**
     * Set the currently logged in user for the application and Authorization headers for API request
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable
     * @param  string|null  $driver
     *
     * @return $this
     */
    public function actingAs(UserContract $user, $driver = null)
    {
        parent::actingAs($user, $driver);

        return $this->withHeader('Authorization', 'Bearer ' . JWTAuth::fromUser($user));
    }

    /**
     * @param string $alias
     *
     * @return $this
     */
    public function actingAsAlias(string $alias = '')
    {
        return $alias ? $this->{'actingAs' . ucfirst($alias)}() : $this;
    }

    /**
     * API Test case helper function for setting up
     * the request auth header as supplied user
     *
     * @param array $credentials
     *
     * @return $this
     */
    public function actingAsUser($credentials)
    {
        if (!$token = JWTAuth::attempt($credentials)) {
            return $this;
        }

        $user = ($apiKey = Arr::get($credentials, 'api_key'))
            ? User::whereApiKey($apiKey)->firstOrFail()
            : User::whereEmail(Arr::get($credentials, 'email'))->firstOrFail()
        ;

        return $this->actingAs($user);
    }

    /**
     * API Test case helper function for setting up the request as a logged in admin user
     *
     * @return $this
     */
    public function actingAsAdmin()
    {
        $user = User::where('email', UserTableSeeder::ADMIN_CREDENTIALS['email'])->firstOrFail();

        return $this->actingAs($user);
    }

    /**
     * API Test case helper function for setting up the request as a logged in manager user
     *
     * @return $this
     */
    public function actingAsManager()
    {
        $user = User::where('email', UserTableSeeder::MANAGER_CREDENTIALS['email'])->firstOrFail();

        return $this->actingAs($user);
    }

    /**
     * API Test case helper function for setting up the request as a logged in business user
     *
     * @return $this
     */
    public function actingAsBusiness()
    {
        $user = User::where('email', UserTableSeeder::BUSINESS_CREDENTIALS['email'])->firstOrFail();

        return $this->actingAs($user);
    }

    /**
     * API Test case helper function for setting up the request as a logged in worker user
     *
     * @return $this
     */
    public function actingAsWorker()
    {
        $user = User::where('email', UserTableSeeder::WORKER_CREDENTIALS['email'])->firstOrFail();

        return $this->actingAs($user);
    }

    /**
     * API Test case helper function for setting up the request as a logged in tourist user
     *
     * @return $this
     */
    public function actingAsTourist()
    {
        $user = User::where('email', UserTableSeeder::TOURIST_CREDENTIALS['email'])->firstOrFail();

        return $this->actingAs($user);
    }

    /**
     * Create test media uploaded file
     *
     * @param string $type
     *
     * @return UploadedFile
     */
    protected function createUploadedFile(?string $type = 'image'): UploadedFile
    {
        switch ($type) {
            case 'image':
            default:
                $name = self::TEST_IMAGE_FILE_NAME;
                break;
            case 'pdf':
                $name = self::TEST_PDF_FILE_NAME;
                break;
        }
        $stub = base_path('/tests/files/' . $name);
        $path = base_path('/tests/files/tmp/' . $name);
        copy($stub, $path);

        return new UploadedFile($path, $name, null, null, true);
    }

    /**
     * Build Path for test uploaded image
     * Conversions\Thumbs Paths
     *
     * @param int    $mediaId
     * @param int    $modelId
     * @param string $modelClass
     *
     * @return array
     */
    protected function buildOriginAndConversionPathsForTestUploadedImage(
        int $mediaId,
        int $modelId,
        string $modelClass
    ): array {
        $allThumbSizeAliases = MediaLibraryConstants::ALL_THUMB_SIZE_ALIASES;
        $modelShortName = strCase(Str::snake(class_basename(new $modelClass)));
        $storageRootPath = config('filesystems.disks.' . FileSystemConstants::DISK_NAME_UPLOADS_MEDIA_TESTING . '.root');
        $baseURL = config('filesystems.disks.' . FileSystemConstants::DISK_NAME_UPLOADS_MEDIA_TESTING . '.url');

        $uploadedImageFileName = sprintf(
            '%1$s-%2$d__%3$s',
            $modelShortName,
            $modelId,
            self::TEST_IMAGE_FILE_NAME
        );

        $paths = [
            'origin' => [
                'url' => sprintf(
                    '%1$s/%2$d/%3$s',
                    $baseURL,
                    $mediaId,
                    $uploadedImageFileName
                ),
                'storagePath' => sprintf(
                    '%1$s/%2$d/%3$s',
                    $storageRootPath,
                    $mediaId,
                    $uploadedImageFileName
                ),
            ],
        ];

        foreach ($allThumbSizeAliases as $thumbSizeAlias) {
            $thumbUploadedImageFileName = sprintf(
                '%1$s-%2$d__%3$s',
                $modelShortName,
                $modelId,
                str_replace('.', '-' . $thumbSizeAlias . '.', self::TEST_IMAGE_FILE_NAME)
            );
            $paths['thumbs'][$thumbSizeAlias] = [
                'url' => sprintf(
                    '%1$s/%2$d/conversions/%3$s',
                    $baseURL,
                    $mediaId,
                    $thumbUploadedImageFileName
                ),
                'storagePath' => sprintf(
                    '%1$s/%2$d/conversions/%3$s',
                    $storageRootPath,
                    $mediaId,
                    $thumbUploadedImageFileName
                ),
            ];
        }

        return $paths;
    }

    /**
     * Assert that response (array|haystack) has not contains hidden model attributes
     *
     * @param string $model
     * @param array  $response
     *
     * @return void
     */
    protected function assertResponseNotContainsHiddenAttributes(
        string $model,
        array $response
    ) {
        if (isset(self::HIDDEN_MODEL_ATTRIBUTES[$model])) {
            foreach (self::HIDDEN_MODEL_ATTRIBUTES[$model] as $hiddenAttributeName) {
                $this->assertArrayNotHasKey($hiddenAttributeName, $response);
            }
        }
    }
}
