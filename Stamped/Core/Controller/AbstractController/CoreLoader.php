<?php

namespace Stamped\Core\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;

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
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
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
        $id = (int)$request->getParam('id');
        if (!$id) {
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
