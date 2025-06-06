<?php

namespace ChannelEngine\Magento2;

class NoAttributesException extends \Exception
{
    public function __construct()
    {
        parent::__construct("No attributes found");
    }
}
