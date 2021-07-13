<?php

namespace Stamped\Core\Controller\AbstractController;

use Magento\Framework\App\Action;
use Magento\Framework\View\Result\PageFactory;

abstract class View extends Action\Action
{
    /**
     * @var CoreLoaderInterface $coreLoader
     */
    protected $coreLoader;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * View constructor.
     *
     * @param Action\Context $context
     * @param CoreLoaderInterface $coreLoader
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        CoreLoaderInterface $coreLoader,
        PageFactory $resultPageFactory
    ) {
        $this->coreLoader = $coreLoader;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public function execute()
    {
        if (!$this->coreLoader->load($this->_request, $this->_response)) {
            return;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        return $this->resultPageFactory->create();
    }
}
