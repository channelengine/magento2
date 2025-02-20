<?php

namespace ChannelEngine\Magento2\Api;

interface StatusApiInterface
{
    /**
     * Get status of the ChannelEngine module
     *
     * @return string
     */
    public function getStatus();
}