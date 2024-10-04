<?php

namespace App\Utils;

use ReflectionClass;
use ReflectionProperty;

abstract class DataTransferObject
{
    public function __construct(array $parameters = [])
    {
        $class = new ReflectionClass(static::class);

        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            $property = $reflectionProperty->getName();
            if (isset($parameters[$property])) {
                $this->{$property} = $parameters[$property];
            } elseif (property_exists($this, $property)) {
                $defaultProperties = $reflectionProperty->getDeclaringClass()->getDefaultProperties();
                if (isset($defaultProperties[$property])) {
                    $this->{$property} = $defaultProperties[$property];
                }
            }
        }
    }

    public function toArray(): array
    {
        $array = get_object_vars($this);

        foreach ($array as $key => $value) {
            if (is_object($value) && method_exists($value, 'toArray')) {
                $array[$key] = $value->toArray();
            }
        }

        return $array;
    }
}
