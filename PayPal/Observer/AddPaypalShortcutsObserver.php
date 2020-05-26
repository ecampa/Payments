<?php
namespace Payments\PayPal\Observer;

use Magento\Framework\Event\ObserverInterface;
use Payments\PayPal\Model\Config as PaypalConfig;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * PayPal module observer
 */
class AddPaypalShortcutsObserver implements ObserverInterface
{
    /**
     * @var PaypalConfig
     */
    protected $paypalConfig;

    /**
     * Constructor
     *
     * @param PaypalConfig $paypalConfig
     */
    public function __construct(
        \Payments\PayPal\Model\Config $paypalConfig
    ) {
        $this->paypalConfig = $paypalConfig;
    }

    /**
     * Add PayPal shortcut buttons
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Catalog\Block\ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();
        $blocks = [
            \Payments\PayPal\Block\Express\InContext\Minicart\Button::class => \Payments\PayPal\Model\Config::CODE,
            \Payments\PayPal\Block\Express\Shortcut::class => \Payments\PayPal\Model\Config::CODE,
            \Payments\PayPal\Block\Bml\Shortcut::class => \Payments\PayPal\Model\Config::CODE,
        ];
        foreach ($blocks as $blockInstanceName => $paymentMethodCode) {
            if (!$this->paypalConfig->isActive()) {
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
