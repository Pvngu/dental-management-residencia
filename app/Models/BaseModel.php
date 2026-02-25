<?php


namespace App\Models;

use Examyou\RestAPI\ApiModel;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;

class BaseModel extends ApiModel
{

    function __call($method, $arguments)
    {
        if (isset($this->hashableGetterFunctions) && isset($this->hashableGetterFunctions[$method])) {

            $value = $this->{$this->hashableGetterFunctions[$method]};

            if ($value) {
                $value = Hashids::encode($value);
            }

            return $value;
        }

        if (isset($this->hashableGetterArrayFunctions) && isset($this->hashableGetterArrayFunctions[$method])) {

            $value = $this->{$this->hashableGetterArrayFunctions[$method]};

            if (count($value) > 0) {
                $valueArray = [];

                foreach ($value as $productId) {
                    $valueArray[] = Hashids::encode($productId);
                }

                $value = $valueArray;
            }

            return $value;
        }

        return parent::__call($method, $arguments);
    }

    public function getXIDAttribute()
    {
        return Hashids::encode($this->id);
    }

    public function getDefaultAttribute()
    {
        return $this->default;
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'xid';
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // If the field is explicitly specified, use default Laravel behavior
        if ($field && $field !== 'xid') {
            return parent::resolveRouteBinding($value, $field);
        }

        // Try to decode the hashable ID
        $decoded = Hashids::decode($value);
        
        if (empty($decoded)) {
            return null;
        }

        // Use the decoded ID to find the model
        return $this->where('id', $decoded[0])->first();
    }
}