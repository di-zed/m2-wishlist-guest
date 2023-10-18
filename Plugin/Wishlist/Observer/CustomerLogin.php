<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\WishlistGuest\Plugin\Wishlist\Observer;

use DiZed\Core\Helper\CoreHelper;
use DiZed\WishlistGuest\Api\WishlistManagementInterface;
use DiZed\WishlistGuest\Helper\Data;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\ItemFactory;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Wishlist\Observer\CustomerLogin as Subject;

/**
 * Plugin for the Wishlist Customer Login observer.
 *
 * @see Subject
 */
class CustomerLogin
{
    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var ItemFactory
     */
    protected $wishlistItemFactory;

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
     * @param WishlistFactory $wishlistFactory
     * @param ItemFactory $wishlistItemFactory
     * @param CoreHelper $coreHelper
     * @param WishlistManagementInterface $wishlistManagement
     * @param Data $helper
     */
    public function __construct(
        RemoteAddress $remoteAddress,
        WishlistFactory $wishlistFactory,
        ItemFactory $wishlistItemFactory,
        CoreHelper $coreHelper,
        WishlistManagementInterface $wishlistManagement,
        Data $helper
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->wishlistFactory = $wishlistFactory;
        $this->wishlistItemFactory = $wishlistItemFactory;
        $this->coreHelper = $coreHelper;
        $this->wishlistManagement = $wishlistManagement;
        $this->helper = $helper;
    }

    /**
     * Merge guest and customer wishlists.
     *
     * @param Subject $subject
     * @param Observer $observer
     * @return Observer[]
     * @see Subject::execute
     */
    public function beforeExecute(
        Subject $subject,
        Observer $observer
    ) {
        if (!$this->helper->isGuestWishlistEnabled()) {
            return [$observer];
        }
        if (!$cookieWishlistCode = $this->wishlistManagement->getCookieWishlistCode()) {
            return [$observer];
        }

        /** @var Customer $customer */
        $customer = $observer->getCustomer();

        try {
            /** @var Wishlist $wishlistGuest */
            if ($wishlistGuest = $this->wishlistManagement->getCookieWishlist()) {
                $wishlist = $this->wishlistFactory->create();
                $wishlist->loadByCustomerId($customer->getId(), true);
                foreach ($this->wishlistManagement->getWishlistItems($wishlistGuest) as $wishlistGuestItem) {
                    // move the item from guest wishlist to the real wishlist:
                    if (!$this->isGuestItemMerged($wishlist, $wishlistGuestItem)) {
                        $wishlistGuestItem->setWishlistId($wishlist->getId());
                        $wishlistGuestItem->save();
                    }
                }
                // log info:
                $this->coreHelper->getLogger()->debug('[DiZed_WishlistGuest] Wishlists were merged.', [
                    'wishlist_id' => $wishlist->getId(),
                    'customer_id' => $customer->getId(),
                    'client_ip' => $this->remoteAddress->getRemoteAddress(),
                ]);
                $wishlistGuest->delete();
            }
            $this->wishlistManagement->deleteCookieWishlistCode();
        } catch (\Exception $e) {
            $this->coreHelper->getLogger()->error('[DiZed_WishlistGuest] ' . $e->getMessage(), [
                'guest_wishlist_code' => $cookieWishlistCode,
                'customer_id' => $customer->getId(),
                'client_ip' => $this->remoteAddress->getRemoteAddress(),
            ]);
        }

        return [$observer];
    }

    /**
     * Does the guest wishlist item exist on the customer's actual wishlist?
     *
     * @param Wishlist $wishlist
     * @param Item $wishlistGuestItem
     * @return bool
     */
    public function isGuestItemMerged(Wishlist $wishlist, Item $wishlistGuestItem): bool
    {
        foreach ($this->wishlistManagement->getWishlistItems($wishlist) as $wishlistItem) {
            if ($wishlistItem->getProductId() == $wishlistGuestItem->getProductId()) {
                if ($wishlistItem->getStoreId() == $wishlistGuestItem->getStoreId()) {
                    return true;
                }
            }
        }

        return false;
    }
}
