<?php

namespace App\Models;

use App\Constants\MediaLibraryConstants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\Conversion\Conversion;
use Spatie\MediaLibrary\Conversion\ConversionCollection;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\MediaLibrary\Models\Media;
use Spatie\Image\Exceptions\InvalidManipulation;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Transformers\BaseTransformer;
use Specialtactics\L5Api\Transformers\RestfulTransformer;
use Specialtactics\L5Api\APIBoilerplate;
use Specialtactics\L5Api\Models\Builder;

class BaseModel extends Model implements HasMedia
{
    use HasMediaTrait;

    /**
     * Every model should have a primary ID key, which will be returned to API consumers.
     *
     * @var string UUID key
     */
    public $primaryKey = 'id';

    /**
     * The number of models to return for pagination.
     * Set to null to get value from config
     * You can override value in specific Model class and ignore default config value
     *
     * @var int
     */
    protected $perPage;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * These attributes (in addition to primary & uuid keys) are not allowed to be updated explicitly through
     *  API routes of update and put. They can still be updated internally by Laravel, and your own code.
     *
     * @var array Attributes to disallow updating through an API update or put
     */
    public $immutableAttributes = ['created_at', 'deleted_at'];

    /**
     * Acts like $with (eager loads relations), however only for immediate controller requests for that object
     * This is useful if you want to use "with" for immediate resource routes, however don't want these relations
     *  always loaded in various service functions, for performance reasons
     *
     * @deprecated Use  getItemWith() and getCollectionWith()
     * @var array Relations to load implicitly by Restful controllers
     */
    public static $localWith = [];

    /**
     * What relations should one model of this entity be returned with, from a relevant controller
     *
     * @var null|array
     */
    public static $itemWith = [];

    /**
     * What relations should a collection of models of this entity be returned with, from a relevant controller
     * If left null, then $itemWith will be used
     *
     * @var null|array
     */
    public static $collectionWith = [];

    /**
     * @var array Relations that are available for current Model."
     */
    public static $availableWith = [];

    /**
     * You can define a custom transformer for a model, if you wish to override the functionality of the Base transformer
     *
     * @var null|RestfulTransformer The transformer to use for this model, if overriding the default
     */
    public static $transformer;

    /**
     * The attributes that must be returned to the paginated response object.
     *
     * @var array
     */
    protected static $selectable = [];

    /**
     * The attributes in which we can search.
     *
     * @var array
     */
    protected static $searchable = [];

    /**
     * The attributes that can be used to Order By in paginated response object.
     *
     * @var array
     */
    protected static $orderable = [];

    /**
     * @var array
     */
    protected $validationErrors = [];

    /**
     * @var bool
     */
    protected $isNew = false;

    /**
     * @var array
     */
    protected $hidden = [
        'media',
    ];

    /**
     * @var array
     */
    protected $appends = ['errors'];

    /**
     * Return the validation rules for this model
     *
     * @return array Validation rules to be used for the model when creating it
     */
    public function getValidationRules()
    {
        return [];
    }

    /**
     * Return the validation rules for this model's update operations
     * In most cases, they will be the same as for the create operations
     *
     * @return array Validation roles to use for updating model
     */
    public function getValidationRulesUpdating()
    {
        return $this->getValidationRules();
    }

    /**
     * Return any custom validation rule messages to be used
     *
     * @return array
     */
    public function getValidationMessages()
    {
        return [];
    }

    /**
     * Boot the model
     *
     * Add various functionality in the model lifecycle hooks
     * @throws BadRequestHttpException
     */
    public static function boot()
    {
        parent::boot();

        // Add functionality for saving a model
        static::saving(function (self $model) {
            // Disallow updating immutable attributes
            self::disableUpdateImmutableAttributes($model);
        });

        // Add functionality for updating a model
        static::updating(function (self $model) {
            // Disallow updating ID keys
            self::disableUpdatePrimaryKey($model);
            // Disallow updating immutable attributes
            self::disableUpdateImmutableAttributes($model);
        });
    }

    /**
     * Retrieve full model
     *
     * @param $id
     *
     * @return Model|null|object|static
     */
    public function getFullModel($id)
    {
        return self::with(self::getItemWith())
            ->where($this->getKeyName(), '=', $id)
            ->first()
        ;
    }

    /**
     * Return this model's transformer, or a generic one if a specific one is not defined for the model
     *
     * @return BaseTransformer
     */
    public static function getTransformer()
    {
        return is_null(static::$transformer) ? new BaseTransformer : new static::$transformer;
    }

    /**
     * When Laravel creates a new model, it will add any new attributes (such as UUID) at the end. When a create
     * operation such as a POST returns the new resource, the UUID will thus be at the end, which doesn't look nice.
     * For purely aesthetic reasons, we have this function to conduct a simple reorder operation to move the UUID
     * attribute to the head of the attributes array
     *
     * This will be used at the end of create-related controller functions
     *
     * @return void
     */
    public function orderAttributesUuidFirst()
    {
        if ($this->getKeyName()) {
            $UuidValue = $this->getKey();
            unset($this->attributes[$this->getKeyName()]);
            $this->attributes = [$this->getKeyName() => $UuidValue] + $this->attributes;
        }
    }

    /**
     * If using deprecated $localWith then use that
     * Otherwise, use $itemWith
     *
     * @return array
     */
    public static function getItemWith()
    {
        return static::$itemWith ?: static::$localWith;
    }

    /**
     * If using deprecated $localWith then use that
     * Otherwise, if collectionWith hasn't been set, use $itemWith by default
     * Otherwise, use collectionWith
     *
     * @return array
     */
    public static function getCollectionWith()
    {
        return static::$collectionWith ?: static::$itemWith;
    }

    /**
     * @return array
     */
    public static function getAvailableWith(): ?array
    {
        $availableWith = (array) static::$availableWith;
        if (!$availableWith) {
            $availableWith = array_unique(array_merge(
                (array) static::$itemWith,
                (array) static::$collectionWith
            ));
        }

        return $availableWith;
    }

    /**
     * @return array
     */
    public static function getSelectable(): array
    {
        return static::$selectable;
    }

    /**
     * @return array
     */
    public static function getSearchable(): array
    {
        return static::$searchable;
    }

    /**
     * @return array
     */
    public static function getOrderable(): array
    {
        return static::$orderable;
    }

    /**
     * @return array
     */
    public function getImmutableAttributes(): array
    {
        return $this->immutableAttributes;
    }

    /************************************************************
     * Extending Laravel Functions Below
     ***********************************************************/

    /**
     * We're extending the existing Laravel Builder
     *
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * @param null|string $collectionName
     *
     * @return string
     */
    public function getImagePlaceholderUrl(?string $collectionName = null): string
    {
        switch ($collectionName) {
            case MediaLibraryConstants::COLLECTION_NAME_AVATAR:
                $placeholderPath = config('medialibrary.placeholder_avatar_path');
                break;
            default:
                $placeholderPath = config('medialibrary.placeholder_image_path');
                break;
        }

        return url($placeholderPath);
    }

    /**
     * Get all the names(aliases) of the registered media conversions for given collection.
     *
     * @param null| Media $media
     * @param null|string $collectionName
     *
     * @return array
     */
    public function getMediaConversionAliases(
        ?Media $media,
        ?string $collectionName = null
    ): array {
        if (!$media) {
            return MediaLibraryConstants::ALL_THUMB_SIZE_ALIASES;
        }

        $conversions = ConversionCollection::createForMedia($media);
        if ($collectionName) {
            $conversions = $conversions->getConversions($collectionName);
        }

        return $conversions->map(function (Conversion $conversion) {
            return $conversion->getName();
        })->toArray();
    }

    /**
     * @param string $collectionName
     *
     * @return array
     */
    public function getFirstMediaThumbsUrls(string $collectionName): array
    {
        /** @var Media $media */
        $media = $this->getFirstMedia($collectionName);
        $thumbUrls = [
            'id' => $media ? $media->getKey() : null,
            'origin' => $media ? $media->getFullUrl() : $this->getImagePlaceholderUrl($collectionName)
        ];
        $conversionAliases = $this->getMediaConversionAliases($media, $collectionName);

        foreach ($conversionAliases as $conversionAlias) {
            $thumbUrls['thumbs'][$conversionAlias] = $media
                ? $media->getFullUrl($conversionAlias)
                : $this->getImagePlaceholderUrl($collectionName)
            ;
        }

        return $thumbUrls;
    }

    /**
     * @param string $collectionName
     *
     * @return array
     */
    public function getMediaThumbsUrls(string $collectionName): array
    {
        $gallery = $this->getMedia($collectionName);
        $galleryUrls = [];

        foreach ($gallery as $galleryItem) { /** @var Media $galleryItem */
            $thumbUrls = [
                'id' => $galleryItem ? $galleryItem->getKey() : null,
                'origin' => $galleryItem->getFullUrl()
            ];
            $conversionAliases = $this->getMediaConversionAliases($galleryItem, $collectionName);
            foreach ($conversionAliases as $conversionAlias) {
                $thumbUrls['thumbs'][$conversionAlias] = $galleryItem->getFullUrl($conversionAlias);
            }
            $galleryUrls[] = $thumbUrls;
        }

        return $galleryUrls;
    }

    /**
     * @param bool|null $case TRUE is Uppercase, FALSE is Lowercase, NULL is MixedCase (origin)
     *
     * @return string
     */
    public function generateMediaNamePrefix(?bool $case = false): string
    {
        return strCase(Str::snake(class_basename($this)) . '-' . $this->getKey(), $case);
    }

    /**
     * @param string $requestFieldName
     * @param string $collectionName
     *
     * @return null|Media
     */
    public function handleUploadedMedia(
        string $requestFieldName,
        string $collectionName
    ): ?Media {
        return $this
            ->addMediaFromRequest($requestFieldName)
            ->sanitizingFileName(function($fileName) {
                return strCase($this->generateMediaNamePrefix(null) . '__' . sanitizeString($fileName));
            })
            ->toMediaCollection($collectionName)
        ;
    }

    /**
     * @param array  $requestFieldsNames
     * @param string $collectionName
     *
     * @return null|Collection
     */
    public function handleUploadedMultipleMedia(
        array $requestFieldsNames,
        string $collectionName
    ): ?Collection {
        return $this
            ->addMultipleMediaFromRequest($requestFieldsNames)
            ->each(function ($fileAdder) use ($collectionName) {
                $fileAdder
                    ->sanitizingFileName(function($fileName) {
                        return strCase($this->generateMediaNamePrefix(null) . '__' . sanitizeString($fileName));
                    })
                    ->toMediaCollection($collectionName)
                ;
            })
        ;
    }

    /**
     * @return void
     * @throws InvalidManipulation
     */
    public function registerMediaCollections()
    {
        /**
         * Register Avatar collection
         */
        $this
            ->addMediaCollection(MediaLibraryConstants::COLLECTION_NAME_AVATAR)
            ->useFallbackUrl(config('medialibrary.placeholder_avatar_path'))
            ->singleFile()
        ;

        /**
         * Register Main Image collection
         */
        $this
            ->addMediaCollection(MediaLibraryConstants::COLLECTION_NAME_MAIN_IMAGE)
            ->useFallbackUrl(config('medialibrary.placeholder_image_path'))
            ->singleFile()
        ;

        /**
         * Register Gallery collection
         */
        $this
            ->addMediaCollection(MediaLibraryConstants::COLLECTION_NAME_GALLERY)
            ->useFallbackUrl(config('medialibrary.placeholder_image_path'))
        ;
    }

    /**
     * @param Media|null $media
     *
     * @return void
     * @throws InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null)
    {
        $thumbSmallMediaConversion = $this
            ->addMediaConversion(MediaLibraryConstants::THUMB_SMALL)
            ->width(config('medialibrary.thumb_small.width'))
            ->height(config('medialibrary.thumb_small.height'))
            ->performOnCollections(
                MediaLibraryConstants::COLLECTION_NAME_AVATAR,
                MediaLibraryConstants::COLLECTION_NAME_MAIN_IMAGE,
                MediaLibraryConstants::COLLECTION_NAME_GALLERY
            )
        ;

        $thumbMediumMediaConversion = $this
            ->addMediaConversion(MediaLibraryConstants::THUMB_MEDIUM)
            ->width(config('medialibrary.thumb_medium.width'))
            ->height(config('medialibrary.thumb_medium.height'))
            ->performOnCollections(
                MediaLibraryConstants::COLLECTION_NAME_AVATAR,
                MediaLibraryConstants::COLLECTION_NAME_MAIN_IMAGE,
                MediaLibraryConstants::COLLECTION_NAME_GALLERY
            )
        ;

        $thumbLargeMediaConversion = $this
            ->addMediaConversion(MediaLibraryConstants::THUMB_LARGE)
            ->width(config('medialibrary.thumb_large.width'))
            ->height(config('medialibrary.thumb_large.height'))
            ->performOnCollections(
                MediaLibraryConstants::COLLECTION_NAME_MAIN_IMAGE,
                MediaLibraryConstants::COLLECTION_NAME_GALLERY
            )
        ;

        if (!env('MEDIA_CONVERSIONS_QUEUED', false)) {
            $thumbSmallMediaConversion->nonQueued();
            $thumbMediumMediaConversion->nonQueued();
            $thumbLargeMediaConversion->nonQueued();
        }
    }

    /**
     * Disallow updating immutable attributes
     *
     * @param BaseModel $model
     *
     * @return void
     * @throws BadRequestHttpException
     */
    protected static function disableUpdateImmutableAttributes(BaseModel $model): void
    {
        if (!empty($model->getImmutableAttributes())) {
            // For each immutable attribute, check if they have changed
            foreach ($model->getImmutableAttributes() as $attributeName) {
                if ($model->getOriginal($attributeName) != $model->getAttribute($attributeName)) {
                    throw new BadRequestHttpException(
                        sprintf(
                            'Updating the "%1$s" attribute is not allowed.',
                            APIBoilerplate::formatCaseAccordingToResponseFormat($attributeName)
                        )
                    );
                }
            }
        }
    }

    /**
     * Disallow updating primaryKey ID
     *
     * @param BaseModel $model
     *
     * @return void
     * @throws BadRequestHttpException
     */
    protected static function disableUpdatePrimaryKey(BaseModel $model): void
    {
        if ($model->getAttribute($model->getKeyName()) != $model->getOriginal($model->getKeyName())) {
            throw new BadRequestHttpException('Updating the UUID of a resource is not allowed.');
        }
    }

    /**
     * @return array
     */
    public function getErrorsAttribute(): array
    {
        return $this->getValidationErrors();
    }

    /**
     * Getter $validationErrors
     *
     * @return array
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    /**
     * Setter $validationErrors
     *
     * @param array $validationErrors
     *
     * @return BaseModel
     */
    public function setValidationErrors(array $validationErrors): self
    {
        $this->validationErrors = $validationErrors;

        return $this;
    }

    /**
     * @param bool $isNew
     *
     * @return BaseModel
     */
    public function setIsNew(bool $isNew): self
    {
        $this->isNew = $isNew;

        return $this;
    }
}
