<?php

namespace Payments\SecureAcceptance\Setup;

use Payments\SecureAcceptance\Model\Ui\ConfigProvider;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    /**
     * @var \Magento\Vault\Model\PaymentTokenRepository
     */
    private $paymentTokenRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Payments\SecureAcceptance\Gateway\Config\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @param \Magento\Vault\Model\PaymentTokenRepository $paymentTokenRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Payments\SecureAcceptance\Gateway\Config\Config $config
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     */
    public function __construct(
        \Magento\Vault\Model\PaymentTokenRepository $paymentTokenRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Payments\SecureAcceptance\Gateway\Config\Config $config,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->config = $config;
        $this->serializer = $serializer;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '3.2.3', '<')) {
            $this->updateVaultPaymentTokensWithMerchantId($setup);
        }
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    private function updateVaultPaymentTokensWithMerchantId(ModuleDataSetupInterface $setup)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            PaymentTokenInterface::PAYMENT_METHOD_CODE,
            \Payments\SecureAcceptance\Model\Ui\ConfigProvider::CODE
        )->create();

        $tokens =  $this->paymentTokenRepository->getList($searchCriteria)->getItems();

        foreach ($tokens as $token) {
            try {
                $details = $this->serializer->unserialize($token->getTokenDetails());
                if (!empty($details['merchantId']) || empty($details['incrementId'])) {
                    continue;
                }

                $select = $setup->getConnection()
                    ->select()
                    ->from($setup->getTable('sales_order'), ['store_id'])
                    ->where('increment_id = ?', $details['incrementId']);

                if (! $storeId = $setup->getConnection()->fetchOne($select)) {
                    continue;
                }

                $details['merchantId'] = $this->config->getValue(
                    \Payments\Core\Model\AbstractGatewayConfig::KEY_MERCHANT_ID,
                    $storeId
                );

                $token->setTokenDetails($this->serializer->serialize($details));
                $this->paymentTokenRepository->save($token);
            } catch (\Exception $e) {

            }
        }
    }
}
