<?php
namespace Payments\Tax\Service;


/**
 * Class CachedTaxService
 */
class CachedTaxService implements \Payments\Tax\Service\TaxServiceInterface
{
    const CACHE_GROUP = \Payments\Tax\Model\Cache\Type::TYPE_IDENTIFIER;
    const CACHE_KEY_PREFIX = 'payments_tax_';

    /**
     * @var \Payments\Tax\Service\TaxServiceInterface
     */
    private $nonCachedTaxService;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    private $cacheStorage;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    private $cacheState;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var \Magento\Customer\Api\Data\RegionInterfaceFactory
     */
    private $regionFactory;

    /**
     * @var \Payments\Tax\Model\Config
     */
    private $taxConfig;

    /**
     * CachedTaxService constructor.
     *
     * @param \Payments\Tax\Service\TaxServiceInterface $nonCachedTaxService
     * @param \Magento\Framework\App\CacheInterface $cacheStorage
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory
     * @param \Magento\Customer\Api\Data\RegionInterfaceFactory $regionFactory
     * @param \Payments\Tax\Model\Config $taxConfig
     */
    public function __construct(
        \Payments\Tax\Service\TaxServiceInterface $nonCachedTaxService,
        \Magento\Framework\App\CacheInterface $cacheStorage,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $regionFactory,
        \Payments\Tax\Model\Config $taxConfig
    ) {
        $this->nonCachedTaxService = $nonCachedTaxService;
        $this->cacheStorage = $cacheStorage;
        $this->cacheState = $cacheState;
        $this->serializer = $serializer;
        $this->addressFactory = $addressFactory;
        $this->regionFactory = $regionFactory;
        $this->taxConfig = $taxConfig;
    }

    /**
     * @inheritDoc
     */
    public function getTaxForOrder(\Magento\Tax\Api\Data\QuoteDetailsInterface $quoteTaxDetails, $storeId = null)
    {

        $cacheId = $this->getCacheId($quoteTaxDetails);

        $cachedResult = $this->loadCache($cacheId);
        if ($cachedResult !== false) {
            return $this->serializer->unserialize($cachedResult);
        }

        $result = $this->nonCachedTaxService->getTaxForOrder($quoteTaxDetails);

        $this->saveCache($cacheId, $result);

        return $result;
    }

    private function loadCache($cacheId)
    {
        if (!$this->cacheState->isEnabled(self::CACHE_GROUP)) {
            return false;
        }

        return $this->cacheStorage->load($cacheId);
    }

    private function saveCache($cacheId, $data)
    {
        if (!$data || !$this->cacheState->isEnabled(self::CACHE_GROUP)) {
            return;
        }

        $this->cacheStorage->save(
            $this->serializer->serialize($data),
            $cacheId,
            [\Payments\Tax\Model\Cache\Type::CACHE_TAG]
        );
    }

    private function getCacheId(\Magento\Tax\Api\Data\QuoteDetailsInterface $quoteTaxDetails)
    {
        return self::CACHE_KEY_PREFIX . implode('|', $this->getCacheKeyInfo($quoteTaxDetails));
    }

    /**
     * @param \Magento\Tax\Api\Data\QuoteDetailsInterface $quoteTaxDetails
     *
     * @return array
     */
    private function getCacheKeyInfo(\Magento\Tax\Api\Data\QuoteDetailsInterface $quoteTaxDetails)
    {
        return array_merge(
            $this->getAddressCacheKeys($quoteTaxDetails->getShippingAddress()),
            $this->getAddressCacheKeys($this->getShipFromAddress()),
            $this->getItemsCacheKeys($quoteTaxDetails->getItems())
        );
    }

    /**
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    private function getShipFromAddress()
    {
        return $this->addressFactory->create()
            ->setCountryId($this->taxConfig->getTaxShipFromCountry())
            ->setRegion($this->regionFactory->create()->setRegionCode($this->taxConfig->getTaxShipFromRegion()))
            ->setPostcode($this->taxConfig->getTaxShipFromPostcode());
    }

    /**
     * @param \Magento\Customer\Api\Data\AddressInterface|null $address
     *
     * @return array
     */
    private function getAddressCacheKeys($address)
    {
        if (!$address) {
            return [];
        }

        return [
            $address->getCountryId(),
            $address->getRegion()->getRegionCode(),
            $address->getPostcode(),
        ];
    }

    /**
     * @param \Magento\Tax\Api\Data\QuoteDetailsItemInterface[] $items
     *
     * @return array
     */
    private function getItemsCacheKeys($items)
    {
        $itemsKeys = [];
        foreach ($items as $item) {
            $itemsKeys = array_merge($itemsKeys, $this->getItemCacheKey($item));
        }
        return $itemsKeys;
    }

    private function getItemCacheKey(\Magento\Tax\Api\Data\QuoteDetailsItemInterface $item)
    {
        $keys = [
            $item->getType(),
            $item->getQuantity(),
            $item->getDiscountAmount(),
        ];

        $extensionAttributes = $item->getExtensionAttributes();

        if (!$extensionAttributes) {
            $keys[] = $item->getUnitPrice();
            return $keys;
        }

        $keys[] = $extensionAttributes->getPriceForTaxCalculation() ?: $item->getUnitPrice();

        $keys[] = ($item->getType() == \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::ITEM_TYPE_SHIPPING)
            ? 'shipping'
            : $extensionAttributes->getSku();

        return $keys;
    }
}
