<?php

/**
 * Created by Swarming Technology, LLC.
 * Project: Kount
 */

namespace Swarming\KountControl\Model;

class Customer2FA
{
    /**
     * @var \Swarming\Kount\Model\Config\Account
     */
    private $kountConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Swarming\KountControl\Model\ControlApi\Event
     */
    private $eventService;

    /**
     * @var \Swarming\KountControl\Model\ControlApi\TrustedDevice
     */
    private $trustedDeviceService;

    /**
     * @var \Swarming\KountControl\Helper\Config
     */
    private $kountControlConfig;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Swarming\Kount\Model\Config\Account $kountConfig
     * @param \Swarming\KountControl\Model\ControlApi\Event $eventService
     * @param \Swarming\KountControl\Model\ControlApi\TrustedDevice $trustedDeviceService
     * @param \Swarming\KountControl\Helper\Config $kountControlConfig
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Swarming\Kount\Model\Config\Account $kountConfig,
        \Swarming\KountControl\Model\ControlApi\Event $eventService,
        \Swarming\KountControl\Model\ControlApi\TrustedDevice $trustedDeviceService,
        \Swarming\KountControl\Helper\Config $kountControlConfig
    ) {
        $this->customerSession = $customerSession;
        $this->kountConfig = $kountConfig;
        $this->eventService = $eventService;
        $this->trustedDeviceService = $trustedDeviceService;
        $this->kountControlConfig = $kountControlConfig;
    }

    /**
     * Goes through KountControl diagram and initiate API requests to KountControl
     *
     * @param $sessionId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Swarming\KountControl\Exception\ConfigException
     * @throws \Swarming\KountControl\Exception\NegativeApiResponse
     * @throws \Swarming\KountControl\Exception\ParamsException
     * @throws \Swarming\KountControl\Exception\PositiveApiResponse
     */
    public function twoFactorAuthenticate($result)
    {
        if (!$this->kountControlConfig->isTrustedDeviceEnabled()) {
            throw new \Swarming\KountControl\Exception\ConfigException(__('KountControl: Login service disable'));
        }

        $userId = $this->customerSession->getCustomerId();
        $clientId = $this->kountConfig->getMerchantNumber();
        $sessionId = $this->customerSession->getKountSessionId();
        if ($sessionId === '' || $userId === '' || $this->kountConfig->getMerchantNumber() === '') {
            throw new \Swarming\KountControl\Exception\ParamsException(__('KountControl: lost POST params. '
                . '$sessionId = "%1"; $userId = "%2"; $clientId', $sessionId, $userId, $clientId));
        }

        $loginResult = $this->customerSession->getLoginResult();
        $this->eventService->setLoginResult($loginResult);
        $this->trustedDeviceService->setDeviceId($loginResult['deviceId']);
        if ($result === 1) {
            $this->eventService->successApiCall($sessionId, $clientId);
            $this->trustedDeviceService->trustedApiCall($sessionId, $clientId);
            throw new \Swarming\KountControl\Exception\PositiveApiResponse(__(
                'KountControl: Kount2FA decision is TRUSTED'
            ));
        } else {
            $this->eventService->failedApiCall($sessionId, $clientId);
            $this->trustedDeviceService->bannedApiCall($sessionId, $clientId);
            throw new \Swarming\KountControl\Exception\NegativeApiResponse(__(
                'KountControl: Kount2FA decision is BANNED'
            ));
        }
    }
}
