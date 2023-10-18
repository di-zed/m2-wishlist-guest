<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\WishlistGuest\Plugin\Wishlist\Controller\Index;

use DiZed\WishlistGuest\Controller\Product\Shared;
use DiZed\WishlistGuest\Helper\Data;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Wishlist\Controller\Index\Add as Subject;

/**
 * Plugin for the Wishlist Add controller.
 *
 * @see Subject
 */
class Add
{
    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Plugin constructor.
     *
     * @param RedirectInterface $redirect
     * @param Data $helper
     */
    public function __construct(
        RedirectInterface $redirect,
        Data $helper
    ) {
        $this->redirect = $redirect;
        $this->helper = $helper;
    }

    /**
     * Return to the shared wishlist if this request from there.
     *
     * @param Subject $subject
     * @param Redirect $resultRedirect
     * @return Redirect
     * @see Subject::execute
     */
    public function afterExecute(
        Subject $subject,
        Redirect $resultRedirect
    ) {
        if (!$this->helper->isShareUrlEnabled()) {
            return $resultRedirect;
        }

        if ($refererUrl = $this->redirect->getRefererUrl()) {
            if (strpos($this->redirect->getRefererUrl(), Shared::URL_ROUTE) !== false) {
                $resultRedirect->setUrl($refererUrl);
            }
        }

        return $resultRedirect;
    }
}
