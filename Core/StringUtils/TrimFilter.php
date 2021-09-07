<?php
namespace Payments\Core\StringUtils;


class TrimFilter implements \Payments\Core\StringUtils\FilterInterface
{

    /**
     * @inheritDoc
     */
    public function filter($input)
    {
        return trim($input);
    }
}
