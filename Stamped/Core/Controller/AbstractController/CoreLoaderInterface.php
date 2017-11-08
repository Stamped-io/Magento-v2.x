<?php

namespace Stamped\Core\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

interface CoreLoaderInterface
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Stamped\Core\Model\Core
     */
    public function load(RequestInterface $request, ResponseInterface $response);
}
