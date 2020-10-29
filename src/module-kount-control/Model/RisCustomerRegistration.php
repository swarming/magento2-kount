<?php

/**
 * Created by Swarming Technology, LLC.
 * Project: Kount
 */

namespace Swarming\KountControl\Model;

use Swarming\Kount\Model\RisService;

class RisCustomerRegistration
{
    const AUTO_DECLINE = 'D';
    const AUTO_REVIEW = 'R';
    const AUTO_APPROVE = 'A';
    const MACK_NO = 'N';
    const BAD_SITE_CODE = '323';

    /**
     * @var \Swarming\Kount\Model\Ris\Inquiry\Builder
     */
    private $inquiryBuilder;

    /**
     * @var \Swarming\Kount\Model\Ris\Sender
     */
    private $requestSender;

    /**
     * @var \Swarming\Kount\Model\Order\Ris
     */
    private $orderRis;

    /**
     * @var \Swarming\Kount\Model\Logger
     */
    private $logger;

    /**
     * @param Ris\Inquiry\Builder\Customer\Registration $inquiryBuilder
     * @param \Swarming\Kount\Model\Ris\Sender $requestSender
     * @param \Swarming\Kount\Model\Order\Ris $orderRis
     * @param \Swarming\Kount\Model\Logger $logger
     */
    public function __construct(
        \Swarming\KountControl\Model\Ris\Inquiry\Builder\Customer\Registration $inquiryBuilder,
        \Swarming\Kount\Model\Ris\Sender $requestSender,
        \Swarming\Kount\Model\Order\Ris $orderRis,
        \Swarming\Kount\Model\Logger $logger
    ) {
        $this->inquiryBuilder = $inquiryBuilder;
        $this->requestSender = $requestSender;
        $this->orderRis = $orderRis;
        $this->logger = $logger;
    }

    /**
     * Сonsiders the response
     *
     * @param $customerEmail
     * @param bool $graceful
     * @param string $auth
     * @param string $mack
     * @return void
     * @throws \Kount_Ris_IllegalArgumentException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Swarming\KountControl\Exception\NegativeApiResponse
     * @throws \Swarming\KountControl\Exception\PositiveApiResponse
     */
    public function registrationRequest(
        $customerEmail,
        $graceful = true,
        $auth = RisService::AUTH_AUTHORIZED,
        $mack = RisService::MACK_NO
    ) {
        $registrationRequest = $this->inquiryBuilder->build($customerEmail, $auth, $mack);
        $registrationResponse = $this->requestSender->send($registrationRequest);

        if (!$registrationResponse instanceof \Kount_Ris_Response) {
            throw new \Swarming\KountControl\Exception\NegativeApiResponse(__(
                'KountControl: RIS create new customer. Received response is not instance of Kount_Ris_Response'
            ));
        }

        // Checks whether auto-decision positive or negative
        if (
            $registrationResponse->getAuto()
            && ($registrationResponse->getAuto() === self::AUTO_APPROVE
                || $registrationResponse->getAuto() === self::AUTO_REVIEW)
        ) {
            throw new \Swarming\KountControl\Exception\PositiveApiResponse(__(
                'KountControl: RIS create new customer: Auto-decision response code is '
                . $registrationResponse->getAuto()
                . '; Merchant ID is ' . $registrationResponse->getMerchantId()
                . '; Session ID is ' . $registrationResponse->getSessionId()
                . '; Site is ' . $registrationResponse->getSite()
            ));
        } elseif (
            $registrationResponse->getAuto()
            && $registrationResponse->getAuto() === self::AUTO_DECLINE
        ) {
            throw new \Swarming\KountControl\Exception\NegativeApiResponse(__(
                'KountControl: RIS create new customer: Auto-decision response code is ' . self::AUTO_DECLINE
                . '; Merchant ID is ' . $registrationResponse->getMerchantId()
                . '; Session ID is ' . $registrationResponse->getSessionId()
                . '; Site is ' . $registrationResponse->getSite()
            ));
        }

        // Checks whether merchant site settings in Kount AWC portal has wrong site code and throw PositiveApiResponse
        // exception so that the customer can register
        if ($registrationResponse->getErrorCode() && $registrationResponse->getErrorCode() === self::BAD_SITE_CODE) {
            throw new \Swarming\KountControl\Exception\PositiveApiResponse(__(
                'KountControl: RIS create new customer: 323 BAD_SITE Cause: "%1" does not exist for merchant "%2"',
                $registrationRequest->getSite(),
                $registrationRequest->getSessionId()
            ));
        }
    }
}
