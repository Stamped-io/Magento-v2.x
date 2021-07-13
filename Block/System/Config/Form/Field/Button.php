<?php
namespace Stamped\Core\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Button extends \Magento\Config\Block\System\Config\Form\Field
{
    /*
     * Path to block template
     */
    const CHECK_TEMPLATE = 'stamped/system/config/button.phtml';

    /**
     * @var string $_template
     */
    protected $_template = 'Stamped_Core::stamped/system/config/button.phtml';

    /**
     * @param AbstractElement $element
     * @return string
     * @codeCoverageIgnore
     */
    public function render(AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->addData(
            [
                'button_label' => __('Import History Orders'),
                'intern_url' => $this->getUrl(
                    'core/index/index',
                    [
                        'store' => $this->getRequest()->getParam('store')
                    ]
                ),
                'html_id' => $element->getHtmlId(),
            ]
        );
        return $this->_toHtml();
    }
}
