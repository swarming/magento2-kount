<?php
/**
 * Copyright (c) 2017 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Swarming\Kount\Model\Order\Action;

use Magento\Sales\Model\Order;
use Swarming\Kount\Model\Order\ActionInterface;
use Swarming\Kount\Model\Order\Ris as OrderRis;

class Review implements ActionInterface
{
    /**
     * @var \Swarming\Kount\Model\Logger
     */
    protected $logger;

    /**
     * @param \Swarming\Kount\Model\Logger $logger
     */
    public function __construct(
        \Swarming\Kount\Model\Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function process($order)
    {
        $orderState = $order->getState();
        $orderStatus = $order->getStatus();
        if ($orderState === Order::STATE_HOLDED && $orderStatus === OrderRis::STATUS_KOUNT_REVIEW) {
            $this->logger->info('Setting order to Kount Review status/state - already set, skipping');
            return;
        }

        $this->logger->info('Setting order to Kount Review status/state');

        $order->setHoldBeforeState($orderState);
        $order->setHoldBeforeStatus($orderStatus);

        $order->setState(Order::STATE_HOLDED);
        $order->addStatusToHistory(OrderRis::STATUS_KOUNT_REVIEW, __('Order on review from Kount.'), false);
    }
}
