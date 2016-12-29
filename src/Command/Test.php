<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Test extends Command
{
    //查看帮助信息 php console help test
    //运行  php console help your_name
    protected function configure()
    {
        $this->setName('test')
            ->setDescription('This is a test')
            ->setDefinition([

                //参数:
                // php console test mary   第一个参数，对应name，值为mary
                new InputArgument('name', InputArgument::REQUIRED, 'your name'), //必须参数
                new InputArgument('sex', InputArgument::OPTIONAL, 'boy'),//可选参数

                //选项:
                //php console test mary  -a 20
                //php console test mary  --age 20
                new InputOption('age', 'a', InputOption::VALUE_OPTIONAL, 'your age', '18'),

            ]);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        //使用leafphp来处理异常
        $this->getApplication()->setCatchExceptions(false);

        //交互获取输入信息
        $output->write("enter your password:");
        $password = trim(fgets(STDIN));

        $name = $input->getArgument('name');
        $age = $input->getOption('age');

        $output->writeln('hi,' . $name . ', your age is ' . $age . ' password:' . $password);
    }

}