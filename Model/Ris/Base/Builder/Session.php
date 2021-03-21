<?php
/**
 * Copyright (c) 2021 KOUNT, INC.
 * See COPYING.txt for license details.
 */
namespace Kount\Ris\Model\Ris\Base\Builder;

class Session
{
    /**
     * @var \Kount\Ris\Model\Session
     */
    protected $kountSession;

    /**
     * Session constructor.
     * @param \Kount\Ris\Model\Session $kountSession
     */
    public function __construct(
        \Kount\Ris\Model\Session $kountSession
    ) {
        $this->kountSession = $kountSession;
    }

    /**
     * @param \Kount_Ris_Request $request
     */
    public function process(\Kount_Ris_Request $request)
    {
        $request->setSessionId($this->kountSession->getKountSessionId());
    }
}
