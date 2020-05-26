<?php
namespace Payments\BankTransfer\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;

class Cancel extends \Magento\Framework\App\Action\Action
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Payments\Core\Model\Checkout\PaymentFailureRouteProviderInterface
     */
    private $paymentFailureRouteProvider;

    /**
     * Receipt constructor.
     *
     * @param Context $context
     * @param \Payments\Core\Model\Checkout\PaymentFailureRouteProviderInterface $paymentFailureRouteProvider
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Payments\Core\Model\Checkout\PaymentFailureRouteProviderInterface $paymentFailureRouteProvider,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->paymentFailureRouteProvider = $paymentFailureRouteProvider;
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->paymentFailureRouteProvider->getFailureRoutePath());
        $this->messageManager->addErrorMessage(__('Your order has been cancelled. Please try again!'));
        return $resultRedirect;
    }
}
