<?php
namespace Payments\SecureAcceptance\Controller\Microform;

class TokenRequest extends \Magento\Framework\App\Action\Action
{

    const COMMAND_CODE = 'generate_flex_key';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Payment\Gateway\Command\CommandManagerInterface
     */
    private $commandManager;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var \Payments\Core\Model\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;

    /**
     * TokenRequest constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Payment\Gateway\Command\CommandManagerInterface $commandManager
     * @param \Magento\Framework\Session\SessionManagerInterface $sessionManager
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Payments\Core\Model\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Payment\Gateway\Command\CommandManagerInterface $commandManager,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Payments\Core\Model\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->commandManager = $commandManager;
        $this->sessionManager = $sessionManager;
        $this->logger = $logger;
        $this->formKeyValidator = $formKeyValidator;
        $this->quoteRepository = $quoteRepository;
    }


    /**
     * Creates SA request JSON
     *
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {

        $result = $this->resultJsonFactory->create();

        try {

            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $this->sessionManager->getQuote();

            if (!$this->getRequest()->isPost()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Wrong method.'));
            }

            if (!$quote || !$quote->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Unable to load cart data.'));
            }

            if (!$this->formKeyValidator->validate($this->getRequest())) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid formkey.'));
            }

            $commandResult = $this->commandManager->executeByCode(
                self::COMMAND_CODE,
                $quote->getPayment()
            );

            $commandResult = $commandResult->get();

            $publicKey = $commandResult['der']['publicKey'] ?? null;

            if (!$publicKey) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Failed to get Flex Microform keys. Please verify your extension configuration.'));
            }

            $quote->getPayment()->setAdditionalInformation('microformPublicKey', $publicKey);
            $this->quoteRepository->save($quote);

            $result->setData(
                [
                    'success' => true,
                    'token' => $commandResult['jwk']
                ]
            );

        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
            $result->setData(['error_msg' => __('Unable to build Token request.')]);
        }

        return $result;
    }
}
