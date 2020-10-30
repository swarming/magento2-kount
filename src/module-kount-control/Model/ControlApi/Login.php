<?php

/**
 * Created by Swarming Technology, LLC.
 * Project: Kount
 */

namespace Swarming\KountControl\Model\ControlApi;

use Swarming\KountControl\Api\ServiceInterface;

class Login extends AbstractService implements ServiceInterface
{
    const ENDPOINT_URI = '/login';
    const ALLOW_DECISION = "Allow";

    /**
     * @inheritdoc
     */
    public function executeApiRequest($sessionId, $clientId)
    {
        $result = parent::executeApiRequest($sessionId, $clientId);
        if (!isset($result['decision'])) {
            throw new \Swarming\KountControl\Exception\NegativeApiResponse(__(
                'KountControl: API Login decision is unknown'
            ));
        }

        if ($result['decision'] === self::ALLOW_DECISION) {
            throw new \Swarming\KountControl\Exception\PositiveApiResponse(__(
                'KountControl: API Login decision is "%1"',
                $result['decision']
            ));
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getEndpoint()
    {
        return self::ENDPOINT_URI;
    }

    /**
     * @inheritdoc
     */
    public function preparePayload($sessionId, $clientId)
    {
        $userId = $this->customerSession->getCustomerId();
        return [
            'body' => json_encode(
                [
                    'clientId' => $clientId,
                    'sessionId' => $sessionId,
                    'userId' => $userId,
                    'loginUrl' => $this->customerUrl->getLoginUrl(),
                    'userAuthenticationStatus' => 'true',
                    'username' => $this->customerSession->getCustomer()->getFirstname()
                        . ' ' . $this->customerSession->getCustomer()->getLastname(),
                    'userPassword' => $this->customerSession->getCustomer()->getPasswordHash(),
                    'userIp' => $this->remoteAddress->getRemoteAddress(),
                ]
            )
        ];
    }
}
