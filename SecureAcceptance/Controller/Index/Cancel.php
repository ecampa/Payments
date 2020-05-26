<?php
namespace Payments\SecureAcceptance\Controller\Index;

use Payments\SecureAcceptance\Gateway\Config\Config;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Framework\Controller\ResultFactory;

class Cancel extends \Payments\Core\Action\CsrfIgnoringAction
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    /**
     * Cancel constructor.
     * @param Context $context
     * @param Session $session
     * @param Config $config
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Session $session,
        \Payments\SecureAcceptance\Gateway\Config\Config $config
    ) {
        $this->config = $config;
        parent::__construct($context);
    }

    public function execute()
    {
        $url = $this->_url->getUrl('checkout');

        if (!$this->config->getUseIFrame()) {
            return $this->_redirect('checkout');
        }
        $html = '<html>
                    <body>
                        <script type="text/javascript">
                            window.onload = function() {
                                parent.window.location = "'.$url.'";
                            };
                        </script>
                    </body>
                </html>';
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setContents($html);
        return $result;
    }
}
