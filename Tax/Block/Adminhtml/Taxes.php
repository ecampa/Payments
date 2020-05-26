<?php
namespace Payments\Tax\Block\Adminhtml;

class Taxes extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'taxes/grid.phtml';

    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'add_new_test_post',
            'label' => __('Add New'),
            'class' => 'add primary',
            'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        $this->setChild(
            'grid',
            $this->getLayout()->createBlock('Payments\Tax\Block\Adminhtml\Taxes\Grid', 'payments.taxes.grid')
        );
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl(
            '*/*/new'
        );
    }

    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}
