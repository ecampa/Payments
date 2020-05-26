<?php

namespace Payments\AccountUpdater\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Payments\AccountUpdater\Model\Config;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Cron extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var string
     */
    protected $_template = 'Payments_AccountUpdater::system/config/cron.phtml';

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Payments\AccountUpdater\Model\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getCronExpr()
    {
        return $this->config->getCronExpr();
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
}
