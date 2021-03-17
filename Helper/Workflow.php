<?php
/**
 * Copyright (c) 2021 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Ris\Helper;

use Magento\Framework\App\Area;

class Workflow
{
    /**
     * @var \Kount\Ris\Model\Config\Account
     */
    protected $configAccount;

    /**
     * @var \Kount\Ris\Model\Config\PaymentMethods
     */
    protected $configPaymentMethods;

    /**
     * @var \Kount\Ris\Model\Config\Admin
     */
    protected $configAdmin;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Kount\Ris\Model\Logger
     */
    protected $logger;

    /**
     * @param \Kount\Ris\Model\Config\Account $configAccount
     * @param \Kount\Ris\Model\Config\PaymentMethods $configPaymentMethods
     * @param \Kount\Ris\Model\Config\Admin $configAdmin
     * @param \Magento\Framework\App\State $appState
     * @param \Kount\Ris\Model\Logger $logger
     */
    public function __construct(
        \Kount\Ris\Model\Config\Account $configAccount,
        \Kount\Ris\Model\Config\PaymentMethods $configPaymentMethods,
        \Kount\Ris\Model\Config\Admin $configAdmin,
        \Magento\Framework\App\State $appState,
        \Kount\Ris\Model\Logger $logger
    ) {
        $this->configAccount = $configAccount;
        $this->configPaymentMethods = $configPaymentMethods;
        $this->configAdmin = $configAdmin;
        $this->appState = $appState;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function isProcessable($order)
    {
        $paymentMethodCode = $order->getPayment()->getMethod();
        $websiteId = $order->getStore()->getWebsiteId();

        if (!$this->configAccount->isAvailable($websiteId)) {
            $this->logger->info('Kount extension is disabled or not configured.');
            return false;
        }

        if ($paymentMethodCode && in_array($paymentMethodCode, $this->configPaymentMethods->getDisableMethods($websiteId), true)) {
            $this->logger->info('Kount disabled for payment method: ' . $paymentMethodCode);
            return false;
        }

        if ($this->isBackendArea($order) && !$this->configAdmin->isEnabled($websiteId)) {
            $this->logger->info('Kount disabled for Admin panel order.');
            return false;
        }

        return true;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function isBackendArea($order)
    {
        return $this->appState->getAreaCode() === Area::AREA_ADMINHTML || empty($order->getRemoteIp());
    }
}
