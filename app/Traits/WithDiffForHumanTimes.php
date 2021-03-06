<?php

namespace App\Traits;

trait WithDiffForHumanTimes
{
    public function __call($method, $arguments)
    {
        if (\ends_with($method, 'TimeagoAttribute')) {
            $date = $this->{snake_case(\substr($method, 3, -16))};

            return $date ? $date->diffForHumans() : null;
        }

        return parent::__call($method, $arguments);
    }
}
