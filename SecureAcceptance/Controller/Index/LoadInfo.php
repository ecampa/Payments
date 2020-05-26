<?php
namespace Payments\SecureAcceptance\Controller\Index;

use Magento\Framework\App\Action\Context;
use Payments\SecureAcceptance\Helper\RequestDataBuilder;
use Magento\Framework\Controller\Result\JsonFactory;
use Payments\SecureAcceptance\Helper\Vault;
use Magento\Checkout\Model\Session;

class LoadInfo extends \Magento\Framework\App\Action\Action
{
    /**
     * @var RequestDataBuilder
     */
    private $helper;

    /**
     * @var Vault
     */
    private $vaultHelper;

    /**
     * @var JsonFactory $resultJsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * LoadIFrame constructor.
     * @param Context $context
     * @param RequestDataBuilder $helper
     * @param JsonFactory $resultJsonFactory
     * @param Vault $vaultHelper
     * @param Session $session
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        Context $context,
        \Payments\SecureAcceptance\Helper\RequestDataBuilder $helper,
        JsonFactory $resultJsonFactory,
        \Payments\SecureAcceptance\Helper\Vault $vaultHelper,
        Session $session,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->vaultHelper = $vaultHelper;
        $this->session = $session;
        $this->formKeyValidator = $formKeyValidator;
        parent::__construct($context);
        $this->quoteRepository = $quoteRepository;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $quote = $this->session->getQuote();

        if ($guestEmail = $this->_request->getParam('quoteEmail')) {
            $quote->setCustomerEmail($guestEmail);
        }

        $this->vaultHelper->setVaultEnabled($this->_request->getParam('vaultIsEnabled'));

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $result->setData(['error' => __('Invalid formkey.')]);
        }

        $quote->reserveOrderId();
        $this->quoteRepository->save($quote);

        $data = [];

        try {
            $data = $this->helper->buildRequestData();
        } catch (\Exception $e) {
            $data['error'] = $e->getMessage();
        }

        $result->setData($data);
        return $result;
    }
}
