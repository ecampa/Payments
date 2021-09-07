<?php
namespace Payments\Core\StringUtils;


class FilterPool implements \Payments\Core\StringUtils\FilterPoolInterface
{

    /**
     * @var \Payments\Core\StringUtils\FilterInterface[]
     */
    private $filters;

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
    public function get($code)
    {
        if (!isset($this->filters[$code])) {
            throw new \Magento\Framework\Exception\NotFoundException(__('The filter "%1" doesn\'t exist.', $code));
        }

        return $this->filters[$code];
    }
}
