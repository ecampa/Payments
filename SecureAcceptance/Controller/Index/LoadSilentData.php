<?php
namespace Payments\SecureAcceptance\Controller\Index;

use Payments\SecureAcceptance\Gateway\Config\Config;
use Magento\Framework\App\Action\Context;
use Payments\SecureAcceptance\Helper\RequestDataBuilder;
use Payments\SecureAcceptance\Helper\Vault;
use Magento\Framework\Exception\LocalizedException;

class LoadSilentData extends \Magento\Framework\App\Action\Action
{
    /**
     * @var RequestDataBuilder
     */
    protected $helper;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Vault
     */
    protected $vaultHelper;
    
    /**
     * \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * LoadSilentData constructor.
     * @param Context $context
     * @param RequestDataBuilder $helper
     * @param Config $config
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param Vault $vaultHelper
     */
    public function __construct(
        Context $context,
        \Payments\SecureAcceptance\Helper\RequestDataBuilder $helper,
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Payments\SecureAcceptance\Helper\Vault $vaultHelper,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ) {
        $this->helper = $helper;
        $this->config = $config;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->vaultHelper = $vaultHelper;
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
    }

    public function execute()
    {

        if (!$this->config->getIsLegacyMode()) {
            $this->_forward('TokenRequest', 'SecureAcceptance');
            return;
        }

        $guestEmail = trim($this->_request->getParam('quoteEmail'));

        if (empty($guestEmail) || $guestEmail == 'null') {
            $guestEmail = null;
        }

        $cardType = $this->_request->getParam('cardType');
        $this->vaultHelper->setVaultEnabled($this->_request->getParam('vaultIsEnabled'));

        $result = $this->resultJsonFactory->create();

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $result->setData(['error' => __('Invalid formkey.')]);
        }

        if ($this->config->isTestMode()) {
            $actionUrl = $this->config->getSopServiceUrlTest();
        } else {
            $actionUrl = $this->config->getSopServiceUrl();
        }

        $data = [];

        try {
            $data = [
                'form_data' => $this->helper->buildSilentRequestData($guestEmail, null, $cardType),
                'action_url' => $actionUrl . '/silent/pay'
            ];
        } catch (\Exception $e) {
            $data['error'] = $e->getMessage();
        }

        $result = $result->setData($data);
        return $result;
    }
}
