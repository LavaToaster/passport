<?php

namespace LaravelDoctrine\Passport\Traits;

use Illuminate\Database\Eloquent\Concerns\HidesAttributes;
use Illuminate\Database\Eloquent\JsonEncodingException;

trait Serializable
{
    use HidesAttributes;

    protected function getVisibleAttributes()
    {
        return [];
    }

    protected function getHiddenAttributes()
    {
        return [];
    }

    public function bootSerializable()
    {
        $this->addHidden($this->getHiddenAttributes());
        $this->addVisible($this->getVisibleAttributes());
    }

    /**
     * Get an attribute array of all arrayable values.
     *
     * @param  array  $values
     * @return array
     */
    protected function getArrayableItems(array $values)
    {
        if (count($this->getVisible()) > 0) {
            $values = array_intersect_key($values, array_flip($this->getVisible()));
        }

        if (count($this->getHidden()) > 0) {
            $values = array_diff_key($values, array_flip($this->getHidden()));
        }

        return $values;
    }

    public function toArray()
    {
        $data = [];

        foreach ($this->attributes as $attribute) {
            $value = $this->{'get'.ucfirst($attribute)}();

            if (is_object($value) && method_exists($value, 'bootSerializable')) {
                $value->bootSerializable();
            }

            $data[$attribute] = $value;
        }

        return $this->getArrayableItems($data);
    }

    /**
     * Convert the entity instance to JSON.
     *
     * @param  int  $options
     * @return string
     *
     * @throws \Illuminate\Database\Eloquent\JsonEncodingException
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->toArray(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new JsonEncodingException('Error encoding model ['.get_class($this).'] :'.json_last_error_msg());
        }

        return $json;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
