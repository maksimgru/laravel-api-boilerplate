<?php

namespace App\Models;

use App\Constants\MediaLibraryConstants;
use App\Transformers\PageTransformer;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property int            $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Page extends BaseModel
{
    use HasSlug, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'pages';

    /**
     * @var array
     */
    public static $itemWith = [];

    /**
     * @var array
     */
    public static $collectionWith = [];

    /**
     * @var array
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
    ];

    /**
     * @var array
     */
    public static $selectable = [
        'id',
        'title',
        'slug',
        'content',
    ];

    /**
     * @var array
     */
    public static $searchable = [
        'title',
        'slug',
        'content',
    ];

    /**
     * @var array
     */
    public static $orderable = [
        'id',
        'title',
        'slug',
        'created_at',
        'updated_at',
    ];

    /**
     * Model's custom transformer
     */
    public static $transformer = PageTransformer::class;

    /**
     * Model's boot function
     */
    public static function boot()
    {
        parent::boot();

        static::updating(function (self $page) {
            $page->slug = $page->slug ?: $page->getOriginal('slug');
        });
    }

    /**
     * Return the validation rules for this model
     *
     * @return array Rules
     */
    public function getValidationRules(): array
    {
        return [
            'title'                                                  => 'required|min:1|max:255',
            'slug'                                                   => [
                'nullable',
                Rule::unique('pages')->ignore($this->id),
            ],
            'content'                                                => 'nullable',
            MediaLibraryConstants::REQUEST_FIELD_NAME_MAIN_IMAGE     => 'filled|image',
            MediaLibraryConstants::REQUEST_FIELD_NAME_GALLERY        => 'filled|array',
            MediaLibraryConstants::REQUEST_FIELD_NAME_GALLERY . '.*' => 'image',
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
     * Get the options for generating the slug.
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->usingSeparator('-')
            ->usingLanguage('en')
            ->doNotGenerateSlugsOnUpdate()
        ;
    }

    /**
     * @param string $slug
     *
     * @return int|null
     */
    public static function getIdBySlug(string $slug): ?int
    {
        return self::where(['slug' => $slug])->first(['id'])->id;
    }
}
