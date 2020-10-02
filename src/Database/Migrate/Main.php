<?php
namespace HurahCli\Database\Migrate;


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

    function run()
    {

    }
}