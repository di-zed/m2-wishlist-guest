<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\WishlistGuest\Block\Product;

use DiZed\WishlistGuest\Api\WishlistManagementInterface;
use DiZed\WishlistGuest\Controller\Product\Merge;
use DiZed\WishlistGuest\Controller\Product\Shared as SharedController;
use DiZed\WishlistGuest\Helper\Data;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Wishlist;

/**
 * Block for shared wishlist.
 */
class Shared extends Template
{
    /**
     * Cache "wishlist" data key.
     */
    const CACHE_WISHLIST_KEY = 'dized_wishlist_guest_data';

    /**
     * Cache "product block" data key.
     */
    const CACHE_PRODUCT_BLOCK_KEY = 'dized_wishlist_guest_product_block';

    /**
     * @var PostHelper
     */
    protected $postHelper;

    /**
     * @var WishlistManagementInterface
     */
    protected $wishlistManagement;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Block constructor.
     *
     * @param PostHelper $postHelper
     * @param Context $context
     * @param WishlistManagementInterface $wishlistManagement
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        PostHelper $postHelper,
        Context $context,
        WishlistManagementInterface $wishlistManagement,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->postHelper = $postHelper;
        $this->wishlistManagement = $wishlistManagement;
        $this->helper = $helper;
    }

    /**
     * Get wishlist.
     *
     * @return Wishlist|null
     */
    public function getWishlist(): ?Wishlist
    {
        return $this->wishlistManagement->getWishlist();
    }

    /**
     * Get wishlist item by product ID.
     *
     * @param int $productId
     * @param Wishlist|null $wishlist
     * @return Item|null
     */
    public function getWishlistItemByProductId(int $productId, Wishlist $wishlist = null): ?Item
    {
        return $this->wishlistManagement->getWishlistItemByProductId($productId, $wishlist);
    }

    /**
     * Get shared wishlist.
     *
     * @return Wishlist|null
     */
    public function getSharedWishlist(): ?Wishlist
    {
        if (!$this->hasData(self::CACHE_WISHLIST_KEY)) {
            $sharingCode = (string)$this->getRequest()->getParam(SharedController::CODE_PARAM_KEY);
            $wishlist = $this->wishlistManagement->getWishlistByCode($sharingCode);
            $this->setData(self::CACHE_WISHLIST_KEY, $wishlist);
        }

        return $this->getData(self::CACHE_WISHLIST_KEY);
    }

    /**
     * Get shared wishlist items.
     *
     * @return Item[]
     */
    public function getSharedWishlistItems(): array
    {
        $result = [];

        try {
            if ($wishlist = $this->getSharedWishlist()) {
                $result = $this->wishlistManagement->getWishlistItems($wishlist);
            }
        } catch (\Exception $e) {
            return [];
        }

        return $result;
    }

    /**
     * Get abstract product block.
     *
     * @return AbstractProduct
     * @throws LocalizedException
     */
    public function getProductBlock(): AbstractProduct
    {
        if (!$this->hasData(self::CACHE_PRODUCT_BLOCK_KEY)) {
            $block = $this->getLayout()->createBlock(AbstractProduct::class);
            $this->setData(self::CACHE_PRODUCT_BLOCK_KEY, $block);
        }

        return $this->getData(self::CACHE_PRODUCT_BLOCK_KEY);
    }

    /**
     * Get post data for merging shared data to the real customer wishlist.
     *
     * @return string
     */
    public function getMergePostData(): string
    {
        return $this->postHelper->getPostData(
            $this->_escaper->escapeUrl($this->getUrl(Merge::URL_ROUTE)),
            [
                SharedController::CODE_PARAM_KEY => $this->getSharedWishlist()->getSharingCode(),
            ]
        );
    }

    /**
     * @inheritDoc
     */
    protected function _toHtml()
    {
        if (!$this->helper->isShareUrlEnabled() || !$this->getSharedWishlist()) {
            return '';
        }

        return parent::_toHtml();
    }
}
