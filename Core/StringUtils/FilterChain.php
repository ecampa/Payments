<?php
namespace Payments\Core\StringUtils;


class FilterChain implements \Payments\Core\StringUtils\FilterInterface
{
    /**
     * @var \Payments\Core\StringUtils\FilterInterface[]
     */
    private $filters;

    /**
     * @param \Magento\Framework\ObjectManager\TMapFactory $tmapFactory
     * @param array $filters
     */
    public function __construct(
        \Magento\Framework\ObjectManager\TMapFactory $tmapFactory,
        array $filters = []
    ) {
        $this->filters = $tmapFactory->create(
            [
                'array' => $filters,
                'type' => \Payments\Core\StringUtils\FilterInterface::class,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function filter($input)
    {
        foreach ($this->filters as $filter) {
            $input = $filter->filter($input);
        }

        return $input;
    }
}
