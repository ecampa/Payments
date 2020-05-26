<?php
namespace Payments\Core\Setup;

use Payments\Core\Model\LoggerInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Payments\SecureAcceptance\Model\Ui\ConfigProvider;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Class UpgradeData
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     *
     * @var \Payments\Core\Model\ResourceModel\Token\Collection
     */
    private $tokenCollection;

    /**
     * @var string
     */
    private $token;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var PaymentTokenFactoryInterface
     */
    private $paymentTokenFactory;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        \Payments\Core\Model\LoggerInterface $logger,
        \Payments\Core\Model\Token $token,
        \Payments\Core\Model\ResourceModel\Token\Collection $tokenCollection,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Vault\Api\Data\PaymentTokenFactoryInterface $paymentTokenFactory,
        \Payments\SecureAcceptance\Gateway\Config\Config $config
    ) {
        $this->logger = $logger;
        $this->token = $token;
        $this->tokenCollection = $tokenCollection;
        $this->encryptor = $encryptor;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->config = $config;
    }

    public function upgrade(
        ModuleDataSetupInterface $installer,
        ModuleContextInterface $context
    ) {

        $installer->startSetup();
        
        if (version_compare($context->getVersion(), '1.0.10', '<')) {
            $table = $installer->getTable('sales_order_status');
            if ($installer->getConnection()->isTableExists($table) == true) {
                $installer->getConnection()->insert(
                    $installer->getTable('sales_order_status'),
                    [
                        'status' => 'dm_refund_review',
                        'label' => 'DM Refund Review'
                    ]
                );
                $installer->getConnection()->insert(
                    $installer->getTable('sales_order_status_state'),
                    [
                        'status' => 'dm_refund_review',
                        'state' => 'dm_refund_review',
                        'is_default' => 0,
                        'visible_on_front' => 0,
                    ]
                );
            }
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.11', '<')) {
            $table = $installer->getTable('email_template');
            if ($installer->getConnection()->isTableExists($table) == true) {
                $email_content = '{{template config_path="design/email/header_template"}}
                    <table>
                        <tr class="email-intro">
                            <td>
                                <p class="greeting">{{trans "%name," name=$order.getCustomerName()}}</p>
                                <p>
                                    {{trans
                                        "Your order #%increment_id has been cancelled by our fraud detection system.
                                        <strong>%order_status</strong>."
                                        increment_id=$order.increment_id
                                        order_status=$order.getStatusLabel() |raw}}
                                </p>

                                <p>
                                    {{trans "We apologize for any inconvenience and urge you to contact us by email: 
                                    <a href=\"mailto:%store_email\">%store_email</a>" store_email=$store_email |raw}}
                                    {{depend store_phone}}
                                    {{trans "or call us at 
                                    <a href=\"tel:%store_phone">%store_phone</a>\" store_phone=$store_phone |raw}}
                                    {{/depend}} if you believe this was cancelled in error.
                                    {{depend store_hours}}
                                    {{trans "Our hours are
                                    <span class=\"no-link\">%store_hours</span>." store_hours=$store_hours |raw}}
                                    {{/depend}}
                                </p>
                            </td>
                        </tr>
                        <tr class="email-information">
                            <td>
                                {{depend comment}}
                                <table class="message-info">
                                    <tr>
                                        <td>
                                            {{var comment|escape|nl2br}}
                                        </td>
                                    </tr>
                                </table>
                                {{/depend}}
                            </td>
                        </tr>
                    </table>
                    {{template config_path="design/email/footer_template"}}';

                $subject = '{{trans "your %store_name order has been cancelled" store_name=$store.getFrontendName()}}';

                $installer->getConnection()->insert(
                    $installer->getTable('email_template'),
                    [
                        'template_code' => 'DM Fail Transaction',
                        'template_text' => $email_content,
                        'template_styles' => '',
                        'template_type' => 2,
                        'template_subject' => $subject,
                        'template_sender_name' => '',
                        'template_sender_email' => '',
                    ]
                );
            }
            $installer->endSetup();
        }

        if (version_compare($context->getVersion(), '1.0.12', '<')) {
            $this->tokenCollection->load();
            if ($this->tokenCollection->getSize() > 0) {
                $this->logger->notice("Start convert token from phase2 to phase3");
                foreach ($this->tokenCollection as $item) {
                    try {
                        $paymentToken = $this->paymentTokenFactory->create(\Magento\Vault\Api\Data\PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD);
                        $paymentToken->setGatewayToken($item->getData('payment_token'));
                        $paymentToken->setExpiresAt($this->getExpirationDate($item->getData('card_expiry_date')));
                        $paymentToken->setIsVisible(true);
                        $paymentToken->setIsActive(true);
                        $paymentToken->setCustomerId($item->getData('customer_id'));
                        $paymentToken->setPaymentMethodCode(\Payments\SecureAcceptance\Model\Ui\ConfigProvider::CODE);

                        $paymentToken->setTokenDetails($this->convertDetailsToJSON([
                            'title' => $this->config->getVaultTitle(),
                            'incrementId' => $item->getData('reference_number'),
                            'type' => $this->getCardType($item->getData('card_type'), true),
                            'maskedCC' => $item->getData('cc_last4'),
                            'expirationDate' => str_replace("-", "/", $item->getData('card_expiry_date'))
                        ]));

                        $paymentToken->setPublicHash($this->generatePublicHash($paymentToken));
                        $paymentToken->save();
                        $this->logger->notice("Token Id: ".$item->getId());
                    } catch (\Exception $e) {
                        $this->logger->error($e->getMessage());
                    }
                }
                $this->logger->notice("End convert token from phase2 to phase3");
            }
        }

        $installer->endSetup();
    }

    /**
     * @return string
     */
    private function getExpirationDate($cardExpiry)
    {
        $cardExpiry = explode("-", $cardExpiry);
        $expDate = new \DateTime(
            $cardExpiry[1]
            . '-'
            . $cardExpiry[0]
            . '-'
            . '01'
            . ' '
            . '00:00:00',
            new \DateTimeZone('UTC')
        );
        $expDate->add(new \DateInterval('P1M'));
        return $expDate->format('Y-m-d 00:00:00');
    }

    /**
     * Convert payment token details to JSON
     * @param array $details
     * @return string
     */
    private function convertDetailsToJSON($details)
    {
        $json = \Zend_Json::encode($details);
        return $json ? $json : '{}';
    }

    /**
     * Generate vault payment public hash
     *
     * @param PaymentTokenInterface $paymentToken
     * @return string
     */
    private function generatePublicHash(PaymentTokenInterface $paymentToken)
    {
        $hashKey = $paymentToken->getGatewayToken();
        if ($paymentToken->getCustomerId()) {
            $hashKey = $paymentToken->getCustomerId();
        }

        $hashKey .= $paymentToken->getPaymentMethodCode()
            . $paymentToken->getType()
            . $paymentToken->getTokenDetails();

        return $this->encryptor->getHash($hashKey);
    }

    /**
     * @param $code
     * @param bool $isMagentoType
     * @return mixed
     */
    private function getCardType($code, $isMagentoType = false)
    {
        $types = [
            'VI' => '001',
            'MC' => '002',
            'AE' => '003',
            'DI' => '004',
            'DN' => '005',
            'JCB' => '007',
            'MI' => '042',
        ];

        if ($isMagentoType) {
            $types = array_flip($types);
        }

        return (!empty($types[$code])) ? $types[$code] : $code;
    }
}
