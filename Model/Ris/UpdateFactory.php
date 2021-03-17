<?php
/**
 * Copyright (c) 2021 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Ris\Model\Ris;

class UpdateFactory
{
    /**
     * @var \Kount\Ris\Model\Lib\Settings
     */
    protected $libSettings;

    /**
     * @param \Kount\Ris\Model\Lib\Settings $libSettings
     */
    public function __construct(
        \Kount\Ris\Model\Lib\Settings $libSettings
    ) {
        $this->libSettings = $libSettings;
    }

    /**
     * @param string|null $websiteCode
     * @return \Kount_Ris_Request_Update
     */
    public function create($websiteCode = null)
    {
        $settings = new \Kount_Ris_ArraySettings($this->libSettings->getSettings($websiteCode));
        return new \Kount_Ris_Request_Update($settings);
    }
}
