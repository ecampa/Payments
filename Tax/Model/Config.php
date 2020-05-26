<?php

namespace Payments\Tax\Model;

class Config extends \Magento\Tax\Model\Config
{
    const TAX_ENABLED = 'tax/paymentstax/tax_enabled';
    const TAX_COUNTRIES = 'tax/paymentstax/tax_countries';
    const TAX_SHIP_FROM_CITY = 'tax/paymentstax/ship_from_city';
    const TAX_SHIP_FROM_POSTCODE = 'tax/paymentstax/ship_from_postcode';
    const TAX_SHIP_FROM_COUNTRY = 'tax/paymentstax/ship_from_country';
    const TAX_SHIP_FROM_REGION = 'tax/paymentstax/ship_from_region';
    const TAX_ACCEPTANCE_CITY = 'tax/paymentstax/acceptance_city';
    const TAX_ACCEPTANCE_POSTCODE = 'tax/paymentstax/acceptance_postcode';
    const TAX_ACCEPTANCE_COUNTRY = 'tax/paymentstax/acceptance_country';
    const TAX_ACCEPTANCE_REGION = 'tax/paymentstax/acceptance_region';
    const TAX_ORIGIN_CITY = 'tax/paymentstax/origin_city';
    const TAX_ORIGIN_POSTCODE = 'tax/paymentstax/origin_postcode';
    const TAX_ORIGIN_COUNTRY = 'tax/paymentstax/origin_country';
    const TAX_ORIGIN_REGION = 'tax/paymentstax/origin_region';
    const TAX_MERCHANT_VAT = 'tax/paymentstax/merchant_vat';
    const TAX_NEXUS_REGION = 'tax/paymentstax/payments_nexus_regions';
    const TAX_DEFAULT_CODE = 'paymentsdefaulttax';

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($scopeConfig);
    }

    public function getTaxShipFromCity()
    {
        return $this->_scopeConfig->getValue(
            self::TAX_SHIP_FROM_CITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTaxShipFromCountry()
    {
        return $this->_scopeConfig->getValue(
            self::TAX_SHIP_FROM_COUNTRY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTaxShipFromRegion()
    {
        return $this->_scopeConfig->getValue(
            self::TAX_SHIP_FROM_REGION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTaxShipFromPostcode()
    {
        return $this->_scopeConfig->getValue(
            self::TAX_SHIP_FROM_POSTCODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTaxNexusRegions($separator = null)
    {
        if ($separator) {
            return str_replace(
                ',',
                $separator,
                $this->_scopeConfig->getValue(
                    self::TAX_NEXUS_REGION,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
        }

        return $this->_scopeConfig->getValue(
            self::TAX_NEXUS_REGION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTaxAcceptanceCity()
    {
        return $this->_scopeConfig->getValue(
            self::TAX_ACCEPTANCE_CITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTaxAcceptanceCountry()
    {
        return $this->_scopeConfig->getValue(
            self::TAX_ACCEPTANCE_COUNTRY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTaxAcceptanceRegion()
    {
        return $this->_scopeConfig->getValue(
            self::TAX_ACCEPTANCE_REGION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTaxAcceptancePostcode()
    {
        return $this->_scopeConfig->getValue(
            self::TAX_ACCEPTANCE_POSTCODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTaxOriginCity()
    {
        return $this->_scopeConfig->getValue(
            self::TAX_ORIGIN_CITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTaxOriginCountry()
    {
        return $this->_scopeConfig->getValue(
            self::TAX_ORIGIN_COUNTRY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTaxOriginRegion()
    {
        return $this->_scopeConfig->getValue(
            self::TAX_ORIGIN_REGION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTaxOriginPostcode()
    {
        return $this->_scopeConfig->getValue(
            self::TAX_ORIGIN_POSTCODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTaxMerchantVat()
    {
        return $this->_scopeConfig->getValue(
            self::TAX_MERCHANT_VAT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isTaxEnabled()
    {
        return (bool) $this->_scopeConfig->getValue(
            self::TAX_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTaxCountries()
    {
        return $this->_scopeConfig->getValue(
            self::TAX_COUNTRIES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
