<?php

namespace Stamped\Core\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

class CoreLoader implements CoreLoaderInterface
{
    /**
     * @var \Stamped\Core\Model\CoreFactory
     */
    protected $coreFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * CoreLoader constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        PageFactory $resultPageFactory
    ) {
        $this->_storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return bool
     */
    public function load(RequestInterface $request, ResponseInterface $response)
    {
        if (!$id = (int) $request->getParam('id')) {
            $request->initForward();
            $request->setActionName('noroute');
            $request->setDispatched(false);
            return false;
        }

        $core = $this->coreFactory->create()->load($id);
        $this->registry->register('current_core', $core);
        return true;
    }
}
