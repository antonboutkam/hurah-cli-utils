<?php
namespace HurahCli;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use HurahCli\Database\Initializer;
use HurahCli\Database\Migrate;
use HurahCli\Package\Find;

class CommandProvider implements CommandProviderCapability
{
    public function getCommands()
    {
        return [
            new Initializer(),
            new Migrate(),
            new Find()
        ];
    }
}

