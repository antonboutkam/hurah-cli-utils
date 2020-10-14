<?php
namespace HurahCli\Database\Initializer;


use Cli\CodeGen\System\Configs;
use Cli\CodeGen\System\Databases;
use Cli\Tools\VO\SystemBuildVo;
use Hi\Helpers\DirectoryStructure;
use HurahCli\Database\Generic\DbLogin;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Main
{
    private InputInterface $input;
    private OutputInterface $output;
    private QuestionHelper $helper;
    private array $aDomains;

    function __construct(InputInterface $input, OutputInterface $output, QuestionHelper $helper, array $aDomains)
    {
        $this->input = $input;
        $this->output = $output;
        $this->helper = $helper;
        $this->aDomains = $aDomains;
    }
    private function getRootLogin():DbLogin
    {
        $sRootEnvFile = './.env';

        if(file_exists($sRootEnvFile))
        {
            $aEnvFile = parse_ini_file($sRootEnvFile);

            if(isset($aEnvFile['DATABASE_IP']))
            {
                $sDbServer = $aEnvFile['DATABASE_IP'];
            }

            if(isset($aEnvFile['DATABASE_ROOT_PASSWORD']))
            {
                $sRootUser = 'root';
                $sRootPassword = $aEnvFile['DATABASE_ROOT_PASSWORD'];
            }
            if(isset($aEnvFile['DATABASE_ROOT_USER']))
            {
                $sRootUser = $aEnvFile['DATABASE_ROOT_USER'];
            }
        }

        if(!isset($sRootUser))
        {
            $userQuestion = $this->askUsername();
        }
        if(!isset($sRootPassword))
        {
            $sRootPassword = $this->askPassword();
        }
        $oDbLogin = new DbLogin($sRootUser, $sRootPassword, $sDbServer);

        while(!$oDbLogin->canConnect())
        {
            $user = $this->askUsername();
            $password = $this->askPassword();
            $oDbLogin = new DbLogin($user, $password, $sDbServer);
        }

        return $oDbLogin;
    }

    private function askUsername($sType = 'root'):string
    {
        $question = new Question("<question>Please provide the mysql $sType username: </question>");
        return $this->helper->ask($this->input, $this->output, $question);
    }
    private function askPassword($sType = 'root'):string
    {
        $question = new Question("<question>Please provide the mysql $sType password: </question>");
        return $this->helper->ask($this->input, $this->output, $question);
    }

    private function askServer():string
    {
        $question = new Question("<question>Please provide the ip address or hostname of the database server: </question>");
        return $this->helper->ask($this->input, $this->output, $question);
    }

    private function getDomainLogin($sDomain)
    {
        $oDirectoryStructure = new DirectoryStructure();
        $sDomainEnvDest = $oDirectoryStructure->getEnvDir() . '/.' . $sDomain;
        $sDomainEnvSrc = $oDirectoryStructure->getDomainDir() . '/' . $sDomain . '/.env';
        if(file_exists($sDomainEnvSrc))
        {
            $this->output->writeln("Reading database settings from " . $sDomainEnvSrc);
            $aEnvFile = parse_ini_file($sDomainEnvSrc);

            if(isset($aEnvFile['DB_USER']))
            {
                $sDbUser = $aEnvFile['DB_USER'];
            }
            else
            {
                $sDbUser = $this->askUsername('domain');
            }

            if(isset($aEnvFile['DB_PASS']))
            {
                $sDbPass = $aEnvFile['DB_PASS'];
            }
            else
            {
                $sDbPass = $this->askPassword('domain');
            }

            if(isset($aEnvFile['DB_HOST']))
            {
                $sDbServer = $aEnvFile['DB_HOST'];
            }
            else
            {
                $sDbServer = $this->askServer();
            }
            return new DbLogin($aEnvFile['DB_USER'], $aEnvFile['DB_PASS'], $aEnvFile['DB_HOST']);
        }
    }

    function run()
    {
        $oRootLogin = $this->getRootLogin();
        $this->output->writeln("Got working root login, now start initializing");
        foreach ($this->aDomains as $sDomain)
        {
            $this->output->writeln("Initializing $sDomain");
            $oDomainLogin = $this->getDomainLogin($sDomain);

            $this->output->writeln("Checking if user <info>{$oDomainLogin->getUser()}</info> exists");
            if(!$oDomainLogin->canConnect())
            {
                $this->output->writeln("Creating user <info>{$oDomainLogin->getUser()}</info>");
                $oRootLogin->createUser($oDomainLogin);
                $this->output->writeln("User <info>{$oDomainLogin->getUser()}</info> created");
            }
            else
            {
                $this->output->writeln("User <info>{$oDomainLogin->getUser()}</info> exists");
            }

            $this->output->writeln("Checking if database <info>{$oDomainLogin->getDbName()}</info> exists");
            if(!$oRootLogin->dbExists($oDomainLogin->getDbName()))
            {
                $this->output->writeln("Database <info>{$oDomainLogin->getDbName()}</info> does not exist, creating");
                $oRootLogin->dbCreate($oDomainLogin->getDbName());
            }
            else
            {
                $this->output->writeln("Database <info>{$oDomainLogin->getDbName()}</info> already existed so skipping creation");
            }

            $this->output->writeln("Checking if user <info>{$oDomainLogin->getUser()}</info> has access to database <info>{$oDomainLogin->getDbName()}</info>");
            if(!$oDomainLogin->canSelectDb($oDomainLogin->getDbName()))
            {
                $this->output->writeln("User <info>{$oDomainLogin->getUser()}</info> has no acces yet, granting now");
                $oRootLogin->grantUserAccess($oDomainLogin, $oDomainLogin->getDbName());
            }
            else
            {
                $this->output->writeln("<info>{$oDomainLogin->getUser()}</info> has access to database <info>{$oDomainLogin->getDbName()}</info>");
            }


            echo "Creating propel source config " . PHP_EOL;
            $oSytemBuildVo = new SystemBuildVo([
                'build_dir' => $sDomain,
                'config_folder' => $sDomain,
                'db_server' => $oDomainLogin->getHost(),
                'db_name' => $oDomainLogin->getDbName(),
                'password' => $oDomainLogin->getPass(),
            ]);
            $oConfigMaker = new Configs();
            $oConfigMaker->create($oSytemBuildVo);

        }





        // echo $sEnvFile;
        /*
        foreach ($aDomains as $sDomain)
        {

        }
        */
    }
}