<?php
namespace Payments\Core\Plugin\Gateway\Request;

class StringFilterPlugin
{

    /**
     * @var \Payments\Core\StringUtils\FilterPoolInterface
     */
    private $filterPool;

    /**
     * @var \Payments\Core\StringUtils\RulePoolInterface
     */
    private $rulePool;

    public function __construct(
        \Payments\Core\StringUtils\FilterPoolInterface $filterPool,
        \Payments\Core\StringUtils\RulePoolInterface $rulePool
    ) {
        $this->filterPool = $filterPool;
        $this->rulePool = $rulePool;
    }

    public function afterBuild($subject, $result)
    {
        array_walk_recursive($result, [$this, 'filterField']);

        return $result;
    }


    /**
     * @param $value
     * @param $key
     *
     * @return string
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    private function filterField(&$value, $key)
    {
        if (!$this->rulePool->has($key)) {
            return $value;
        }
        $value = $this->filterPool->get(
            $this->rulePool->get($key)
        )
            ->filter($value);

        return $value;
    }

}
