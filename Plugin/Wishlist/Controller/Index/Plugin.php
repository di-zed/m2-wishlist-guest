<?php
/**
 * @author DiZed Team
 * @copyright Copyright (c) DiZed Team (https://github.com/di-zed/)
 */
namespace DiZed\WishlistGuest\Plugin\Wishlist\Controller\Index;

use DiZed\WishlistGuest\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Store\Model\ScopeInterface;
use Magento\Wishlist\Controller\Index\Plugin as Subject;

/**
 * Plugin for the Wishlist Before Dispatch plugin.
 *
 * @see Subject
 */
class Plugin
{
    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Plugin constructor.
     *
     * @param ScopeConfigInterface $config
     * @param Session $customerSession
     * @param Data $helper
     */
    public function __construct(
        ScopeConfigInterface $config,
        Session $customerSession,
        Data $helper
    ) {
        $this->config = $config;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
    }

    /**
     * Disable authentication check.
     *
     * @param Subject $plugin
     * @param callable $proceed
     * @param ActionInterface $subject
     * @param RequestInterface $request
     * @return void
     * @throws NotFoundException
     * @see Subject::beforeDispatch
     */
    public function aroundBeforeDispatch(
        Subject $plugin,
        callable $proceed,
        ActionInterface $subject,
        RequestInterface $request
    ) {
        if (!$this->helper->isGuestWishlistEnabled() || $this->customerSession->isLoggedIn()) {
            return $proceed($subject, $request);
        }
        if (!$this->config->isSetFlag('wishlist/general/active', ScopeInterface::SCOPE_STORES)) {
            throw new NotFoundException(__('Page not found.'));
        }
    }
}
