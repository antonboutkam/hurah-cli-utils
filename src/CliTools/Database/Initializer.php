<?php
namespace HurahCli\CliTools\Database;

use Composer\Command\BaseCommand;


class Initializer extends BaseCommand
{
    protected function configure()
    {
        $this->setName("novum-db-init");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Executing');
    }

}
