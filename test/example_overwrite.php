<?php

use Kennisnet\Env\EnvironmentVars;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ValidatorBuilder;

require dirname(__DIR__).'/vendor/autoload.php';

class AppEnv {
    public $APP_ENV;

    /**
     *
     * @var bool
     */
    public $APP_DEBUG;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    public $APP_TEST;
}

// The env loading is not fully compatible with the symfony defaults. because of the .envs file from BKS
// This part is an attempt to the Symfony 4 env files structure.
$basePath = dirname(__DIR__.'/test');
EnvironmentVars::setAppEnvClassName(AppEnv::class);
EnvironmentVars::loadEnv($basePath);

$_SERVER              += $_ENV;
$_SERVER['APP_ENV']   = $_ENV['APP_ENV'] = ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? null) ?: 'dev';
$_SERVER['APP_DEBUG'] = $_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? 'production' !== $_SERVER['APP_ENV'];
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = (int)$_SERVER['APP_DEBUG'] || filter_var($_SERVER['APP_DEBUG'],
                                                                                      FILTER_VALIDATE_BOOLEAN) ? '1' : '0';

$builder = new ValidatorBuilder();
// Read the constraints from PHP attributes: Symfony 6.4+ has enableAttributeMapping(); on 5.4 the same is
// enableAnnotationMapping(true), where `true` skips the Doctrine annotation reader.
method_exists($builder, 'enableAttributeMapping')
    ? $builder->enableAttributeMapping()
    : $builder->enableAnnotationMapping(true);

$checkReport = EnvironmentVars::checkAppEnv($builder->getValidator());
echo  $checkReport;
