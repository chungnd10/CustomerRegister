<?php

namespace Fastest\CustomerRegister\Controller\Account;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\Phrase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoginPost extends AbstractAccount implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    private $_customerFactory;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerUrl $customerHelperData
     * @param Validator $formKeyValidator
     * @param AccountRedirect $accountRedirect
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerUrl $customerHelperData,
        Validator $formKeyValidator,
        AccountRedirect $accountRedirect,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Model\Customer $customer
    ) {
        $this->customer = $customer;
        $this->_customerFactory = $customerFactory;
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerUrl = $customerHelperData;
        $this->formKeyValidator = $formKeyValidator;
        $this->accountRedirect = $accountRedirect;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Get scope config
     *
     * @return ScopeConfigInterface
     * @deprecated 100.0.10
     */
    private function getScopeConfig()
    {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        } else {
            return $this->scopeConfig;
        }
    }

    /**
     * Retrieve cookie manager
     *
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     * @deprecated 100.1.0
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     * @deprecated 100.1.0
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/');

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
        return null;
    }

    /**
     * Login post action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if ($this->session->isLoggedIn() || !$this->formKeyValidator->validate($this->getRequest())) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        $email = "";
        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');

            if (strpos($login['username'], '@') == false) {

                // Get phone number
                $getCustomerByPhoneNumber = $this->customer->getCollection()
                    ->addAttributeToFilter('phone_number', $login['username'])
                    ->getFirstItem();
                // Set phone number
                $email = $getCustomerByPhoneNumber->getEmail();

                if ($email == null) {
                    // Get username
                    $getCustomerByUserName = $this->customer->getCollection()
                        ->addAttributeToFilter('username_customer', $login['username'])
                        ->getFirstItem();
                    // Set user name
                    $email = $getCustomerByUserName->getEmail();
                }
            } else {
                $email = $login['username'];
            }

            if ( !empty($login['username']) && !empty($login['password'])) {
                try {
                    $customer = $this->customerAccountManagement->authenticate($email, $login['password']);
                    $this->session->setCustomerDataAsLoggedIn($customer);
                    if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                        $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                        $metadata->setPath('/');
                        $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                    }
                    $redirectUrl = $this->accountRedirect->getRedirectCookie();
                    if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                        $this->accountRedirect->clearRedirectCookie();
                        $resultRedirect = $this->resultRedirectFactory->create();
                        // URL is checked to be internal in $this->_redirect->success()
                        $resultRedirect->setUrl($this->_redirect->success($redirectUrl));
                        return $resultRedirect;
                    }
                } catch (EmailNotConfirmedException $e) {
                    $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                    $message = __(
                        'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
                        $value
                    );
                } catch (AuthenticationException $e) {
                    $message = __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    );
                } catch (LocalizedException $e) {
                    $message = $e->getMessage();
                } catch (\Exception $e) {
                    // PA DSS violation: throwing or logging an exception here can disclose customer password
                    $this->messageManager->addErrorMessage(
                        __('An unspecified error occurred. Please contact us for assistance.')
                    );
                } finally {
                    if (isset($message)) {
                        $this->messageManager->addErrorMessage($message);
                        $this->session->setUsername($login['username']);
                    }
                }
            } else {
                $this->messageManager->addErrorMessage(__('A login and a password are required.'));
            }
        }

        return $this->accountRedirect->getRedirect();
    }
}









//namespace Fastest\CustomerRegister\Controller\Account;
//
//use Magento\Customer\Model\Account\Redirect as AccountRedirect;
//use Magento\Framework\App\Action\Context;
//use Magento\Customer\Model\Session;
//use Magento\Customer\Api\AccountManagementInterface;
//use Magento\Customer\Model\Url as CustomerUrl;
//use Magento\Framework\Exception\EmailNotConfirmedException;
//use Magento\Framework\Exception\AuthenticationException;
//use Magento\Framework\Data\Form\FormKey\Validator;
//use Magento\Framework\Exception\LocalizedException;
//use Magento\Framework\Exception\State\UserLockedException;
//use Magento\Framework\App\Config\ScopeConfigInterface;
//
///**
// * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
// */
//class LoginPost extends \Magento\Customer\Controller\AbstractAccount
//{
//    /**
//     * @var \Magento\Customer\Api\AccountManagementInterface
//     */
//    protected $customerAccountManagement;
//
//    /**
//     * @var \Magento\Framework\Data\Form\FormKey\Validator
//     */
//    protected $formKeyValidator;
//
//    /**
//     * @var AccountRedirect
//     */
//    protected $accountRedirect;
//
//    /**
//     * @var Session
//     */
//    protected $session;
//
//    /**
//     * @var ScopeConfigInterface
//     */
//    private $scopeConfig;
//
//    /**
//     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
//     */
//    private $cookieMetadataFactory;
//
//    /**
//     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
//     */
//    private $cookieMetadataManager;
//    /**
//     * @var \Fastest\CustomerRegister\Model\LoginByTelephone
//     */
//    protected $loginByTelephone;
//
//    /**
//     * @param Context $context
//     * @param Session $customerSession
//     * @param AccountManagementInterface $customerAccountManagement
//     * @param CustomerUrl $customerHelperData
//     * @param Validator $formKeyValidator
//     * @param AccountRedirect $accountRedirect
//     */
//    public function __construct(
//        Context $context,
//        Session $customerSession,
//        AccountManagementInterface $customerAccountManagement,
//        CustomerUrl $customerHelperData,
//        Validator $formKeyValidator,
//        AccountRedirect $accountRedirect,
//        \Fastest\CustomerRegister\Model\LoginByTelephone $loginByTelephone
//    ) {
//        $this->session = $customerSession;
//        $this->customerAccountManagement = $customerAccountManagement;
//        $this->customerUrl = $customerHelperData;
//        $this->formKeyValidator = $formKeyValidator;
//        $this->accountRedirect = $accountRedirect;
//        $this->loginByTelephone = $loginByTelephone;
//        parent::__construct($context);
//
//    }
//
//    /**
//     * Get scope config
//     *
//     * @return ScopeConfigInterface
//     * @deprecated 100.0.10
//     */
//    private function getScopeConfig()
//    {
//        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
//            return \Magento\Framework\App\ObjectManager::getInstance()->get(
//                \Magento\Framework\App\Config\ScopeConfigInterface::class
//            );
//        } else {
//            return $this->scopeConfig;
//        }
//    }
//
//    /**
//     * Retrieve cookie manager
//     *
//     * @deprecated 100.1.0
//     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
//     */
//    private function getCookieManager()
//    {
//        if (!$this->cookieMetadataManager) {
//            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
//                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
//            );
//        }
//        return $this->cookieMetadataManager;
//    }
//
//    /**
//     * Retrieve cookie metadata factory
//     *
//     * @deprecated 100.1.0
//     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
//     */
//    private function getCookieMetadataFactory()
//    {
//        if (!$this->cookieMetadataFactory) {
//            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
//                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
//            );
//        }
//        return $this->cookieMetadataFactory;
//    }
//
//    /**
//     * Login post action
//     *
//     * @return \Magento\Framework\Controller\Result\Redirect
//     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
//     */
//    public function execute()
//    {
//        if ($this->session->isLoggedIn() || !$this->formKeyValidator->validate($this->getRequest())) {
//            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
//            $resultRedirect = $this->resultRedirectFactory->create();
//            $resultRedirect->setPath('*/*/');
//            return $resultRedirect;
//        }
//
//        if ($this->getRequest()->isPost()) {
//            $login = $this->getRequest()->getPost('login');
//            if (!empty($login['username']) && !empty($login['password'])) {
//                try {
//                    $telePhone = $login['username'];
//                    $emailId = $this->loginByTelephone->authenticateByTelephone($login['username'], $login['password']);
//                    // If email id does exits then throw error
//                    if(!$emailId)
//                    {
//                        $message = __(
//                            'Incorrect telephone number.'
//                        );
//                        $this->messageManager->addError($message);
//                        $this->session->setUsername($login['username']);
//                        return $this->accountRedirect->getRedirect();
//                    }
//                    $login['username'] =  $emailId;
//
//                    $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
//                    $this->session->setCustomerDataAsLoggedIn($customer);
//                    $this->session->regenerateId();
//                    if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
//                        $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
//                        $metadata->setPath('/');
//                        $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
//                    }
//                    $redirectUrl = $this->accountRedirect->getRedirectCookie();
//                    if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
//                        $this->accountRedirect->clearRedirectCookie();
//                        $resultRedirect = $this->resultRedirectFactory->create();
//                        // URL is checked to be internal in $this->_redirect->success()
//                        $resultRedirect->setUrl($this->_redirect->success($redirectUrl));
//                        return $resultRedirect;
//                    }
//                } catch (EmailNotConfirmedException $e) {
//                    $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
//                    $message = __('This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
//                        $value
//                    );
//                    $this->messageManager->addError($message);
//                    //$this->session->setUsername($login['username']);
//                    $this->session->setUsername($telePhone);
//                } catch (UserLockedException $e) {
//                    $message = __(
//                        'You did not sign in correctly or your account is temporarily disabled.'
//                    );
//                    $this->messageManager->addError($message);
//                    //$this->session->setUsername($login['username']);
//                    $this->session->setUsername($telePhone);
//                } catch (AuthenticationException $e) {
//                    $message = __('You did not sign in correctly or your account is temporarily disabled.');
//                    $this->messageManager->addError($message);
//                    $this->session->setUsername($login['username']);
//                } catch (LocalizedException $e) {
//                    $message = $e->getMessage();
//                    $this->messageManager->addError($message);
//                    //$this->session->setUsername($login['username']);
//                    $this->session->setUsername($telePhone);
//                } catch (\Exception $e) {
//                    // PA DSS violation: throwing or logging an exception here can disclose customer password
//                    $this->messageManager->addError(
//                        __('An unspecified error occurred. Please contact us for assistance.')
//                    );
//                }
//            } else {
//                $this->messageManager->addError(__('A login and a password are required.'));
//            }
//        }
//
//        return $this->accountRedirect->getRedirect();
//    }
//}
