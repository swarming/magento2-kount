<?php
/**
 * Copyright (c) 2021 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Ris\Model\Order\Action;

use Magento\Sales\Model\Order;
use Kount\Ris\Model\Order\ActionInterface;
use Kount\Ris\Model\Order\Ris as OrderRis;

class Review implements ActionInterface
{
    /**
     * @var \Kount\Ris\Model\Logger
     */
    protected $logger;

    /**
     * @param \Kount\Ris\Model\Logger $logger
     */
    public function __construct(
        \Kount\Ris\Model\Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function process($order)
    {
        $this->logger->info('Setting order to Kount Review status/state');

        $order->setHoldBeforeState($order->getState());
        $order->setHoldBeforeStatus($order->getStatus());

        $order->setState(Order::STATE_HOLDED);
        $order->addStatusToHistory(OrderRis::STATUS_KOUNT_REVIEW, __('Order on review from Kount.'), false);
    }
}
