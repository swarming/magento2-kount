<?php

/**
 * Created by Swarming Technology, LLC.
 * Project: Kount
 */

namespace Swarming\Kount2FA\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Secret extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('swarming_kount2fa_secrets', 'secret_id');
    }
}
