<?php

/*
 * This file is part of Wikiwijs Maken.
 * Maintained by Kennisnet and published under the GNU licence.
 * See the LICENCE.md file for more information.
 */

namespace Kennisnet\Env\Cache;

use Env\EnvironmentVars;
use Exception;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CheckEnvWhileWarmUp implements CacheWarmerInterface
{
    protected $env;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function warmUp($cacheDirectory)
    {
        // Ignore when not called by the CLI or in a test run
        if (getenv('APP_ENV') === 'test' || php_sapi_name() !== 'cli') {
            return;
        }
        $report = EnvironmentVars::checkAppEnv($this->validator);
        if (!$report->valid) {
            throw new Exception($report);
        }
    }

    public function isOptional()
    {
        return false;
    }
}
