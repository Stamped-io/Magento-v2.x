<?php

namespace Dintero\Checkout\Helper;

use Dintero\Checkout\Model\Api\Client;
use Dintero\Checkout\Model\Dintero;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config
 *
 * @package Dintero\Payment\Helper
 */
class Config extends AbstractHelper
{
    /*
     * XPATH Check if the payment method is active
     */
    const XPATH_IS_ACTIVE = 'payment/dintero/active';

    /*
     * XPATH for client id
     */
    const XPATH_CLIENT_ID = 'payment/dintero/client_id';

    /*
     * XPATH for client secret
     */
    const XPATH_CLIENT_SECRET = 'payment/dintero/client_secret';

    /*
     * XPATH for account id
     */
    const XPATH_ACCOUNT_ID = 'payment/dintero/account_id';

    /*
     * XPATH for environment
     */
    const XPATH_ENVIRONMENT = 'payment/dintero/environment';

    /*
     * XPATH for profile id
     */
    const XPATH_PROFILE_ID = 'payment/dintero/checkout_profile_id';

    /*
     * Payment action
     */
    const XPATH_PAYMENT_ACTION = 'payment/dintero/payment_action';

    /*
     * Logo Type
     */
    const XPATH_LOGO_TYPE = 'payment/dintero/logo_type';

    /*
     * Logo Color
     */
    const XPATH_LOGO_COLOR = 'payment/dintero/logo_color';

    /*
     * Logo width in pixels
     */
    const XPATH_LOGO_WIDTH = 'payment/dintero/logo_width';

    /*
     * Checkout Language
     */
    const XPATH_LANGUAGE = 'payment/dintero/language';

    /*
     * XPATH Embedded checkout enabled
     */
    const XPATH_IS_EMBEDDED = 'payment/dintero/is_embedded';

    /*
     * XPATH Express checkout enabled
     */
    const XPATH_IS_EXPRESS = 'payment/dintero/is_express';

    /*
     * XPATH Express button image
     */
    const XPATH_EXPRESS_BUTTON_IMAGE = 'payment/dintero/express_button_type';

    /*
     * Default callback delay in seconds
     */
    const DEFAULT_CALLBACK_DELAY = 30;

    /*
     * Default logo width
     */
    const DEFAULT_LOGO_WIDTH = 500;

    /*
     * Default logo color
     */
    const DEFAULT_LOGO_COLOR = '#c4c4c4';

    /*
     * Enable pay button on product page
     */
    const XPATH_PRODUCT_PAGE_BUTTON_ENABLED = 'payment/dintero/product_page_button_enabled';

    /**
     * Encryptor object used to encrypt/decrypt sensitive data
     *
     * @var EncryptorInterface $encryptor
     */
    private $encryptor;

    /** @var StoreManagerInterface $storeManager */
    private $storeManager;

    /**
     * Config constructor.
     *
     * @param Context $context
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->encryptor = $encryptor;
        $this->storeManager = $storeManager;
    }

    /**
     * Checking whether the payment method is active or not
     *
     * @param $store Store|null
     * @return bool
     */
    public function isActive(Store $store = null)
    {
        $store = $store ?? $this->storeManager->getStore();
        return $this->scopeConfig->isSetFlag(self::XPATH_IS_ACTIVE, $store->getScopeType());
    }

    /**
     * Retrieving payment session url
     *
     * @return string
     */
    public function getPlaceOrderUrl()
    {
        return $this->_getUrl('dintero/payment/place');
    }

    /**
     * Retrieving client id from configuration
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::XPATH_CLIENT_ID));
    }

    /**
     * Retrieving client secret from configuration
     *
     * @return string
     */
    public function getClientSecret()
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::XPATH_CLIENT_SECRET));
    }

    /**
     * Retrieving account id from configuration
     *
     * @return string
     */
    public function getAccountId()
    {
        return $this->scopeConfig->getValue(self::XPATH_ACCOUNT_ID);
    }

    /**
     * Retrieving environment
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_ENVIRONMENT) ? 'T' : 'P';
    }

    /**
     * Retrieving account id with environment prefix
     *
     * @return string
     */
    public function getFullAccountId()
    {
        return $this->getEnvironment() . $this->getAccountId();
    }

    /**
     * Retrieving callback url
     *
     * @return string
     */
    public function getCallbackUrl()
    {
        return $this->_getUrl('dintero/payment/response', [
            '_query' => [
                'method' => 'POST',
                'delay_callback' => self::DEFAULT_CALLBACK_DELAY
            ]
        ]);
    }

    /**
     * Retrieving profile id from configuration
     *
     * @return string
     */
    public function getProfileId()
    {
        return $this->encryptor->decrypt($this->scopeConfig->getValue(self::XPATH_PROFILE_ID));
    }

    /**
     * Retrieving return url
     *
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->_getUrl('dintero/payment/success');
    }

    /**
     * Retrieving invoice pay success url
     *
     * @return string
     */
    public function getInvoicePayUrl()
    {
        return $this->_getUrl('sales/order/history');
    }

    /**
     * Retrieving Shipping url callback
     *
     * @return string
     */
    public function getShippingCallbackUrl()
    {
        return $this->_getUrl('rest/default/V1', ['dintero' => 'shipping']);
    }

    /**
     * @return string
     */
    public function getExpressCheckoutCallback()
    {
        return $this->_getUrl(
            'rest/default/V1',
            [
                'dintero' => 'express',
                '_query' => [
                    'method' => 'POST',
                    'delay_callback' => self::DEFAULT_CALLBACK_DELAY
                ]
            ]
        );
    }

    /**
     * @return string
     */
    public function getEmbeddedCheckoutCallback()
    {
        return $this->_getUrl(
            'rest/default/V1',
            [
                'dintero' => 'embedded',
                '_query' => [
                    'method' => 'POST',
                    'delay_callback' => self::DEFAULT_CALLBACK_DELAY,
                ]
            ]
        );
    }

    /**
     * Invoice call back url
     *
     * @param Invoice $invoice
     * @return string
     */
    public function getInvoiceCallBackUrl(Invoice $invoice)
    {
        return $this->_getUrl('dintero/invoice/response', ['invoice_id' => $invoice->getId()]);
    }

    /**
     * Retrieving logo type
     *
     * @return string
     */
    public function getLogoType()
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_LOGO_TYPE) ? 'mono' : 'colors';
    }

    /**
     * Retrieving logo color
     *
     * @return string
     */
    public function getLogoColor()
    {
        $value = $this->scopeConfig->getValue(self::XPATH_LOGO_COLOR);
        return $value ?: self::DEFAULT_LOGO_COLOR;
    }

    /**
     * Retrieving logo width
     *
     * @return int
     */
    public function getLogoWidth()
    {
        $value = $this->scopeConfig->getValue(self::XPATH_LOGO_WIDTH);
        return $value ?: self::DEFAULT_LOGO_WIDTH;
    }

    /**
     * Retrieving footer logo url
     *
     * @return string
     */
    public function getFooterLogoUrl()
    {
        return $this->getProfileId() ? $this->getCheckoutLogoUrl() : $this->getDefaultLogoUrl();
    }

    /**
     * Retrieving default logo url
     *
     * @return string
     */
    public function getDefaultLogoUrl()
    {
        $baseUrl = Client::CHECKOUT_API_BASE_URL;
        $pattern = '%s/branding/logos/visa_mastercard_vipps_swish_instabank/'
            . 'variant/%s/colors/color/%s/width/%d/dintero_left_frame.svg';

        if ($this->scopeConfig->isSetFlag(self::XPATH_LOGO_TYPE)) {
            $pattern = '%s/branding/logos/visa_mastercard_vipps_swish_instabank/'
                . 'variant/%s/color/%s/width/%d/dintero_left_frame.svg';
        }

        return sprintf(
            $pattern,
            $baseUrl,
            $this->getLogoType(),
            str_replace('#', '', $this->getLogoColor()),
            $this->getLogoWidth()
        );
    }

    /**
     * Retrieving checkout logo url
     *
     * @return string
     */
    public function getCheckoutLogoUrl()
    {
        $baseUrl = Client::CHECKOUT_API_BASE_URL;
        $pattern = '%s/branding/profiles/%s/'
            . 'variant/%s/color/%s/width/%d/dintero_left_frame.svg';

        return sprintf(
            $pattern,
            $baseUrl,
            $this->getProfileId(),
            $this->getLogoType(),
            str_replace('#', '', $this->getLogoColor()),
            $this->getLogoWidth()
        );
    }

    /**
     * Retrieving language code
     *
     * @return string
     */
    public function getLanguage()
    {
        return str_replace('_', '-', $this->scopeConfig->getValue(self::XPATH_LANGUAGE));
    }

    /**
     * Resolving checkout url
     *
     * @param array $queryParams
     * @return string
     */
    public function resolveCheckoutUrl($url)
    {
        $queryParams = parse_url($url, PHP_URL_QUERY);
        $queryParams['language'] = $this->getLanguage();
        list($baseUrl) = explode('?', $url);
        return implode('?', [$baseUrl, http_build_query($queryParams)]);
    }

    /**
     * Checking whether embedded checkout is enabled
     *
     * @return bool
     */
    public function isEmbedded()
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_IS_EMBEDDED);
    }

    /**
     * Checking if express checkout is enabled
     *
     * @return bool
     */
    public function isExpress()
    {
        return $this->scopeConfig->isSetFlag(self::XPATH_IS_EXPRESS);
    }

    /**
     * Checking if payment button is enabled for product page
     *
     * @return bool
     */
    public function isProductPagePaymentButtonEnabled()
    {
        return $this->isActive()
            && $this->isExpress()
            && $this->scopeConfig->isSetFlag(self::XPATH_PRODUCT_PAGE_BUTTON_ENABLED);
    }

    /**
     * Retrieving payment action
     *
     * @return string
     */
    public function getPaymentAction()
    {
        return $this->scopeConfig->getValue(self::XPATH_PAYMENT_ACTION);
    }

    /**
     * @return bool
     */
    public function isAutocaptureEnabled()
    {
        return $this->getPaymentAction() == Dintero::ACTION_AUTHORIZE_CAPTURE;
    }

    /**
     * @return string
     */
    public function getExpressButtonImage()
    {
        return $this->scopeConfig->getValue(self::XPATH_EXPRESS_BUTTON_IMAGE);
    }
}
