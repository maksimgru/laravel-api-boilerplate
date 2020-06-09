<?php

namespace App\Models;

/**
 * @property int    $id
 * @property string $name
 * @property string $description
 */
class Role extends BaseModel
{
    /**
     * @var string
     */
    protected $table = 'roles';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * @var array
     */
    public static $selectable = [
        'id',
        'name',
        'description',
    ];

    /**
     * @var array
     */
    public static $searchable = [
        'name',
        'description',
    ];

    /**
     * @var array
     */
    public static $orderable = [
        'id',
        'name',
        'description',
    ];

    /**
     * @param string $roleName
     *
     * @return int|null
     */
    public static function getIdByRoleName(string $roleName): ?int
    {
        $role = self::where(['name' => $roleName])->first(['id']);

        return $role ? $role->id : null;
    }

    /**
     * @param string $roleName
     *
     * @return int|null
     */
    public static function getIdBy(string $roleName): ?int
    {
        return self::getIdByRoleName($roleName);
    }

    /**
     * @return array
     */
    public static function getRolesMap(): array
    {
        $rolesCollection = self::all(['id', 'name']);
        $rolesMap = [];
        foreach ($rolesCollection as $role) {
            /** @var self $role */
            $rolesMap[$role->name] = $role->id;
        }

        return $rolesMap;
    }
}
