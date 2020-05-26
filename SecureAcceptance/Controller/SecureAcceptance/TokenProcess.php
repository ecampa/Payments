<?php
namespace Payments\SecureAcceptance\Controller\SecureAcceptance;

use Payments\SecureAcceptance\Helper\RequestDataBuilder;

class TokenProcess extends \Magento\Framework\App\Action\Action
{


    /**
     * @var \Magento\Payment\Gateway\Command\CommandManagerInterface
     */
    private $commandManager;

    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Payments\Core\Model\LoggerInterface
     */
    private $logger;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    /**
     * @var \Payments\Core\Model\Checkout\PaymentFailureRouteProviderInterface
     */
    private $paymentFailureRouteProvider;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Payment\Gateway\Command\CommandManagerInterface $commandManager,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\LayoutFactory $layoutFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Payments\Core\Model\LoggerInterface $logger,
        \Payments\Core\Model\Checkout\PaymentFailureRouteProviderInterface $paymentFailureRouteProvider,
        \Payments\SecureAcceptance\Gateway\Config\Config $config
    ) {
        parent::__construct($context);


        $this->commandManager = $commandManager;
        $this->quoteManagement = $quoteManagement;
        $this->cartRepository = $cartRepository;
        $this->registry = $registry;
        $this->layoutFactory = $layoutFactory;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->paymentFailureRouteProvider = $paymentFailureRouteProvider;
        $this->config = $config;
    }

    /**
     *
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {

        $result = [];
        $quote = null;

        try {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->cartRepository->get($this->getRequest()->getParam(
                'req_' . \Payments\SecureAcceptance\Helper\RequestDataBuilder::KEY_QUOTE_ID
            ));

            if (!$quote || !$quote->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Unable to load card data.'));
            }

            if (!$quote->getIsActive()) {
                $result['redirect'] = $this->_url->getUrl($this->paymentFailureRouteProvider->getFailureRoutePath());
                $this->registry->register(\Magento\Payment\Block\Transparent\Iframe::REGISTRY_KEY, $result);
                return $this->getResultLayout(['payments_iframe_payment_response']);
            }

            $this->commandManager->executeByCode(
                \Payments\SecureAcceptance\Gateway\Command\TokenHandleResponseCommand::COMMAND_NAME,
                $quote->getPayment(),
                ['response' => $this->getRequest()->getParams()]
            );

            $this->cartRepository->save($quote);

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result['error_msg'] = $e->getMessage();
            $this->logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
        } catch (\Exception $e) {
            $result['error_msg'] = __('Unable to handle token response');
            $this->logger->critical($e->getMessage());
        }

        if (!$this->config->isSilent()) {
            $result['payload'] = [
                'method' => \Payments\SecureAcceptance\Model\Ui\ConfigProvider::CODE,
                'extension_attributes' => [
                    'agreement_ids' => explode(
                        ',',
                        $this->getRequest()->getParam('req_' . \Payments\SecureAcceptance\Helper\RequestDataBuilder::KEY_AGREEMENT_IDS)
                    ),
                ],
            ];
        }

        if ($quote && !$quote->getCustomerId()) {
            $result['email'] = $this->getRequest()->getParam('req_bill_to_email');
        }

        $this->registry->register(\Magento\Payment\Block\Transparent\Iframe::REGISTRY_KEY, $result);

        if ($this->isRedirect()) {

            if (!empty($result['error_msg'])) {
                $this->messageManager->addErrorMessage($result['error_msg']);
                $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setPath($this->paymentFailureRouteProvider->getFailureRoutePath());
                return $resultRedirect;
            }

            $this->messageManager->addWarningMessage(__('Your payment is being processed. Do not close this page.'));

            /** @var \Magento\Framework\View\Result\Page $pageResult */
            $pageResult = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
            $pageResult->getLayout()->getUpdate()->addHandle(['payments_iframe_payment_response_redirect']);
            return $pageResult;
        }

        if (!$this->config->isSilent() && $this->config->getUseIFrame()) {
            return $this->getResultLayout(['payments_iframe_payment_response_hosted_iframe']);
        }

        return $this->getResultLayout(['payments_iframe_payment_response']);
    }

    private function isRedirect()
    {
        return !$this->config->isSilent() && !$this->config->getUseIFrame();
    }

    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    private function getResultLayout(array $handles = [])
    {

        $resultLayout = $this->layoutFactory->create();
        $resultLayout->addDefaultHandle();
        $resultLayout->getLayout()->getUpdate()->load($handles);

        return $resultLayout;
    }
}
