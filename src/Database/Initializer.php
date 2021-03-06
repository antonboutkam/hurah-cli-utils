<?php
namespace HurahCli\Database;

use HurahCli\BaseCommand;
use Hi\Helpers\Domain;
use Hi\Helpers\DirectoryStructure;
use HurahCli\Database\Initializer\Main;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use HurahCli\Database\Initializer\NoDomains;
use Symfony\Component\Console\Question\Question;

class Initializer extends BaseCommand
{
    protected function configure()
    {
        $this->setName("novum:db-init");
        $this->setDescription('Creates a new database, user and initializes schema\'s based on schema.xml');
        $this->addArgument('domain', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'domain to install');
    }
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output); // TODO: Change the autogenerated stub
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $oDirectoryStructure = new DirectoryStructure();
        $aDomains = $oDirectoryStructure->getDomainCollection();

        $aSelectedDomains = $input->getArgument('domain');

        if(empty($aDomains))
        {
            $this->noDomainsMassage($output);
            return;
        }
        else if(empty($aSelectedDomains))
        {
            $this->selectDomainMessage($input, $output, $aDomains);
        }
        $aSelectedDomains = $input->getArgument('domain');

        $helper = $this->getHelper('question');
        $oMain = new Main($input, $output, $helper, $aSelectedDomains === 'all' ? []: $aSelectedDomains);
        $oMain->run();
    }

}
