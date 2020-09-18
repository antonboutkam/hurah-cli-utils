<?php
namespace HurahCli;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use HurahCli\Database\Initializer;

class CommandProvider implements CommandProviderCapability
{
    public function getCommands()
    {
        return array(new Initializer());
    }
}

