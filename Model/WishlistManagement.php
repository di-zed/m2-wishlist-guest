<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\WishlistGuest\Model;

use DiZed\WishlistGuest\Api\WishlistManagementInterface;
use DiZed\WishlistGuest\Helper\Data;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\ResourceModel\Wishlist\CollectionFactory;
use Magento\Wishlist\Model\Wishlist;

/**
 * Wishlist Management.
 */
class WishlistManagement implements WishlistManagementInterface
{
    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var CollectionFactory
     */
    protected $wishlistCollectionFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Management constructor.
     *
     * @param SessionManagerInterface $sessionManager
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param WishlistProviderInterface $wishlistProvider
     * @param CollectionFactory $wishlistCollectionFactory
     * @param Data $helper
     */
    public function __construct(
        SessionManagerInterface $sessionManager,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        WishlistProviderInterface $wishlistProvider,
        CollectionFactory $wishlistCollectionFactory,
        Data $helper
    ) {
        $this->sessionManager = $sessionManager;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->wishlistProvider = $wishlistProvider;
        $this->wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function getWishlist(int $wishlistId = 0): ?Wishlist
    {
        try {
            $wishlist = $this->wishlistProvider->getWishlist($wishlistId);
            if (!$wishlist || !$wishlist->getId()) {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }

        return $wishlist;
    }

    /**
     * @inheritDoc
     */
    public function getWishlistItems(Wishlist $wishlist = null, int $wishlistId = 0): array
    {
        if (!$wishlist) {
            if (!$wishlist = $this->getWishlist($wishlistId)) {
                return [];
            }
        }

        try {
            return $wishlist->getItemCollection()->getItems();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @inheritDoc
     */
    public function getWishlistByCode(string $wishlistCode): ?Wishlist
    {
        if (!trim($wishlistCode)) {
            return null;
        }

        $collection = $this->wishlistCollectionFactory->create();
        $collection->addFieldToFilter('sharing_code', $wishlistCode);

        /** @var Wishlist $wishlist */
        $wishlist = $collection->getFirstItem();
        if (!$wishlist || !$wishlist->getId()) {
            return null;
        }

        return $wishlist;
    }

    /**
     * @inheritDoc
     */
    public function getWishlistItemByProductId(int $productId, Wishlist $wishlist = null): ?Item
    {
        foreach ($this->getWishlistItems($wishlist) as $wishlistItem) {
            if ($wishlistItem->getProductId() == $productId) {
                return $wishlistItem;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function getCookieWishlist(): ?Wishlist
    {
        if (!$wishlistCode = $this->getCookieWishlistCode()) {
            return null;
        }

        return $this->getWishlistByCode($wishlistCode);
    }

    /**
     * @inheritDoc
     */
    public function getCookieWishlistId(): int
    {
        if (!$wishlist = $this->getCookieWishlist()) {
            return 0;
        }

        return (int)$wishlist->getId();
    }

    /**
     * @inheritDoc
     */
    public function getCookieWishlistCode(): string
    {
        return (string)$this->cookieManager->getCookie(self::COOKIE_WISHLIST_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setCookieWishlistCode(string $wishlistCode): bool
    {
        if (!trim($wishlistCode)) {
            return false;
        }

        try {
            $metadata = $this->cookieMetadataFactory
                ->createPublicCookieMetadata()
                ->setDuration($this->helper->getGuestWishlistLifetime())
                ->setPath($this->sessionManager->getCookiePath())
                ->setDomain($this->sessionManager->getCookieDomain());
            $this->cookieManager->setPublicCookie(self::COOKIE_WISHLIST_CODE, $wishlistCode, $metadata);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteCookieWishlistCode(): bool
    {
        try {
            $metadata = $this->cookieMetadataFactory
                ->createPublicCookieMetadata()
                ->setDuration($this->helper->getGuestWishlistLifetime())
                ->setPath($this->sessionManager->getCookiePath())
                ->setDomain($this->sessionManager->getCookieDomain());
            $this->cookieManager->deleteCookie(self::COOKIE_WISHLIST_CODE, $metadata);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
