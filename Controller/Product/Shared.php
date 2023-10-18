<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\WishlistGuest\Controller\Product;

use DiZed\WishlistGuest\Api\WishlistManagementInterface;
use DiZed\WishlistGuest\Helper\Data;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;

/**
 * List of shared products.
 */
class Shared implements HttpGetActionInterface
{
    /**
     * Route to the current action.
     */
    const URL_ROUTE = 'wishlist/product/shared';

    /**
     * Code parameter key.
     */
    const CODE_PARAM_KEY = 'code';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var WishlistManagementInterface
     */
    protected $wishlistManagement;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Action constructor.
     *
     * @param PageFactory $resultPageFactory
     * @param RequestInterface $request
     * @param WishlistManagementInterface $wishlistManagement
     * @param Data $helper
     */
    public function __construct(
        PageFactory $resultPageFactory,
        RequestInterface $request,
        WishlistManagementInterface $wishlistManagement,
        Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->wishlistManagement = $wishlistManagement;
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        if (!$this->helper->isShareUrlEnabled()) {
            throw new NotFoundException(__('Page not found.'));
        }

        $sharingCode = (string)$this->request->getParam(self::CODE_PARAM_KEY);
        if (!$this->wishlistManagement->getWishlistByCode($sharingCode)) {
            throw new NotFoundException(__('Page not found.'));
        }

        return $this->resultPageFactory->create();
    }
}
