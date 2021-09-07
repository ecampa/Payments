<?php
namespace Payments\GooglePay\Gateway\Config;

/**
 * Class Config
 */
class Config extends \Payments\Core\Model\AbstractGatewayConfig
{

    const KEY_GOOGLE_MERCHANT_ID = 'google_merchant_id';
    const KEY_DISPLAY_NAME = 'display_name';
    const KEY_CC_TYPES = 'cctypes';
    const KEY_BUTTON_SHOW_PDP = 'button_pdp_is_visible';
    const KEY_BUTTON_SHOW_MINICART = 'button_minicart_is_visible';

    /**
     * Returns config value with fallback to core
     *
     * @param string $field
     * @param null $storeId
     *
     * @return mixed
     */
    public function getValue($field, $storeId = null)
    {
        $this->setMethodCode(\Payments\GooglePay\Model\Ui\ConfigProvider::CODE);
        $value = parent::getValue($field, $storeId);
        if ($value === null) {
            $this->setMethodCode(\Payments\Core\Model\Config::CODE);
            $value = parent::getValue($field, $storeId);
            $this->setMethodCode(\Payments\GooglePay\Model\Ui\ConfigProvider::CODE);
        }

        return $value;
    }

    public function getGoogleMerchantId($storeId = null)
    {
        return $this->getValue(static::KEY_GOOGLE_MERCHANT_ID, $storeId);
    }

    public function getMerchantDisplayName($storeId = null)
    {
        return $this->getValue(static::KEY_DISPLAY_NAME, $storeId);
    }

    public function getCcTypes($storeId = null)
    {
        $configuredValue = $this->getValue(static::KEY_CC_TYPES, $storeId);

        if (empty($configuredValue)) {
            // Extremely weird case that no card types allowed, but the module is enabled.
            // We must pass some card to avoid the error.
            return ['VI'];
        }

        return explode(',', $configuredValue);
    }

    public function buttonShowInCart($storeId = null)
    {
        return $this->getValue(static::KEY_BUTTON_SHOW_MINICART, $storeId);
    }

    public function buttonShowPdp($storeId = null)
    {
        return $this->getValue(static::KEY_BUTTON_SHOW_PDP, $storeId);
    }

}
