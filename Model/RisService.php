<?php
/**
 * Copyright (c) 2021 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Ris\Model;

use Magento\Sales\Model\Order;
use Magento\Framework\Exception\LocalizedException;

class RisService
{
    const AUTO_DECLINE = 'D';
    const AUTO_REVIEW = 'R';
    const AUTO_ESCALATE = 'E';
    const AUTO_APPROVE = 'A';

    const AUTH_AUTHORIZED = 'A';
    const AUTH_DECLINED = 'D';

    const MACK_YES = 'Y';
    const MACK_NO = 'N';

    /**
     * @var \Kount\Ris\Model\Ris\Inquiry\Builder
     */
    protected $inquiryBuilder;

    /**
     * @var \Kount\Ris\Model\Ris\Update\Builder
     */
    protected $updateBuilder;

    /**
     * @var \Kount\Ris\Model\Ris\Sender
     */
    protected $requestSender;

    /**
     * @var \Kount\Ris\Model\Order\Ris
     */
    protected $orderRis;

    /**
     * @var \Kount\Ris\Model\Lib\LoggerFactory
     */
    protected $loggerFactory;

    /**
     * @var \Kount\Ris\Model\Logger
     */
    protected $logger;

    /**
     * @param \Kount\Ris\Model\Ris\Inquiry\Builder $inquiryBuilder
     * @param \Kount\Ris\Model\Ris\Update\Builder $updateBuilder
     * @param \Kount\Ris\Model\Ris\Sender $requestSender
     * @param \Kount\Ris\Model\Order\Ris $orderRis
     * @param \Kount\Ris\Model\Lib\LoggerFactory $loggerFactory
     * @param \Kount\Ris\Model\Logger $logger
     */
    public function __construct(
        \Kount\Ris\Model\Ris\Inquiry\Builder $inquiryBuilder,
        \Kount\Ris\Model\Ris\Update\Builder $updateBuilder,
        \Kount\Ris\Model\Ris\Sender $requestSender,
        \Kount\Ris\Model\Order\Ris $orderRis,
        \Kount\Ris\Model\Lib\LoggerFactory $loggerFactory,
        \Kount\Ris\Model\Logger $logger
    ) {
        $this->inquiryBuilder = $inquiryBuilder;
        $this->updateBuilder = $updateBuilder;
        $this->requestSender = $requestSender;
        $this->orderRis = $orderRis;
        $this->loggerFactory = $loggerFactory;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public static function getAutos()
    {
        return [self::AUTO_APPROVE, self::AUTO_REVIEW, self::AUTO_ESCALATE, self::AUTO_DECLINE];
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param bool $graceful
     * @param string $auth
     * @param string $mack
     * @return bool
     * @throws LocalizedException
     */
    public function inquiryRequest(Order $order, $graceful = true, $auth = RisService::AUTH_AUTHORIZED, $mack = RisService::MACK_YES)
    {
        $ris = $this->orderRis->getRis($order);
        if (!empty($ris->getResponse())) {
            $this->logger->info('Skipp second time inquiry request.'); /* Authorize.net calls payment place twice */
            return false;
        }

        \Kount_Log_Factory_LogFactory::setLoggerFactory($this->loggerFactory->setWebsiteId($order->getStore()->getWebsiteId()));
        $inquiryRequest = $this->inquiryBuilder->build($order, $auth, $mack);
        $inquiryResponse = $this->requestSender->send($inquiryRequest);

        if (!$inquiryResponse instanceof \Kount_Ris_Response) {
            $this->logger->warning('Wrong response, skipping Update.');
            return false;
        }

        if (!$inquiryResponse->getTransactionId()) {
            $this->logger->warning('No transaction_id in response, skipping Update.');
            return false;
        }

        if (!$graceful && $this->parseResponse($inquiryResponse) === RisService::AUTO_DECLINE) {
            throw new LocalizedException(__('Payment authorization rejection from the processor.'));
        }

        $this->orderRis->updateRis($order, $inquiryResponse);
        return true;
    }

    /**
     * @param \Kount_Ris_Response $response
     * @return string|bool
     */
    protected function parseResponse(\Kount_Ris_Response $response)
    {
        if ($response->getErrorCount() != 0) {
            $this->logger->warning('Continuing to process order without Kount.');
            return false;
        }
        return $response->getAuto();
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param bool $processorAuthorized
     * @return bool
     */
    public function updateRequest(Order $order, $processorAuthorized)
    {
        $ris = $this->orderRis->getRis($order);
        if (empty($ris->getTransactionId())) {
            $this->logger->warning('No ris transaction_id in order, skipping Update.');
            return false;
        }

        \Kount_Log_Factory_LogFactory::setLoggerFactory($this->loggerFactory->setWebsiteId($order->getStore()->getWebsiteId()));
        $updateRequest = $this->updateBuilder->build($order, $ris->getTransactionId(), $processorAuthorized);
        $this->requestSender->send($updateRequest);

        return true;
    }
}
