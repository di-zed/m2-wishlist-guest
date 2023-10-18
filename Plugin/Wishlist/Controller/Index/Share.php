<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\WishlistGuest\Plugin\Wishlist\Controller\Index;

use DiZed\WishlistGuest\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\Page;
use Magento\Wishlist\Controller\Index\Share as Subject;

/**
 * Plugin for the Wishlist Share controller.
 *
 * @see Subject
 */
class Share
{
    /**
     * @var ResultFactory
     */
    protected $resultFactory;

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
     * @param ResultFactory $resultFactory
     * @param Session $customerSession
     * @param Data $helper
     */
    public function __construct(
        ResultFactory $resultFactory,
        Session $customerSession,
        Data $helper
    ) {
        $this->resultFactory = $resultFactory;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
    }

    /**
     * Enable share functionality for the guests.
     *
     * @param Subject $controller
     * @param callable $proceed
     * @return Page
     * @see Subject::execute
     */
    public function aroundExecute(
        Subject $controller,
        callable $proceed
    ) {
        if ($this->helper->isGuestWishlistEnabled() && !$this->customerSession->isLoggedIn()) {
            /** @var Page $resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $resultPage->addHandle('wishlist_index_share_guest');
            return $resultPage;
        }

        return $proceed();
    }
}
