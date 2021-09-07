<?php
namespace Payments\Core\StringUtils;


interface RulePoolInterface
{

    /**
     * Checks the existence of the rule in the pool
     *
     * @param $code
     *
     * @return bool
     */
    public function has($code);

    /**
     * Gets the rule from the pool
     *
     * @param $code
     *
     * @return string
     */
    public function get($code);
}
