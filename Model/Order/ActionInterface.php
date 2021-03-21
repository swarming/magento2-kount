<?php
/**
 * Copyright (c) 2021 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Ris\Model\Order;

interface ActionInterface
{
    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function process($order);
}
