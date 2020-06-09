<?php

namespace App\Models\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class PropertyTrait
 *
 * @package App\Models\Traits
 */
trait PropertyTrait
{
    /**
     * @var string
     */
    protected $propertyAttr = 'properties';

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        if (isset($this->properties[Str::snake($key)])) {
            $key = Str::snake($key);
            return $this->setProperty($key, $value);
        }
        return parent::setAttribute($key, $value);
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (isset($this->properties[$key])) {
            return $this->getProperty($key);
        }

        return parent::getAttribute($key);
    }

    /**
     * Get the fillable attributes for the model.
     *
     * @return array
     */
    public function getFillable()
    {
        return array_merge(parent::getFillable(), $this->getProperties());
    }

    /**
     * @return array
     */
    protected function getProperties(): array
    {
        $properties = array_keys($this->properties);
        foreach ($properties as $property) {
            if (($sProperty = Str::camel($property)) !== $property) {
                $properties[] = $sProperty;
            }
        }

        return $properties;
    }

    /**
     * @return array
     */
    public function getPropertyAttributeValues(): array
    {
        $propertiesNames = array_keys((array) $this->properties);
        foreach ($propertiesNames as $propertyName) {
            $properties[$propertyName] = $this->$propertyName;
        }

        return $properties;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    protected function getProperty($key)
    {
        $value = Arr::get(
            $this->getAttribute($this->propertyAttr),
            $this->properties[$key][0],
            $this->getDefaultValueFrom($this->properties[$key])
        );
        if (empty($value) && !empty($this->properties[$key]['empty'])) {
            return $this->properties[$key]['empty'];
        }
        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $value);
        }

        return $value;
    }

    /**
     * @param $key
     * @param $value
     */
    protected function setProperty($key, $value)
    {
        if ($this->hasCast($key)) {
            $value = $this->castAttribute($key, $value);
        }
        $properties = $this->getAttribute($this->propertyAttr) ?: [];
        Arr::set($properties, $this->properties[$key][0], $value);
        $this->setAttribute($this->propertyAttr, $properties);
    }

    /**
     * @param $setting
     *
     * @return mixed|null
     */
    protected function getDefaultValueFrom($setting)
    {
        if (!isset($setting[1]) && !isset($setting['empty'])) {
            return null;
        }
        if (empty($setting[1]) && !empty($setting['empty'])) {
            return $setting['empty'];
        }
        if (\is_callable($setting[1])) {
            return \call_user_func_array($setting[1], [$this]);
        }

        return $setting[1];
    }
}
