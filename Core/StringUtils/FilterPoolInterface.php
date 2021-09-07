<?php
namespace Payments\Core\StringUtils;


interface FilterPoolInterface
{
    /**
     * @param string $code
     *
     * @return \Payments\Core\StringUtils\FilterInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function get($code);
}
