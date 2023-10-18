<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\WishlistGuest\Block\Customer;

use DiZed\WishlistGuest\Helper\Data;
use Magento\Catalog\Block\Product\Context as BlockContext;
use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Wishlist\Block\Customer\Wishlist as WishlistBlock;

/**
 * Customer Wishlist block.
 */
class Wishlist extends WishlistBlock
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Block constructor.
     *
     * @param BlockContext $context
     * @param HttpContext $httpContext
     * @param ConfigurationPool $helperPool
     * @param CurrentCustomer $currentCustomer
     * @param PostHelper $postDataHelper
     * @param Session $customerSession
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        BlockContext $context,
        HttpContext $httpContext,
        ConfigurationPool $helperPool,
        CurrentCustomer $currentCustomer,
        PostHelper $postDataHelper,
        Session $customerSession,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $httpContext, $helperPool, $currentCustomer, $postDataHelper, $data);

        $this->customerSession = $customerSession;
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    protected function _toHtml()
    {
        if ($this->helper->isGuestWishlistEnabled() && !$this->customerSession->isLoggedIn()) {
            if ($this->getTemplate()) {
                try {
                    return $this->fetchView($this->getTemplateFile());
                } catch (\Exception $e) {
                    return parent::_toHtml();
                }
            }
        }

        return parent::_toHtml();
    }
}
