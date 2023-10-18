<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\WishlistGuest\Cron;

use DiZed\Core\Helper\CoreHelper;
use DiZed\WishlistGuest\Helper\Data;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Wishlist\Model\ResourceModel\Wishlist\CollectionFactory;

/**
 * Remove old wishlists.
 */
class RemoveWishlists
{
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var CollectionFactory
     */
    protected $wishlistCollectionFactory;

    /**
     * @var CoreHelper
     */
    protected $coreHelper;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Cron Job constructor.
     *
     * @param TimezoneInterface $timezone
     * @param CollectionFactory $wishlistCollectionFactory
     * @param CoreHelper $coreHelper,
     * @param Data $helper
     */
    public function __construct(
        TimezoneInterface $timezone,
        CollectionFactory $wishlistCollectionFactory,
        CoreHelper $coreHelper,
        Data $helper
    ) {
        $this->timezone = $timezone;
        $this->wishlistCollectionFactory = $wishlistCollectionFactory;
        $this->coreHelper = $coreHelper;
        $this->helper = $helper;
    }

    /**
     * Find and remove old wishlists.
     *
     * @return void
     */
    public function execute()
    {
        if (!$intervalDays = $this->helper->getGuestWishlistRemovingInterval()) {
            return;
        }

        $startDate = $this->timezone->date()->modify('-' . $intervalDays . ' days')->format('Y-m-d H:i:s');

        $collection = $this->wishlistCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', ['null' => true]);
        $collection->addFieldToFilter('updated_at', ['lt' => $startDate]);

        foreach ($collection->getItems() as $wishlist) {
            try {
                $wishlist->delete();
            } catch (\Exception $e) {
                $this->coreHelper->getLogger()->error(
                    '[DiZed_WishlistGuest] ' . $e->getMessage(),
                    (array)$wishlist->getData()
                );
                continue;
            }
        }
    }
}
