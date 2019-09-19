<?php
namespace Stamped\Core\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
class Button extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Path to block template
     */
    const CHECK_TEMPLATE = 'stamped/system/config/button.phtml';
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     */
    public function __construct(
    \Magento\Backend\Block\Template\Context $context,
    \Magento\Framework\App\Request\Http $request,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigObject
    ) {
        $this->scopeConfigObject = $scopeConfigObject;
        $this->request = $request;
       
        parent::__construct($context);
       
    }

    protected function _prepareLayout()
    {
       
        parent::_prepareLayout();
        
            $this->setTemplate('stamped/system/config/button.phtml');
        
        return $this;
    }
    /**
     * @param AbstractElement $element
     * @return string
     * @codeCoverageIgnore
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label' => __("Import History Orders"),
                'intern_url' => $this->getUrl('core/index/index',array('store'=>$this->getRequest()->getParam('store'))),
                'html_id' => $element->getHtmlId(),
            ]
        );
        return $this->_toHtml();
    }
}