<?php

namespace Payments\AccountUpdater\Model\Config\Backend\Cron;

class Validator
{
    /**
     * @param string $expr
     * @return bool
     */
    public function validate($expr)
    {
        return count(explode(' ', $expr)) === 5;
    }
}
