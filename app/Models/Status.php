<?php

namespace App\Models;

/**
 * @property int    $id
 * @property string $name
 */
class Status extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'statuses';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * @param string $statusName
     *
     * @return int|null
     */
    public static function getIdByStatusName(string $statusName): ?int
    {
        return self::where(['name' => $statusName])->first(['id'])->id;
    }

    /**
     * @return array
     */
    public static function getAllIds(): array
    {
        return self::all(['id'])->pluck('id')->toArray();
    }
}
