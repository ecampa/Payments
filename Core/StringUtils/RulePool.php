<?php
namespace Payments\Core\StringUtils;


class RulePool implements \Payments\Core\StringUtils\RulePoolInterface
{

    /**
     * @var string[]
     */
    private $rules;

    public function __construct($rules = [])
    {
        $this->rules = $rules;
    }

    /**
     * @inheritDoc
     */
    public function has($code)
    {
        return isset($this->rules[$code]);
    }

    /**
     * @inheritDoc
     */
    public function get($code)
    {
        if (!isset($this->rules[$code])) {
            throw new \Magento\Framework\Exception\NotFoundException(__('The string filter rule "%1" doesn\'t exist.', $code));
        }

        return $this->rules[$code];
    }
}
