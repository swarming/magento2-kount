<?php
/**
 * Copyright (c) 2021 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Ris\Model\Ris\Inquiry;

use Magento\Sales\Model\Order;
use Kount\Ris\Model\RisService;

class Builder
{
    /**
     * @var \Kount\Ris\Model\Ris\InquiryFactory
     */
    protected $inquiryFactory;

    /**
     * @var \Kount\Ris\Model\Config\Account
     */
    protected $configAccount;

    /**
     * @var \Kount\Ris\Model\Ris\Inquiry\Builder\VersionInfo
     */
    protected $versionBuilder;

    /**
     * @var \Kount\Ris\Model\Ris\Base\Builder\Session
     */
    protected $sessionBuilder;

    /**
     * @var \Kount\Ris\Model\Ris\Inquiry\Builder\Order
     */
    protected $orderBuilder;

    /**
     * @var \Kount\Ris\Model\Ris\Base\Builder\PaymentInterface
     */
    protected $paymentBuilder;

    /**
     * @param \Kount\Ris\Model\Ris\InquiryFactory $inquiryFactory
     * @param \Kount\Ris\Model\Config\Account $configAccount
     * @param \Kount\Ris\Model\Ris\Inquiry\Builder\VersionInfo $versionBuilder
     * @param \Kount\Ris\Model\Ris\Base\Builder\Session $sessionBuilder
     * @param \Kount\Ris\Model\Ris\Inquiry\Builder\Order $orderBuilder
     * @param \Kount\Ris\Model\Ris\Base\Builder\PaymentInterface $paymentBuilder
     */
    public function __construct(
        \Kount\Ris\Model\Ris\InquiryFactory $inquiryFactory,
        \Kount\Ris\Model\Config\Account $configAccount,
        \Kount\Ris\Model\Ris\Inquiry\Builder\VersionInfo $versionBuilder,
        \Kount\Ris\Model\Ris\Base\Builder\Session $sessionBuilder,
        \Kount\Ris\Model\Ris\Inquiry\Builder\Order $orderBuilder,
        \Kount\Ris\Model\Ris\Base\Builder\PaymentInterface $paymentBuilder
    ) {
        $this->inquiryFactory = $inquiryFactory;
        $this->configAccount = $configAccount;
        $this->versionBuilder = $versionBuilder;
        $this->sessionBuilder = $sessionBuilder;
        $this->orderBuilder = $orderBuilder;
        $this->paymentBuilder = $paymentBuilder;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param string $auth
     * @param string $mack
     * @return \Kount_Ris_Request_Inquiry
     */
    public function build(Order $order, $auth = RisService::AUTH_AUTHORIZED, $mack = RisService::MACK_YES)
    {
        $inquiry = $this->inquiryFactory->create($order->getStore()->getWebsiteId());

        $inquiry->setWebsite($this->configAccount->getWebsite($order->getStore()->getWebsiteId()));
        $inquiry->setAuth($auth);
        $inquiry->setMack($mack);

        $this->versionBuilder->process($inquiry);
        $this->sessionBuilder->process($inquiry);
        $this->orderBuilder->process($inquiry, $order);
        $this->paymentBuilder->process($inquiry, $order->getPayment());

        return $inquiry;
    }
}
