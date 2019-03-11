<?php

namespace App\Validators;

/**
 * Class KeepWordValidator.
 */
class KeepWordValidator
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        return !in_array($value, config('filter.words')) && !in_array($value, array_map('str_plural', config('filter.words'))); // str_plural(),辅助函数，将字符串转为复数
    }
}
