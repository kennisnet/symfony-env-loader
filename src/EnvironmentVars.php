<?php

namespace Kennisnet\Env;

use Env\Annotation\SecretValue;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EnvironmentVars
{
    /**
     * @var array
     */
    public static  $usedEnvFiles = [];

    public static  $secretsEnvFile;

    public static  $localEnvFile;

    public static  $appEnvClassName;

    public static  $appEnv;

    private static $appEnvName;

    public function __construct()
    {
        throw new \LogicException(__CLASS__ . ' Can only be used as static');
    }

    /**
     * @return mixed
     */
    public static function setAppEnvClassName($dtoClassName)
    {
        self::$appEnvClassName = $dtoClassName;
    }

    public static function loadLocalEnvFile($basePath)
    {
        $dotEnv = new Dotenv(true);
        if (is_file($basePath . '/.env.local')) {
            self::$localEnvFile = $basePath . '/.env.local';
            $dotEnv->load($basePath . '/.env.local');
            self::$appEnvName = getenv('APP_ENV');

        } elseif (self::$secretsEnvFile && file_exists(self::$secretsEnvFile)) {

            // File local file does not exits, but the .envs file does. Use this file as initial APP_ENV
            // This resolve the missing APP_ENV in an later stage of the envLoad
            $values = $dotEnv->parse(file_get_contents(self::$secretsEnvFile));
            if (isset($values['APP_ENV'])) {
                self::$appEnvName = $values['APP_ENV'];
            }
        } else {
            self::$appEnvName = getenv('APP_ENV'); // it needs to be available from the system env
        }
    }

    /**
     * @param array $files
     */
    public static function loadEnv($basePath, array $files = [])
    {
        // Register secrets env file
        $secretFile = $basePath . '/.envs';
        if (file_exists($secretFile)) {
            self::$secretsEnvFile = $secretFile;
        }
        // load the local env file to obtain the APP_ENV value before loading env specific files
        self::loadLocalEnvFile($basePath);

        if (empty($files)) {
            $files = [$basePath . '/.env', $basePath . '/.env.' . self::$appEnvName ?? 'dev'];
        }
        $dotEnv = new Dotenv(true);
        // Check if local env file exist, and exclude the local env file from the test environment only load the .env.test
        if (self::$localEnvFile && !in_array(self::$localEnvFile, $files) && getenv('APP_ENV') !== 'test') {
            $files[] = self::$localEnvFile;
        }
        if (self::$secretsEnvFile && !in_array(self::$secretsEnvFile, $files)) {
            $files[] = self::$secretsEnvFile;
        }

        self::$usedEnvFiles = $files;

        $dotEnv->overload(...$files);
    }

    /**
     * @param ValidatorInterface $validator
     *
     * @return CheckReport
     *
     * @throws EnvironmentCheckException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public static function checkAppEnv(ValidatorInterface $validator)
    {
        if (!isset($_SERVER['SYMFONY_DOTENV_VARS'])) {
            throw EnvironmentCheckException::noSymfonyEnvVariablesAvailable();
        }
        $envValues = [];
        foreach (self::$usedEnvFiles as $file) {
            if ($file === self::$secretsEnvFile) {
                continue;
            }
            $values    = (new Dotenv())->parse(file_get_contents($file), $file);
            $envValues = array_merge($envValues, $values);
        }

        $checkReport               = new CheckReport();
        $checkReport->usedEnvFiles = self::$usedEnvFiles;
        $normalizer                = new ObjectNormalizer();
        ksort($envValues);
        $envValuesWithSecrets = [];
        if (self::$secretsEnvFile) {
            $envsFileData         = (new Dotenv())->parse(file_get_contents(self::$secretsEnvFile),
                                                          self::$secretsEnvFile);
            $envValuesWithSecrets = array_merge($envValues, $envsFileData);
        }

        /** @var Dto $appEnv */
        $appEnv       = $normalizer->denormalize(
            !empty($envValuesWithSecrets) ? $envValuesWithSecrets : $envValues,
            self::$appEnvClassName
        );
        self::$appEnv = $appEnv;
        try {
            $checkReport->errors = $validator->validate($appEnv);
            $checkReport->valid  = $checkReport->errors->count() < 1;
        } catch (\throwable $validationException) {
            $checkReport->valid = false;
        }

        // Diff Env values
        if (self::$secretsEnvFile) {
            $envFileData       = (new Dotenv())->parse(file_get_contents(self::$secretsEnvFile), self::$secretsEnvFile);
            $checkReport->diff = array_diff_assoc($envFileData, $envValues);

            foreach ($checkReport->diff as $item => $value) {
                // Check if item / property is a marked as secret value
                if (!SecretValue::hasAnnotation(self::$appEnvClassName, $item)) {
                    $checkReport->errors->add(
                        new ConstraintViolation(
                            'Env mismatch for field: ' . $item .
                            ' Repo value = ' . $envFileData[$item] .
                            ' <> ' .
                            ' System value = ' . $envValues[$item] ?? '',
                            '',
                            [], null, '', $envValues[$item]
                        )
                    );
                } else {
                    // Remove secrets to prevent form leaking to logs or notification messages
                    $checkReport->diff[$item] = '*****';
                }
            }
        }

        $checkReport->valid = $checkReport->errors->count() < 1;

        return $checkReport;
    }
}
