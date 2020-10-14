<?php


namespace HurahCli;


use Hi\Helpers\Domain;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class BaseCommand extends \Composer\Command\BaseCommand
{
    protected function noDomainsMassage(OutputInterface $output):void
    {
        $output->writeln("<warning>No databases / domains available</warning>");
        $output->writeln("Databases are bound to a domain, you must first install or create a domain");
        $output->writeln("Here are some domain packages you could install:");
        $sHr = "---------------------------------------";
        $output->writeln($sHr);
        $command = $this->getApplication()->find('search');
        $arguments = [
            '--type'    =>  'novum-domain',
            'tokens'  => ['novum']
        ];

        $searchInput = new ArrayInput($arguments);
        $returnCode = $command->run($searchInput, $output);
        $output->writeln($sHr);
        //  composer search --type='novum-domain' novum
        $output->writeln("<info>Installing is as easy as typing \"composer require <vendor/package>\"</info>");
        $output->writeln("<info>For example \"composer require novum/domain-svb\"</info>");

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Domain[] $aDomain
     * @throws \Exception
     */
    protected function selectDomainMessage(InputInterface $input, OutputInterface $output, array $aDomain):void
    {
        $output->writeln("<question>This command requires a domain, please choose one</question>");
        $this->hr($output);

        $i=0;
        $aDomainIndex = [];
        foreach ($aDomain as $domain)
        {
            $i++;
            $aDomainIndex[$i] = $domain->getSystemID();
            $output->writeln("{$i}: {$domain->getSystemID()}");
        }
        $this->hr($output);

        $helper = $this->getHelper('question');

        $question = new Question("<question>Select the appropriate numbers separated by commas and/or spaces, or leave input" . PHP_EOL . "blank to select all (Enter 'c' to cancel): </question>", 'all');
        $sDomainInput = $helper->ask($input, $output, $question);

        $aDomainInput = explode(' ', str_replace(',', ' ', $sDomainInput));
        $aValues = [];
        foreach ($aDomainInput as $sDomainItem)
        {
            $sDomainItem = trim($sDomainItem);
            if(trim($sDomainItem) === 'all')
            {
                $input->setArgument('domain', 'all');
            }
            else if(is_numeric($sDomainItem) && isset($aDomainIndex[$sDomainItem]))
            {
                $output->writeln("Executing task on <info>{$aDomainIndex[$sDomainItem]}</info>");
                $aValues[] = $aDomainIndex[$sDomainItem];
            }
            else
            {
                $output->writeln("<warning>$sDomainItem is not a valid option, skipping</warning>");
            }
        }
        if(!empty($aValues))
        {
            $input->setArgument('domain', $aValues);
        }

    }

    protected function hr(OutputInterface  $output)
    {
        $output->writeln('- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -');
    }

}