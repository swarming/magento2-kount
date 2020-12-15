<?php

/**
 * Created by Swarming Technology, LLC.
 * Project: Kount
 */

namespace Swarming\Kount2FA\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Swarming\Kount2FA\Model\ResourceModel\Secret as SecretResourceModel;
use Swarming\Kount2FA\Model\ResourceModel\Secret\Collection as SecretCollection;

/**
 * @method SecretResourceModel getResource()
 * @method SecretCollection getCollection()
 */
class Secret extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'swarming_kount2fa_secret';
    protected $_cacheTag = 'swarming_kount2fa_secret';
    protected $_eventPrefix = 'swarming_kount2fa_secret';

    protected function _construct()
    {
        $this->_init('Swarming\Kount2FA\Model\ResourceModel\Secret');
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
