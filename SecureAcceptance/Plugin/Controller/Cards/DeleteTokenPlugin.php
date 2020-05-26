<?php
namespace Payments\SecureAcceptance\Plugin\Controller\Cards;

use Payments\SecureAcceptance\Helper\RequestDataBuilder;
use Payments\SecureAcceptance\Service\GatewaySoapApi;
use Magento\Vault\Model\PaymentTokenManagement;
use Magento\Framework\App\Request\Http;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Customer\Model\Session;

class DeleteTokenPlugin
{
    /**
     * @var PaymentTokenManagement
     */
    private $paymentTokenManagement;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var RequestDataBuilder
     */
    private $requestDataBuilder;

    private $gatewayApi;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirect;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    private $response;

    /**
     * DeleteTokenPlugin constructor.
     * @param PaymentTokenManagement $paymentTokenManagement
     * @param Session $customerSession
     * @param RequestDataBuilder $requestDataBuilder
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        PaymentTokenManagement $paymentTokenManagement,
        Session $customerSession,
        \Payments\SecureAcceptance\Helper\RequestDataBuilder $requestDataBuilder,
        \Payments\SecureAcceptance\Service\GatewaySoapApi $gatewayApi,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->customerSession = $customerSession;
        $this->requestDataBuilder = $requestDataBuilder;
        $this->gatewayApi = $gatewayApi;
        $this->redirect = $redirect;
        $this->messageManager = $messageManager;
        $this->response = $response;
    }

    public function aroundExecute(\Magento\Vault\Controller\Cards\DeleteAction $subject, \Closure $proceed)
    {
        $request = $subject->getRequest();
        $paymentToken = $this->getPaymentToken($request);

        if ($paymentToken !== null && !empty($paymentToken->getData())) {

            if ($paymentToken->getPaymentMethodCode() != \Payments\SecureAcceptance\Model\Ui\ConfigProvider::CODE) {
                return $proceed();
            }

            $requestData = $this->requestDataBuilder->buildDeleteTokenRequest($paymentToken);

            $result = $this->gatewayApi->run($requestData);

            if ($result && $result->reasonCode !== 100) {
                $this->messageManager->addErrorMessage(__('Deletion failure. Please try again. Error: '. $result->reasonCode));
                $this->redirect->redirect($subject->getResponse(), 'vault/cards/listaction');
            } else {
                return $proceed();
            }
        }
    }

    /**
     * @param Http $request
     * @return PaymentTokenInterface|null
     */
    private function getPaymentToken(Http $request)
    {
        $publicHash = $request->getPostValue(PaymentTokenInterface::PUBLIC_HASH);

        if ($publicHash === null) {
            return null;
        }

        return $this->paymentTokenManagement->getByPublicHash(
            $publicHash,
            $this->customerSession->getCustomerId()
        );
    }
}
