<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\WishlistGuest\Controller\Product;

use DiZed\WishlistGuest\Api\WishlistManagementInterface;
use DiZed\WishlistGuest\Helper\Data;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Phrase;

/**
 * Merge shared wishlist into real customer wishlist.
 */
class Merge implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * Route to the current action.
     */
    const URL_ROUTE = 'wishlist/product/merge';

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

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
     * @param RequestInterface $request
     * @param RedirectFactory $resultRedirectFactory
     * @param Validator $formKeyValidator
     * @param ManagerInterface $messageManager
     * @param WishlistManagementInterface $wishlistManagement
     * @param Data $helper
     */
    public function __construct(
        RequestInterface $request,
        RedirectFactory $resultRedirectFactory,
        Validator $formKeyValidator,
        ManagerInterface $messageManager,
        WishlistManagementInterface $wishlistManagement,
        Data $helper
    ) {
        $this->request = $request;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->messageManager = $messageManager;
        $this->wishlistManagement = $wishlistManagement;
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath(Shared::URL_ROUTE);

        return new InvalidRequestException(
            $resultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        $validFormKey = $this->formKeyValidator->validate($request);
        return ($validFormKey && $this->request->isPost());
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        if (!$this->helper->isShareUrlEnabled()) {
            throw new NotFoundException(__('Page not found.'));
        }

        $sharingCode = (string)$this->request->getParam(Shared::CODE_PARAM_KEY);
        if (!$sharedWishlist = $this->wishlistManagement->getWishlistByCode($sharingCode)) {
            throw new NotFoundException(__('Page not found.'));
        }
        if (!$wishlist = $this->wishlistManagement->getWishlist()) {
            throw new NotFoundException(__('Page not found.'));
        }

        $resultRedirect = $this->resultRedirectFactory->create();

        foreach ($this->wishlistManagement->getWishlistItems($sharedWishlist) as $sharedWishlistItem) {
            try {
                $sharedProduct = $sharedWishlistItem->getProduct();
                if (!$sharedProduct || !$sharedProduct->isVisibleInCatalog()) {
                    throw new LocalizedException(__('A product %s can not be specified.', $sharedProduct->getSku()));
                }
                if (!$this->helper->isSharedProductMerged()) {
                    if ($this->wishlistManagement->getWishlistItemByProductId($sharedProduct->getId(), $wishlist)) {
                        continue;
                    }
                }
                $result = $wishlist->addNewItem($sharedProduct, $sharedWishlistItem->getBuyRequest());
                if (is_string($result)) {
                    throw new LocalizedException(__($result));
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $resultRedirect->setPath(Shared::URL_ROUTE);
                return $resultRedirect;
            } catch (\Exception $e) {
                continue;
            }
        }

        $this->messageManager->addSuccessMessage(__('All products were merged into the wishlist.'));
        $resultRedirect->setPath('wishlist/index/index', ['wishlist_id' => $wishlist->getId()]);

        return $resultRedirect;
    }
}
