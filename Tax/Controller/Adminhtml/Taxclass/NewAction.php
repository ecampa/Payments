<?php
namespace Payments\Tax\Controller\Adminhtml\Taxclass;
  
class NewAction extends \Payments\Tax\Controller\Adminhtml\Taxclass\Index
{
   /**
    * Create new news action
    *
    * @return void
    */
    public function execute()
    {
        $this->_forward('edit');
    }
}
