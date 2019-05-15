<?php

namespace App\Validators;

class PolyExistsValidator
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        if (!$objectType = array_get($validator->getData(), $parameters[0], false)) {
            return false;
        }

        try {
            return !empty(resolve($objectType)->find($value));
        } catch (\Exception $e) {
            \Log::error($e->getMessage());

            return false;
        }
    }
}
