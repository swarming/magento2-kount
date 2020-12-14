<?php

/**
 * Created by Swarming Technology, LLC.
 * Project: Kount
 */

namespace Swarming\KountControl\Model;

class CustomerLogin
{
    const SUCCESS_CHALLENGE = "Success";

    /**
     * @var \Swarming\Kount\Model\Config\Account
     */
    private $kountConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Swarming\KountControl\Model\ControlApi\Login
     */
    private $loginService;

    /**
     * @var \Swarming\KountControl\Model\ControlApi\Event
     */
    private $eventService;

    /**
     * @var \Swarming\KountControl\Helper\Config
     */
    private $kountControlConfig;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Swarming\Kount\Model\Config\Account $kountConfig
     * @param \Swarming\KountControl\Model\ControlApi\Login $loginService
     * @param \Swarming\KountControl\Model\ControlApi\Event $eventService
     * @param \Swarming\KountControl\Helper\Config $kountControlConfig
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Swarming\Kount\Model\Config\Account $kountConfig,
        \Swarming\KountControl\Model\ControlApi\Login $loginService,
        \Swarming\KountControl\Model\ControlApi\Event $eventService,
        \Swarming\KountControl\Helper\Config $kountControlConfig
    ) {
        $this->customerSession = $customerSession;
        $this->kountConfig = $kountConfig;
        $this->loginService = $loginService;
        $this->eventService = $eventService;
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
    public function login($sessionId)
    {
        if (!$this->kountControlConfig->isLoginServiceEnabled()) {
            throw new \Swarming\KountControl\Exception\ConfigException(__('KountControl: Login service disable'));
        }

        $userId = $this->customerSession->getCustomerId();
        $clientId = $this->kountConfig->getMerchantNumber();
        if ($sessionId === '' || $userId === '' || $this->kountConfig->getMerchantNumber() === '') {
            throw new \Swarming\KountControl\Exception\ParamsException(__('KountControl: lost POST params. '
                . '$sessionId = "%1"; $userId = "%2"; $clientId', $sessionId, $userId, $clientId));
        }

        $loginResult = $this->loginService->executeApiRequest($sessionId, $clientId);
        $this->customerSession->setLoginResult($loginResult);
        if ($loginResult['decision'] === \Swarming\KountControl\Model\ControlApi\Login::BLOCK_DECISION) {
            $this->eventService->setLoginResult($loginResult);
            $this->eventService->failedApiCall($sessionId, $clientId);
            throw new \Swarming\KountControl\Exception\NegativeApiResponse(__(
                'KountControl: API Login decision is "%1"',
                $loginResult['decision']
            ));
        }

        if ($loginResult['decision'] === \Swarming\KountControl\Model\ControlApi\Login::CHALLENGE_DECISION) {
            if (!isset($loginResult['deviceId'])) {
                throw new \Swarming\KountControl\Exception\ParamsException(__(
                    'KountControl: lost POST params. API login decision is "%1". $deviceId is not set.',
                    $loginResult['decision']
                ));
            }
            throw new \Swarming\KountControl\Exception\ChallengeApiResponse(__(
                'KountControl: API login decision is "%1".',
                $loginResult['decision']
            ));
        }
    }
}
