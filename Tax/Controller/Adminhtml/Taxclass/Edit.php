<?php
namespace Payments\Tax\Controller\Adminhtml\Taxclass;
  
class Edit extends \Payments\Tax\Controller\Adminhtml\Taxclass\Index
{
    
   /**
    * @return void
    */
    public function execute()
    {
        $id = $this->getRequest()->getParam('class_id');
        if ($id) {
            $this->modelTax->load($id);
            if (!$this->modelTax->getId()) {
                $this->messageManager->addError(__('This tax class no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }
        
        $this->_coreRegistry->register('tax_class_model', $this->modelTax);
 
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Payments_Tax::paymentstax_tax');
        $resultPage->getConfig()->getTitle()->prepend(__('Tax Class'));
        return $resultPage;
    }
}
