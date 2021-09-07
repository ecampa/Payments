<?php
namespace Payments\Core\StringUtils;


interface FilterInterface
{

    /**
     * @param string $input
     *
     * @return string
     */
    public function filter($input);

}
