<?php

/**
 * Created by Swarming Technology, LLC.
 * Project: Kount
 */

namespace Swarming\KountControl\Model\ControlApi;

use Swarming\KountControl\Api\ServiceInterface;

class Event extends AbstractService implements ServiceInterface
{
    const ENDPOINT_URI = '/events';
    const CHALLENGE_DECISION = "Challenge";

    /**
     * @var array
     */
    private $loginResult;

    /**
     * @inheritdoc
     */
    public function preparePayload($sessionId, $clientId)
    {
        $userId = $this->customerSession->getCustomerId();
        // As for now in case "Challenge" decision it always send Success result to Event API
        if ($this->getLoginResult()['decision'] === self::CHALLENGE_DECISION) {
            $payload = $this->getSuccessEventPayload($sessionId, $userId, $clientId, $this->getLoginResult());
        } else {
            $payload = $this->getFailedEventPayload($sessionId, $userId, $clientId);
        }

        return $payload;
    }

    /**
     * @param $sessionId
     * @param $userId
     * @param $clientId
     * @param $loginResult
     * @return array
     */
    private function getSuccessEventPayload($sessionId, $userId, $clientId, $loginResult)
    {
        $loginDecisionCorrelationId = isset($loginResult['loginDecisionCorrelationId'])
            ? $loginResult['loginDecisionCorrelationId']
            : '';

        return [
            'body' => json_encode(
                [
                    'challengeOutcome' => [
                        'clientId' => $clientId,
                        'loginDecisionCorrelationId' => $loginDecisionCorrelationId,
                        'challengeType' => 'Captcha',
                        'challengeStatus' => \Swarming\KountControl\Model\CustomerLogin::SUCCESS_CHALLENGE,
                        'sessionId' => $sessionId,
                        'userId' => $userId,
                        'sentTimestamp' => '',
                        'completedTimestamp' => '',
                        'failureType' => 'TimedOut'
                    ]
                ]
            )
        ];
    }

    /**
     * @param $sessionId
     * @param $userId
     * @param $clientId
     * @return array
     */
    private function getFailedEventPayload($sessionId, $userId, $clientId)
    {
        return [
            'body' => json_encode(
                [
                    'failedAttempt' => [
                        'clientId' => $clientId,
                        'sessionId' => $sessionId,
                        'userId' => $userId,
                        'username' => $this->customerSession->getCustomer()->getFirstname()
                            . ' ' . $this->customerSession->getCustomer()->getLastname(),
                        'userPassword' => $this->customerSession->getCustomer()->getPasswordHash(),
                        'userIp' => $this->remoteAddress->getRemoteAddress(),
                        'loginUrl' => $this->customerUrl->getLoginUrl()
                    ]
                ]
            )
        ];
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return self::ENDPOINT_URI;
    }

    /**
     * @return array
     */
    public function getLoginResult()
    {
        return $this->loginResult;
    }

    /**
     * @param $loginResult
     */
    public function setLoginResult($loginResult)
    {
        $this->loginResult = $loginResult;
    }
}
