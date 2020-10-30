<?php

/**
 * Created by Swarming Technology, LLC.
 * Project: Kount
 */

namespace Swarming\KountControl\Plugin\Controller\Account;

use Magento\Framework\App\Action\HttpPostActionInterface;

class CreatePost
{
    /**
     * @var \Swarming\KountControl\Model\RisCustomerRegistration
     */
    private $risCustomerRegistration;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    private $httpResponse;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @var \Swarming\Kount\Model\Logger
     */
    private $logger;

    /**
     * @var \Swarming\KountControl\Helper\Config
     */
    private $kountControlConfig;

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\Response\Http $httpResponse
     * @param \Magento\Framework\UrlInterface $url
     * @param \Swarming\KountControl\Model\RisCustomerRegistration $risCustomerRegistration
     * @param \Swarming\Kount\Model\Logger $logger
     * @param \Swarming\KountControl\Helper\Config $kountControlConfig
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\Http $httpResponse,
        \Magento\Framework\UrlInterface $url,
        \Swarming\KountControl\Model\RisCustomerRegistration $risCustomerRegistration,
        \Swarming\Kount\Model\Logger $logger,
        \Swarming\KountControl\Helper\Config $kountControlConfig
    ) {
        $this->messageManager = $messageManager;
        $this->httpResponse = $httpResponse;
        $this->url = $url;
        $this->risCustomerRegistration = $risCustomerRegistration;
        $this->logger = $logger;
        $this->kountControlConfig = $kountControlConfig;
    }

    /**
     * Interrupt creating customer process and call RIS API
     *
     * @param HttpPostActionInterface $httpPostAction
     * @param \Closure $proceed
     * @return \Magento\Framework\App\Response\Http|\Magento\Framework\App\Response\HttpInterface|mixed
     */
    public function aroundExecute(HttpPostActionInterface $httpPostAction, \Closure $proceed)
    {
        if (!$this->kountControlConfig->isSignupEnabled()) {
            return $proceed();
        } else {
            return $this->sendRisResquest($httpPostAction, $proceed);
        }
    }

    /**
     * If RIS API request is fail, redirect to customer registration page
     *
     * @param HttpPostActionInterface $httpPostAction
     * @param \Closure $proceed
     * @return \Magento\Framework\App\Response\Http|\Magento\Framework\App\Response\HttpInterface|mixed
     */
    private function sendRisResquest(HttpPostActionInterface $httpPostAction, \Closure $proceed)
    {
        $customerEmail = $httpPostAction->getRequest()->getParams()['email'];
        $isSuccessful = true;
        try {
            $this->risCustomerRegistration->registrationRequest($customerEmail);
        } catch (\Swarming\KountControl\Exception\NegativeApiResponse $e) {
            $isSuccessful = false;
            $this->logger->warning($e->getMessage());
        } catch (\Swarming\KountControl\Exception\PositiveApiResponse $e) {
            $this->logger->info($e->getMessage());
        } catch (
            \Kount_Ris_IllegalArgumentException
            | \Magento\Framework\Exception\NoSuchEntityException $e
        ) {
            $this->logger->error(__('KountControl: RIS registration: ' . $e->getMessage()));
        }

        if (!$isSuccessful) {
            $this->messageManager->addErrorMessage(__("Registration failed. Please try again!"));
            return $this->httpResponse->setRedirect($this->url->getUrl('customer/account/create'));
        } else {
            return $proceed();
        }
    }
}
