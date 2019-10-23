<?php

/*
 * This file is part of Wikiwijs Maken.
 * Maintained by Kennisnet and published under the GNU licence.
 * See the LICENCE.md file for more information.
 */

namespace Kennisnet\Env;

use Exception;

/**
 * Class EnvironmentCheckException.
 */
class EnvironmentCheckException extends Exception
{
    /**
     * @return EnvironmentCheckException
     */
    public static function noSymfonyEnvVariablesAvailable()
    {
        return new self('Missing the SYMFONY_DOTENV_VARS environment value. This should be provided by the Dotenv loader');
    }
}
