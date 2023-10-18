<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\WishlistGuest\Api;

use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Wishlist;

/**
 * Wishlist Management Interface.
 */
interface WishlistManagementInterface
{
    /**
     * Cookie name for the "wishlist code" value.
     */
    const COOKIE_WISHLIST_CODE = 'dized_wishlist_guest_code';

    /**
     * Get wishlist.
     *
     * @param int $wishlistId
     * @return Wishlist|null
     */
    public function getWishlist(int $wishlistId = 0): ?Wishlist;

    /**
     * Get wishlist items.
     *
     * @param Wishlist|null $wishlist
     * @param int $wishlistId
     * @return Item[]
     */
    public function getWishlistItems(Wishlist $wishlist = null, int $wishlistId = 0): array;

    /**
     * Get wishlist by sharing code.
     *
     * @param string $wishlistCode
     * @return Wishlist|null
     */
    public function getWishlistByCode(string $wishlistCode): ?Wishlist;

    /**
     * Get wishlist item by product ID.
     *
     * @param int $productId
     * @param Wishlist|null $wishlist
     * @return Item|null
     */
    public function getWishlistItemByProductId(int $productId, Wishlist $wishlist = null): ?Item;

    /**
     * Get cookie wishlist.
     *
     * @return Wishlist|null
     */
    public function getCookieWishlist(): ?Wishlist;

    /**
     * Get cookie wishlist ID.
     *
     * @return int
     */
    public function getCookieWishlistId(): int;

    /**
     * Get cookie wishlist sharing code.
     *
     * @return string
     */
    public function getCookieWishlistCode(): string;

    /**
     * Set cookie wishlist sharing code.
     *
     * @param string $wishlistCode
     * @return bool
     */
    public function setCookieWishlistCode(string $wishlistCode): bool;

    /**
     * Delete cookie wishlist sharing code.
     *
     * @return bool
     */
    public function deleteCookieWishlistCode(): bool;
}
