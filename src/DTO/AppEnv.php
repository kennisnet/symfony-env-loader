<?php

namespace Kennisnet\Env\DTO;

use Kennisnet\Env\Annotation\SecretValue;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AppEnv
{
    /**
     *
     * @Assert\NotBlank()
     * @var string
     */
    public $APP_ENV;

    /**
     * @var string
     */
    public $TIER;

    /**
     * @var string
     */
    public $DEV_UUID;

    /**
     * @SecretValue()
     * @Assert\NotBlank()
     * @var string
     */
    public $APP_SECRET;

    /**
     * @Assert\NotBlank()
     * @var string
     */
    public $PROXY_URL;

    /**
     * @SecretValue()
     * @Assert\NotBlank()
     * @var string
     */
    public $DATABASE_URL;

    /**
     * @Assert\Callback
     * @param ExecutionContextInterface $context
     */
    public function UrlValidator(ExecutionContextInterface $context)
    {
        $databaseUrl = $this->DATABASE_URL;

        if (strpos($databaseUrl, '//') === 0) {
            $databaseUrl = 'https:' . $databaseUrl;
        }

        if (!filter_var($databaseUrl, FILTER_VALIDATE_URL)) {
            $context->addViolation('Database URL is invalid');
        }
    }
}
