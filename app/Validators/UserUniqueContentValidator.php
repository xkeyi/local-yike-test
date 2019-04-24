<?php

namespace App\Validators;

class UserUniqueContentValidator
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        return !\request()->user()->{$parameters[0]}()
            ->where($parameters[1] ?? $attribute, $value)
            ->where('id', '!=', $parameters[2] ?? 0)
            ->exists();
    }
}
