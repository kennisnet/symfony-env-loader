<?php

namespace Kennisnet\Env\Command;

use Kennisnet\Env\EnvironmentVars;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CheckEnvVarsCommand extends Command implements ContainerAwareInterface
{
    protected static $defaultName = 'app:check-env-vars';

    /** @var ContainerInterface */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    protected function configure()
    {
        $this
            ->setDescription('Check Env file and System defined values');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \Kennisnet\Env\EnvironmentCheckException
     * @throws \ReflectionException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ValidatorInterface $validator */
        $validator = $this->container->get('validator');
        $report = EnvironmentVars::checkAppEnv($validator);
        if (!$report->valid) {
            $output->write((string)$report);
            return 1; // Return error;
        }
    }
}
