<?php
namespace Payments\Core\StringUtils;


class MaxLengthFilter implements \Payments\Core\StringUtils\FilterInterface
{

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    private $stringUtils;

    /**
     * @var int
     */
    private $maxLength;

    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $stringUtils,
        $maxLength = 255
    ) {
        $this->stringUtils = $stringUtils;
        $this->maxLength = (int)$maxLength;
    }

    /**
     * @inheritDoc
     */
    public function filter($input)
    {
        return $this->stringUtils->substr($input, 0, $this->maxLength);
    }
}
