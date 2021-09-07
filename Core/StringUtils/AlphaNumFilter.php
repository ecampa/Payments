<?php
namespace Payments\Core\StringUtils;


class AlphaNumFilter implements \Payments\Core\StringUtils\FilterInterface
{

    public function filter($input)
    {
        return preg_replace("/[^[:alnum:][:space:]]/u", '', $input);
    }
}
