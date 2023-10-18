<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\WishlistGuest\Helper\Customer;

use DiZed\WishlistGuest\Helper\Data;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;

/**
 * Helper Customer View.
 */
class View extends \Magento\Customer\Helper\View
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
     * Helper constructor.
     *
     * @param Context $context
     * @param CustomerMetadataInterface $customerMetadataService
     * @param Session $customerSession
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        CustomerMetadataInterface $customerMetadataService,
        Session $customerSession,
        Data $helper
    ) {
        parent::__construct($context, $customerMetadataService);

        $this->customerSession = $customerSession;
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function getCustomerName(CustomerInterface $customerData)
    {
        if ($this->helper->isGuestWishlistEnabled() && !$this->customerSession->isLoggedIn()) {
            return __('Guest')->render();
        }

        return parent::getCustomerName($customerData);
    }
}
