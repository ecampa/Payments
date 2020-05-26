<?php
namespace Payments\VisaCheckout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Payments\VisaCheckout\Gateway\Config\Config as VCConfig;

/**
 * Visacheckout module observer
 */
class AddVCShortcutsObserver implements ObserverInterface
{
    /**
     * @var VCConfig
     */
    protected $vcConfig;

    /**
     * Constructor
     *
     * @param VCConfig $vcConfig
     */
    public function __construct(
        \Payments\VisaCheckout\Gateway\Config\Config $vcConfig
    ) {
        $this->vcConfig = $vcConfig;
    }

    /**
     * Add Visacheckout shortcut buttons
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Catalog\Block\ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();
        $blocks = [
            \Payments\VisaCheckout\Block\Shortcut::class => \Payments\VisaCheckout\Gateway\Config\Config::CODE
        ];
        foreach ($blocks as $blockInstanceName => $paymentMethodCode) {
            if (!$this->vcConfig->isActive()) {
                continue;
            }

            $shortcut = $shortcutButtons->getLayout()->createBlock($blockInstanceName);
            $shortcut->setIsInCatalogProduct(
                $observer->getEvent()->getIsCatalogProduct()
            )->setShowOrPosition(
                $observer->getEvent()->getOrPosition()
            );
            $shortcutButtons->addShortcut($shortcut);
        }
    }
}
