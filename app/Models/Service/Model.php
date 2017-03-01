<?php

namespace App\Models\Service;

class Model extends \Illuminate\Database\Eloquent\Model
{
    /**
     * Comma-separated attributes
     *
     */
    protected $commaSeparated = [];
    protected $multiLine = [];

    /**
     * Get a plain attribute (not a relationship).
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        $value = parent::getAttributeValue($key);

        if (in_array($key, $this->commaSeparated)) {
            return $this->getCommaSeparated($value);
        }
        if (in_array($key, $this->multiLine)) {
            return $this->getMultiLine($value);
        }

        return $value;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->commaSeparated)) {
            return $this->setCommaSeparated($key, $value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        foreach ($this->commaSeparated as $key) {
            if (! array_key_exists($key, $attributes)) {
                continue;
            }
            $attributes[$key] = $this->getCommaSeparated($attributes[$key]);
        }
        foreach ($this->multiLine as $key) {
            if (! array_key_exists($key, $attributes)) {
                continue;
            }
            $attributes[$key] = $this->getMultiLine($attributes[$key]);
        }


        return $attributes;
    }

    /**
     * Get comma-separated attribute
     */
    private function getCommaSeparated($value)
    {
        if (is_array($value)) {
            return $value;
        } else {
            return empty($value) ? [] : array_map('intval', explode(',', $value));
        }
    }

    /**
     * Get multiline attribute
     */
    private function getMultiLine($value)
    {
        return preg_replace("/ *[\r\n]+/", ", ", $value);
    }

    /**
     * Set comma-separated attribute
     */
    private function setCommaSeparated($key, $value)
    {
        if (is_array($value)) {
            $this->attributes[$key] = implode(',', $value);
        } else {
            $this->attributes[$key] = $value;
        }
        return $this;
    }

    /**
     * Get a clean attribute, avoiding accessors/getters
     *
     */
    public function getClean($key)
    {
        return $this->getAttributes()[$key];
    }
}
