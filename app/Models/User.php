<?php

namespace App\Models;

use App\Constants\RoleConstants;
use App\Constants\MediaLibraryConstants;
use App\Constants\StatusConstants;
use App\Constants\UserConstants;
use App\Models\Traits\PropertyTrait;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use App\Transformers\UserTransformer;
use Carbon\Carbon;
use Hash;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\belongsTo;
use Illuminate\Database\Eloquent\Relations\belongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Contracts\JWTSubject;


class User extends BaseModel implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract,
    JWTSubject,
    MustVerifyEmail
{
    use Authenticatable,
        Authorizable,
        CanResetPassword,
        Notifiable,
        PropertyTrait,
        SoftDeletes
    ;

    /**
     * @var string
     */
    protected $table = 'users';

    /**
     * @var array
     */
    public static $itemWith = [
        'primaryRole',
        'roles',
    ];

    /**
     * @var array
     */
    public static $collectionWith = [
        'primaryRole',
    ];

    /**
     * @var array
     */
    public static $availableWith = [
        'primaryRole',
        'roles',
        'devices',
    ];

    /*
     * @var array [attr => [key, default, cast => false, set => true, exists => true]]
     * exists   - true - check by array_key_exists
     *          - false - check by empty
     * public $properties = [
     *     'attr' => ['attr.key', 'default'],
     * ];
     */
    public $properties = [
        'phone'                 => ['phone', null],
        'company'               => ['company', null],
        'position'              => ['position', null],
        'about'                 => ['about', null],
        'address'               => ['address', ['country' => '', 'city' => '', 'address' => '','postal_code' => '']],
        'socials'               => ['socials', ['facebook' => '', 'twitter' => '', 'google_plus' => '']],
    ];

    /**
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'is_active',
        'password',
        'primary_role_id',
        'manager_id',
        'business_id',
        'phone',
        'google_id',
        'facebook_id',
        'company',
        'position',
        'about',
        'address',
        'socials',
        'birthday',
    ];

    /**
     * @var array
     */
    protected static $selectable = [
        'id',
        'first_name',
        'last_name',
        'username',
        'email',
        'is_active',
        'primary_role_id',
        'manager_id',
        'business_id',
        'birthday',
    ];

    /**
     * @var array
     */
    protected static $searchable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'is_active',
        'primary_role_id',
        'manager_id',
        'business_id',
        'birthday',
    ];

    /**
     * @var array
     */
    protected static $orderable = [
        'id',
        'first_name',
        'last_name',
        'username',
        'email',
        'is_active',
        'primary_role_id',
        'manager_id',
        'business_id',
        'created_at',
        'updated_at',
        'birthday',
    ];

    /**
     * These attributes can't be update in own profile
     * Only admin or parent user or internally in system can update it
     *
     * @var array
     */
    protected static $closed = [
        'is_active',
        'email_verification_token',
        'email_verified_at',
        'created_at',
        'updated_at',
        'deleted_at',
        'primary_role_id',
    ];

    /**
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_token',
        'email_verified_at',
        'media',
        'facebook_id',
        'google_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'properties'      => 'json',
        'is_active'       => 'boolean',
        'primary_role_id' => 'integer',
        'manager_id'      => 'integer',
        'business_id'     => 'integer',
        'phone'           => 'string',

        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
        'birthday'        => 'date',
    ];

    /**
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'birthday',
    ];

    /**
     * @var array
     */
    protected $appends = [
        'errors',
        'is_new',
    ];

    /**
     * Model's boot function
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function (self $user) {
            $user->setDefaultPrimaryRoleIfNull();
            $user->setDefaultBalanceIfNull();
        });

        static::saving(function (self $user) {
            $user->setDefaultPrimaryRoleIfNull();
            $user->hashedPasswordIfNeed();
        });

        static::deleting(function (self $user) {
            $user->deactivateIfSoftDeleting();
        });

        static::retrieved(function (self $user) {
            $user->setDefaultPrimaryRoleIfNull();
        });
    }

    /**
     * Model's custom transformer
     */
    public static $transformer = UserTransformer::class;

    /**
     * Return the validation rules for this model
     *
     * @return array Rules
     */
    public function getValidationRules(): array
    {
        return [
            'email'                                          => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->id),
            ],
            'username'                                       => [
                'required',
                'min:3',
                'max:255',
                Rule::unique('users')->ignore($this->id),
            ],
            'password'                                       => 'required|min:6',
            'primary_role_id'                                => 'filled|integer|exists:roles,id',
            'is_active'                                      => 'filled|boolean',
            'first_name'                                     => 'nullable|min:3|max:255',
            'last_name'                                      => 'nullable|min:3|max:255',
            'birthday'                                       => 'nullable|date|dateFormat:' . config('formats.date', 'Y-m-d'),
            MediaLibraryConstants::REQUEST_FIELD_NAME_AVATAR => 'filled|image',
            'manager_id'                                     => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('primary_role_id', Role::getIdByRoleName(RoleConstants::ROLE_MANAGER));
                }),
            ],
            'business_id'                                    => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('primary_role_id', Role::getIdByRoleName(RoleConstants::ROLE_BUSINESS));
                }),
            ],
            'address'                                        => 'filled|array',
            'address.country'                                => 'nullable|string',
            'address.city'                                   => 'nullable|string',
            'address.address'                                => 'nullable|string',
            'address.postal_code'                            => 'nullable',
            'phone'                                          => 'nullable',
            'socials'                                        => 'filled|array',
            'socials.*'                                      => 'nullable|url',
        ];
    }

    /**
     * @return array
     */
    public function getImmutableAttributes(): array
    {
        $excludeAttributes = ['deleted_at'];
        $immutableAttributes = $this->immutableAttributes;

        if (auth()->user() && auth()->user()->isAdmin()) {
            $immutableAttributes = array_filter(
                $immutableAttributes,
                function ($attrName) use ($excludeAttributes) {
                    return !\in_array($attrName, $excludeAttributes, true);
                }
            );
        }

        return $immutableAttributes;
    }

    /**
     * @param int|null $userId
     *
     * @return array
     */
    public static function getClosedAttributes(?int $userId = null): array
    {
        /** @var self $authUser */
        $authUser = auth()->user();
        $closed = static::$closed;
        $exclude = [];

        if ($authUser) {
            $userId = $userId ?? $authUser->getKey();
            if ($authUser->isAdmin()) {
                $exclude[] = 'balance';
                $exclude[] = 'commission';
                if ($userId !== $authUser->getKey()) {
                    $exclude[] = 'is_active';
                    $exclude[] = 'primary_role_id';
                }
            } elseif (($authUser->isPrimaryRoleManager() && \in_array($userId, $authUser->getOwnBusinessesIds(), true))
                ||
                ($authUser->isPrimaryRoleBusiness() && \in_array($userId, $authUser->getOwnWorkersIds(), true))
            ) {
                $exclude[] = 'balance';
                $exclude[] = 'commission';
                $exclude[] = 'is_active';
            }
        }

        foreach ($closed as $indx => $closedAttr) {
            if (\in_array($closedAttr, $exclude, true)) {
                unset($closed[$indx]);
            }
        }

        return array_values($closed);
    }

    /**
     * User's primary role
     *
     * @return BelongsTo
     */
    public function primaryRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'primary_role_id');
    }

    /**
     * User's secondary roles
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    /**
     * Get all user's roles
     *
     * @return array
     */
    public function getRoles(): array
    {
        $allRoles = array_merge(
            [
                $this->primaryRole->name,
            ],
            $this->roles->pluck('name')->toArray()
        );

        return $allRoles;
    }

    /**
     * Set default user primary role if existed primary role is NULL
     *
     * @return self
     */
    public function setDefaultPrimaryRoleIfNull(): self
    {
        $this->primary_role_id = $this->primary_role_id ?: Role::getIdByRoleName(RoleConstants::ROLE_DEFAULT);

        return $this;
    }

    /**
     * Is this user an admin?
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->primary_role_id === Role::getIdByRoleName(RoleConstants::ROLE_ADMIN);
    }

    /**
     * Is this user an Manager?
     *
     * @return bool
     */
    public function isPrimaryRoleManager(): bool
    {
        return $this->primary_role_id === Role::getIdByRoleName(RoleConstants::ROLE_MANAGER);
    }

    /**
     * Is this user an Business?
     *
     * @return bool
     */
    public function isPrimaryRoleBusiness(): bool
    {
        return $this->primary_role_id === Role::getIdByRoleName(RoleConstants::ROLE_BUSINESS);
    }

    /**
     * Is this user an Worker?
     *
     * @return bool
     */
    public function isPrimaryRoleWorker(): bool
    {
        return $this->primary_role_id === Role::getIdByRoleName(RoleConstants::ROLE_WORKER);
    }

    /**
     * Is this user an Tourist?
     *
     * @return bool
     */
    public function isPrimaryRoleTourist(): bool
    {
        return $this->primary_role_id === Role::getIdByRoleName(RoleConstants::ROLE_TOURIST);
    }

    /**
     * Has user this role?
     *
     * @param string $roleName
     *
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        return \in_array($roleName, $this->getRoles(), true);
    }

    /**
     * @param int $userId
     *
     * @return null|string
     */
    public static function getPrimaryRoleNameByUserId(int $userId): ?string
    {
        $primaryRole = self::find($userId, ['id', 'primary_role_id'])->primaryRole;

        return $primaryRole ? $primaryRole->name : null;
    }

    /**
     * @param int $userId
     *
     * @return bool
     */
    public function canEditRoleFor(int $userId): bool
    {
        return $this->isAdmin() && $this->id !== $userId;
    }

    /**
     * For Business User, has as parent Manager
     *
     * @return belongsTo
     */
    public function parentManager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    /**
     * For Worker User, has as parent Business
     *
     * @return belongsTo
     */
    public function parentBusiness(): BelongsTo
    {
        return $this->belongsTo(self::class, 'business_id');
    }

    /**
     * For Manager User, get all own business users
     *
     * @return HasMany
     */
    public function ownBusinesses(): HasMany
    {
        return $this->hasMany(self::class, 'manager_id')
            ->where('primary_role_id', Role::getIdByRoleName(RoleConstants::ROLE_BUSINESS))
        ;
    }

    /**
     * Assign (if null) Business as referral only for Tourist user
     *
     * @param User|null $business
     *
     * @return User
     */
    public function assignReferralBusiness(?User $business): self
    {
        if ($business
            && !$this->business_id
            && $this->isPrimaryRoleTourist()
            && $business->isPrimaryRoleBusiness()
        ) {
            $this->business_id = $business->getKey();
            $this->save();
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getOwnBusinessesIds(): array
    {
        return $this->ownBusinesses()->get(['id'])->pluck('id')->toArray();
    }

    /**
     * For Business User, get all own worker users
     *
     * @return HasMany
     */
    public function ownWorkers(): HasMany
    {
        return $this->hasMany(self::class, 'business_id')
            ->where('primary_role_id', Role::getIdByRoleName(RoleConstants::ROLE_WORKER))
        ;
    }

    /**
     * @return array
     */
    public function getOwnWorkersIds(): array
    {
        return $this->ownWorkers()->get(['id'])->pluck('id')->toArray();
    }

    /**
     * @return array
     */
    public function getBusinessIDsByCurrentUser(): array
    {
        $businessIds = [];
        if ($this->isPrimaryRoleManager()) {
            $businessIds = $this->getOwnBusinessesIds();
        } elseif ($this->isPrimaryRoleBusiness()) {
            $businessIds = [$this->getKey()];
        } elseif ($this->isPrimaryRoleWorker() || $this->isPrimaryRoleTourist()) {
            $businessIds = [$this->business_id];
        }

        return array_filter($businessIds, function($item) {return $item;});
    }

    /**
     * @return array
     */
    public function getWorkersIDsByCurrentUser(): array
    {
        $workerIds = [];
        if ($this->isPrimaryRoleManager()) {
            $workerIds = self::whereIn('business_id', $this->getOwnBusinessesIds())
                ->where('primary_role_id', Role::getIdByRoleName(RoleConstants::ROLE_WORKER))
                ->get(['id'])
                ->pluck('id')
                ->toArray()
            ;
        } elseif ($this->isPrimaryRoleBusiness()) {
            $workerIds = $this->getOwnWorkersIds();
        } elseif ($this->isPrimaryRoleWorker()) {
            $workerIds = [$this->getKey()];
        }

        return array_filter($workerIds, function($item) {return $item;});
    }

    /**
     * @return HasMany
     */
    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class, 'user_id');
    }
    /**
     * Hash user password, if not already hashed
     *
     * @return self
     */
    public function hashedPasswordIfNeed(): self
    {
        if (Hash::needsRehash($this->password)) {
            $this->password = Hash::make($this->password);
        }

        return $this;
    }

    /**
     * For Authentication
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * For Authentication
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'user' => [
                'id' => $this->getKey(),
                'email' => $this->email,
                'primaryRole' => ['id' => $this->primaryRole->id, 'name' => $this->primaryRole->name],
                'businessId' => $this->business_id,
                'isNew' => $this->isNew,
                'rememberToken' => $this->getRememberToken(),
            ],
        ];
    }

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     *
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($this, $token));
    }

    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return (bool) $this->email_verified_at && !$this->email_verification_token;
    }

    /**
     * Mark the given user's email as verified.
     *
     * @return bool
     */
    public function markEmailAsVerified()
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
            'email_verification_token' => null,
        ])->save();
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        if (!$this->hasVerifiedEmail()) {
            $this->notify(new VerifyEmailNotification($this->generateEmailVerificationToken()));
        }
    }

    /**
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getEmailVerificationToken(): ?string
    {
        return $this->email_verification_token;
    }

    /**
     * @return string
     */
    protected function generateEmailVerificationToken(): string
    {
        $token = generateRandomString(20);
        $this->forceFill([
            'email_verification_token' => $token,
        ])->save();

        return $token;
    }

    /**
     * Determine if the user is activated.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * @param int $userId
     *
     * @return bool
     */
    public function canActivatedOf(int $userId): bool
    {
        return $this->id !== $userId
            && ($this->isAdmin()
                ||
                ($this->isPrimaryRoleManager() && \in_array($userId, $this->getOwnBusinessesIds(), true))
                ||
                ($this->isPrimaryRoleBusiness() && \in_array($userId, $this->getOwnWorkersIds(), true))
            )
        ;
    }

    /**
     * Mark the given user as activated.
     *
     * @return bool
     */
    public function markAsActive(): bool
    {
        return $this->forceFill([
            'is_active' => true,
        ])->save();
    }


    /**
     * Set is_active FALSE if Soft deleting
     *
     * @return self
     */
    public function deactivateIfSoftDeleting(): self
    {
        if (!$this->isForceDeleting()) {
            $this->is_active = false;
            $this->save();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsNewAttribute(): bool
    {
        return $this->isNew;
    }
}
