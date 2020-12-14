<?php

/**
 * Created by Swarming Technology, LLC.
 * Project: Kount
 */

namespace Swarming\Kount2FA\Model\ResourceModel\Secret;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'secret_id';

    protected function _construct()
    {
        $this->_init('Swarming\Kount2FA\Model\Secret', 'Swarming\Kount2FA\Model\ResourceModel\Secret');
    }
}
