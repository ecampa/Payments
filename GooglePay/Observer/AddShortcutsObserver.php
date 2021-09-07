<?php
namespace Payments\GooglePay\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class AddShortcutsObserver implements ObserverInterface
{

    /**
     * @var \Payments\GooglePay\Gateway\Config\Config
     */
    private $config;

    public function __construct(
        \Payments\GooglePay\Gateway\Config\Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param EventObserver $observer
     *
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Catalog\Block\ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();
        $blocks = [
            \Payments\GooglePay\Block\Button::class => \Payments\GooglePay\Model\Ui\ConfigProvider::CODE
        ];
        foreach ($blocks as $blockInstanceName => $paymentMethodCode) {
            if (!$this->config->isActive()) {
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
