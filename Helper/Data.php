<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\WishlistGuest\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Session\Config;
use Magento\Store\Model\ScopeInterface;

/**
 * Helper Data.
 */
class Data extends AbstractHelper
{
    /**
     * Path for the module status.
     */
    const XML_PATH_ENABLED = 'dized_wishlist_guest/general/enabled';

    /**
     * Path for the "Enable for Guests" status.
     */
    const XML_PATH_IS_GUEST_WISHLIST_ENABLED = 'dized_wishlist_guest/general/is_guest_wishlist_enabled';

    /**
     * Path for the "Lifetime of Guest Wishlist" value.
     */
    const XML_PATH_GUEST_WISHLIST_LIFETIME = 'dized_wishlist_guest/general/guest_wishlist_lifetime';

    /**
     * Path for the "Share Wishlist by URL" status.
     */
    const XML_PATH_IS_SHARE_URL_ENABLED = 'dized_wishlist_guest/general/is_share_url_enabled';

    /**
     * Path for the "Merge Shared Products" status.
     */
    const XML_PATH_IS_SHARED_PRODUCT_MERGED = 'dized_wishlist_guest/general/is_shared_product_merged';

    /**
     * Path for the "Remove Old Guest Wishlists" status.
     */
    const XML_PATH_IS_OLD_GUEST_WISHLIST_REMOVED = 'dized_wishlist_guest/general/is_old_guest_wishlist_removed';

    /**
     * Path for the "Interval for Removing Old Wishlists" value.
     */
    const XML_PATH_GUEST_WISHLIST_REMOVING_INTERVAL = 'dized_wishlist_guest/general/guest_wishlist_removing_interval';

    /**
     * Is module enabled?
     *
     * @return bool
     */
    public function isModuleEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Is the Wishlist enabled for Guests?
     *
     * @return bool
     */
    public function isGuestWishlistEnabled(): bool
    {
        if ($this->isModuleEnabled()) {
            return $this->scopeConfig->isSetFlag(self::XML_PATH_IS_GUEST_WISHLIST_ENABLED, ScopeInterface::SCOPE_STORE);
        }

        return false;
    }

    /**
     * Get a Lifetime of Guest Wishlist in SECONDS.
     *
     * @return int
     */
    public function getGuestWishlistLifetime(): int
    {
        if ($this->isGuestWishlistEnabled()) {
            $value = (int)$this->scopeConfig->getValue(
                self::XML_PATH_GUEST_WISHLIST_LIFETIME,
                ScopeInterface::SCOPE_STORE
            );
            if (!$value) {
                $value = (int)$this->scopeConfig->getValue(
                    Config::XML_PATH_COOKIE_LIFETIME,
                    ScopeInterface::SCOPE_STORE
                );
            }
            return $value;
        }

        return 0;
    }

    /**
     * Is Wishlist can be Shared by URL?
     *
     * @return bool
     */
    public function isShareUrlEnabled(): bool
    {
        if ($this->isModuleEnabled()) {
            return $this->scopeConfig->isSetFlag(self::XML_PATH_IS_SHARE_URL_ENABLED, ScopeInterface::SCOPE_STORE);
        }

        return false;
    }

    /**
     * Is Shared Product should be Merged?
     *
     * @return bool
     */
    public function isSharedProductMerged(): bool
    {
        if ($this->isShareUrlEnabled()) {
            return $this->scopeConfig->isSetFlag(self::XML_PATH_IS_SHARED_PRODUCT_MERGED, ScopeInterface::SCOPE_STORE);
        }

        return false;
    }

    /**
     * Is Old Guest Wishlist should be Removed?
     *
     * @return bool
     */
    public function isOldGuestWishlistRemoved(): bool
    {
        if ($this->isModuleEnabled()) {
            return $this->scopeConfig->isSetFlag(
                self::XML_PATH_IS_OLD_GUEST_WISHLIST_REMOVED,
                ScopeInterface::SCOPE_STORE
            );
        }

        return false;
    }

    /**
     * Get an Interval for Removing Old Guest Wishlists in DAYS.
     *
     * @return int
     */
    public function getGuestWishlistRemovingInterval(): int
    {
        if ($this->isOldGuestWishlistRemoved()) {
            return (int)$this->scopeConfig->getValue(
                self::XML_PATH_GUEST_WISHLIST_REMOVING_INTERVAL,
                ScopeInterface::SCOPE_STORE
            );
        }

        return 0;
    }
}
