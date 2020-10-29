<?php

/**
 * Created by Swarming Technology, LLC.
 * Project: Kount
 */

namespace Swarming\KountControl\Plugin\Controller\Account;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;

class LoginPost
{
    /**
     * @var \Swarming\KountControl\Model\CustomerLogin
     */
    private $customerLogin;

    /**
     * @var \Swarming\Kount\Model\Logger
     */
    private $logger;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    private $httpResponse;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @param \Swarming\KountControl\Model\CustomerLogin $customerLogin
     * @param \Swarming\Kount\Model\Logger $logger
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Response\Http $httpResponse
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Swarming\KountControl\Model\CustomerLogin $customerLogin,
        \Swarming\Kount\Model\Logger $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Response\Http $httpResponse,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->customerLogin = $customerLogin;
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->httpResponse = $httpResponse;
        $this->url = $url;
    }

    /**
     * Initiates API request to Kount system about customer log in. Logs out customer if API response is negative.
     *
     * @param HttpPostActionInterface $httpPostAction
     * @param Redirect $result
     * @return Redirect
     */
    public function afterExecute(HttpPostActionInterface $httpPostAction, Redirect $result)
    {
        $sessionId = '';
        $isSuccessful = true;
        if (isset($httpPostAction->getRequest()->getParams()['kountsessionid'])) {
            $sessionId = $httpPostAction->getRequest()->getParams()['kountsessionid'];
        }
        try {
            $this->customerLogin->login($sessionId);
        } catch (
            \Swarming\KountControl\Exception\ConfigException
            | \Swarming\KountControl\Exception\PositiveApiResponse $e
        ) {
            $this->logger->info($e->getMessage());
        } catch (
            \Swarming\KountControl\Exception\ParamsException
            | \Swarming\KountControl\Exception\NegativeApiResponse $e
        ) {
            $isSuccessful = false;
            $this->logoutCustomer();
            $this->logger->warning($e->getMessage());
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->error(__('KountControl: ' . $e->getMessage()));
        }

        if (!$isSuccessful) {
            return $this->httpResponse->setRedirect($this->url->getUrl('customer/account/login'));
        } else {
            return $result;
        }
    }

    /**
     * @return void
     */
    private function logoutCustomer()
    {
        if ($this->customerSession->getCustomer()->getId()) {
            $this->customerSession->destroy();
        }
    }
}
