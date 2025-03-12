<?php

namespace ChannelEngine\Magento2\Model;

use ChannelEngine\Magento2\Api\StatusApiInterface;

class StatusApi implements StatusApiInterface
{
    public function getStatus()
    {
        return "OK";
    }
}