<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\WishlistGuest\Plugin\Wishlist\Controller;

use DiZed\Core\Helper\CoreHelper;
use DiZed\WishlistGuest\Api\WishlistManagementInterface;
use DiZed\WishlistGuest\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Math\Random;
use Magento\Framework\Message\ManagerInterface;
use Magento\Wishlist\Controller\WishlistProviderInterface as Subject;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;

/**
 * Plugin for the wishlist provider.
 *
 * @see Subject
 */
class WishlistProviderInterface
{
    /**
     * Is wishlist provided?
     * Workaround to avoid cycle for get wishlist in the code.
     *
     * @var bool
     */
    protected $isWishlistProvided = false;

    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var Random
     */
    protected $mathRandom;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var CoreHelper
     */
    protected $coreHelper;

    /**
     * @var WishlistManagementInterface
     */
    protected $wishlistManagement;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Plugin constructor.
     *
     * @param RemoteAddress $remoteAddress
     * @param Random $mathRandom
     * @param ManagerInterface $messageManager
     * @param Session $customerSession
     * @param WishlistFactory $wishlistFactory
     * @param CoreHelper $coreHelper
     * @param WishlistManagementInterface $wishlistManagement
     * @param Data $helper
     */
    public function __construct(
        RemoteAddress $remoteAddress,
        Random $mathRandom,
        ManagerInterface $messageManager,
        Session $customerSession,
        WishlistFactory $wishlistFactory,
        CoreHelper $coreHelper,
        WishlistManagementInterface $wishlistManagement,
        Data $helper
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->mathRandom = $mathRandom;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->wishlistFactory = $wishlistFactory;
        $this->coreHelper = $coreHelper;
        $this->wishlistManagement = $wishlistManagement;
        $this->helper = $helper;
    }

    /**
     * Check the wishlist in the cookie.
     *
     * @param Subject $wishlistProvider
     * @param Wishlist $result
     * @param string $wishlistId
     * @return Wishlist
     * @see Subject::getWishlist
     */
    public function afterGetWishlist(
        Subject $wishlistProvider,
        $result,
        $wishlistId = null
    ) {
        // workaround to avoid cycle for get wishlist in the code:
        if ($this->isWishlistProvided) {
            return $result;
        }

        // do nothing if the functionality isn't available:
        if (!$this->helper->isGuestWishlistEnabled()) {
            return $result;
        }
        // do nothing if the customer is logged in:
        if ($this->customerSession->isLoggedIn()) {
            return $result;
        }

        // do nothing if the wishlist already exists:
        if (($result instanceof Wishlist) && $result->getId()) {
            if ($result->getSharingCode() != $this->wishlistManagement->getCookieWishlistCode()) {
                $this->messageManager->addErrorMessage(__('The requested Wish List does not exist.'));
                $result = false;
            }
            return $result;
        }

        try {
            if (!$cookieWishlistCode = $this->wishlistManagement->getCookieWishlistCode()) {
                $wishlist = $this->wishlistFactory->create();
                $wishlist->setCustomerId(null);
                $wishlist->setShared(0);
                $wishlist->generateSharingCode();
                $wishlist->save();
                // set cookie wishlist ID:
                $cookieWishlistCode = $wishlist->getSharingCode();
                $this->wishlistManagement->setCookieWishlistCode($cookieWishlistCode);
                // log info:
                $this->coreHelper->getLogger()->debug('[DiZed_WishlistGuest] Created guest wishlist.', [
                    'wishlist_id' => $wishlist->getId(),
                    'client_ip' => $this->remoteAddress->getRemoteAddress(),
                ]);
            }
            // get wishlist by code:
            if ($cookieWishlist = $this->wishlistManagement->getWishlistByCode($cookieWishlistCode)) {
                $this->isWishlistProvided = true;
                $result = $wishlistProvider->getWishlist($cookieWishlist->getId());
            }
        } catch (\Exception $e) {
            $this->coreHelper->getLogger()->error('[DiZed_WishlistGuest] ' . $e->getMessage(), [
                'client_ip' => $this->remoteAddress->getRemoteAddress(),
            ]);
        }

        return $result;
    }
}
