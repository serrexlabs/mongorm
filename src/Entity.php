<?php

namespace Serrexlabs\Mongorm;


use JsonSerializable;
use MongoDB\BSON\ObjectID;
use ReflectionClass;

class Entity implements JsonSerializable
{
    /**
     * @param int $options
     * @return string
     * @throws \ReflectionException
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * @FIXME Remove reflection type coding
     *
     * @param bool $convertIdToString
     * @return array
     * @throws \ReflectionException
     */
    public function toArray($convertIdToString = false)
    {
        $public = [];
        $reflection = new ReflectionClass($this);
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            if ($property->getValue($this) != null ) {
                if ($convertIdToString && $property->getValue($this) instanceof ObjectID) {
                    $public[$property->getName()] = $property->getValue($this)->__toString();
                    continue;
                }
                $public[$property->getName()] = $property->getValue($this);
            }
        }
        return $public;
    }


    /**
     * @return array|mixed
     * @throws \ReflectionException
     */
    public function jsonSerialize()
    {
        return $this->toArray(true);
    }
}