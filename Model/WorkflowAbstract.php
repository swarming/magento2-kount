<?php
/**
 * Copyright (c) 2021 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Ris\Model;

use Kount\Ris\Model\Order\ActionFactory as OrderActionFactory;

abstract class WorkflowAbstract implements WorkflowInterface
{
    /**
     * @var \Kount\Ris\Model\Config\Workflow
     */
    protected $configWorkflow;

    /**
     * @var \Kount\Ris\Model\RisService
     */
    protected $risService;

    /**
     * @var \Kount\Ris\Model\Order\ActionFactory
     */
    protected $orderActionFactory;

    /**
     * @var \Kount\Ris\Model\Order\Ris
     */
    protected $orderRis;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Kount\Ris\Model\Logger
     */
    protected $logger;

    /**
     * @param \Kount\Ris\Model\Config\Workflow $configWorkflow
     * @param \Kount\Ris\Model\RisService $risService
     * @param \Kount\Ris\Model\Order\ActionFactory $orderActionFactory
     * @param \Kount\Ris\Model\Order\Ris $orderRis
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Kount\Ris\Model\Logger $logger
     */
    public function __construct(
        \Kount\Ris\Model\Config\Workflow $configWorkflow,
        \Kount\Ris\Model\RisService $risService,
        \Kount\Ris\Model\Order\ActionFactory $orderActionFactory,
        \Kount\Ris\Model\Order\Ris $orderRis,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Kount\Ris\Model\Logger $logger
    ) {
        $this->configWorkflow = $configWorkflow;
        $this->risService = $risService;
        $this->orderActionFactory = $orderActionFactory;
        $this->orderRis = $orderRis;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    protected function updaterOrderStatus($order)
    {
        $kountRisResponse = $this->orderRis->getRis($order)->getResponse();
        switch ($kountRisResponse) {
            case RisService::AUTO_DECLINE:
                $this->orderActionFactory->create(OrderActionFactory::DECLINE)->process($order);
                break;
            case RisService::AUTO_REVIEW:
            case RisService::AUTO_ESCALATE:
                $this->orderActionFactory->create(OrderActionFactory::REVIEW)->process($order);
                break;
        }

        $this->orderRepository->save($order);
    }
}
