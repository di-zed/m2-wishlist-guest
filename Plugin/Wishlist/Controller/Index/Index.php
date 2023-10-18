<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\WishlistGuest\Plugin\Wishlist\Controller\Index;

use DiZed\WishlistGuest\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\Page;
use Magento\Wishlist\Controller\Index\Index as Subject;

/**
 * Plugin for the Wishlist Index controller.
 *
 * @see Subject
 */
class Index
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
     * Plugin constructor.
     *
     * @param Session $customerSession
     * @param Data $helper
     */
    public function __construct(
        Session $customerSession,
        Data $helper
    ) {
        $this->customerSession = $customerSession;
        $this->helper = $helper;
    }

    /**
     * Change layout for the guest customers.
     *
     * @param Subject $controller
     * @param Page $resultPage
     * @return Page
     * @see Subject::execute
     */
    public function afterExecute(
        Subject $controller,
        Page $resultPage
    ) {
        if ($this->helper->isGuestWishlistEnabled() && !$this->customerSession->isLoggedIn()) {
            $resultPage->addHandle('wishlist_index_index_guest');
        }

        return $resultPage;
    }
}
