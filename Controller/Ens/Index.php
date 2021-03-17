<?php
/**
 * Copyright (c) 2021 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Ris\Controller\Ens;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;

class Index extends Action implements \Magento\Framework\App\CsrfAwareActionInterface
{
    /**
     * @var \Kount\Ris\Model\Config\Account
     */
    protected $configAccount;

    /**
     * @var \Kount\Ris\Model\Config\Ens
     */
    protected $configEns;

    /**
     * @var \Kount\Ris\Model\Ens\Manager
     */
    protected $ensManager;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var \Kount\Ris\Model\Logger
     */
    protected $logger;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Kount\Ris\Model\Config\Account $configAccount
     * @param \Kount\Ris\Model\Config\Ens $configEns
     * @param \Kount\Ris\Model\Ens\Manager $ensManager
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Kount\Ris\Model\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Kount\Ris\Model\Config\Account $configAccount,
        \Kount\Ris\Model\Config\Ens $configEns,
        \Kount\Ris\Model\Ens\Manager $ensManager,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Kount\Ris\Model\Logger $logger
    ) {
        $this->configAccount = $configAccount;
        $this->configEns = $configEns;
        $this->ensManager = $ensManager;
        $this->remoteAddress = $remoteAddress;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        try {
            if (!$this->isEnabled()) {
                throw new LocalizedException(__('ENS is not enabled.'));
            }

            if (!$this->isAllowed()) {
                throw new AuthenticationException(
                    __('Invalid ENS Ip Address: ' . $this->remoteAddress->getRemoteAddress() . '. Please ensure you whitelist this ip address in the Magento Kount configuration settings')
                );
            }

            $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
<events merchant="900410" total="2">
  <event>
    <name>WORKFLOW_STATUS_EDIT</name>
    <key site="JON" order_number="DEV000000028">DYKJ0KPH8Q4N</key>
    <old_value>R</old_value>
    <new_value>A</new_value>
    <agent>ian@swarmingtech.com</agent>
    <occurred>2021-03-16 12:11:21</occurred>
  </event>
</events>';
            $this->respondOnReceiptOfEvents();
            $response = $this->ensManager->handleRequest($xmlString);
            $this->logger->info($response);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->logger->critical($e);
            $response = $this->ensManager->generateResponse(0, 1);
        }

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $resultRaw->setHeader('Content-Type', 'text/xml');
        $resultRaw->setContents($response);
        return $resultRaw;
    }

    /**
     * @return bool
     */
    protected function isEnabled()
    {
        return $this->configEns->isEnabled();
    }

    /**
     * @return bool
     */
    protected function isAllowed()
    {
        return $this->configAccount->isTestMode()
            ||
            $this->configEns->isAllowedIp($this->remoteAddress->getRemoteAddress());
    }

    /**
     * Create response upon receipt of request instead of after processing.  The initial request can be added to a queue
     * and processed via cron at a later date, for now, we will just respond upon receipt and keep processing going now.
     * For now, we will send response now and and keep session alive to process data.
     *
     * @return void
     */
    protected function respondOnReceiptOfEvents()
    {
        ob_start();
        echo "200 OK";
        $size = ob_get_length();
        header("Content-Encoding: none");
        header("Content-Length: {$size}");
        header("Connection: close");
        ob_end_flush();
        ob_flush();
        flush();
        if (session_id()) {
            session_write_close();
        }
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
