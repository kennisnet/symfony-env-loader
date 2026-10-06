<?php

namespace Kennisnet\Env\DTO;

use Kennisnet\Env\Annotation\SecretValue;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AppEnv
{
    /**
     * @var string
     */
    #[Assert\NotBlank]
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
     * @var string
     */
    #[SecretValue]
    #[Assert\NotBlank]
    public $APP_SECRET;

    /**
     * @var string
     */
    #[Assert\NotBlank]
    public $PROXY_URL;

    /**
     * @var string
     */
    #[SecretValue]
    #[Assert\NotBlank]
    public $DATABASE_URL;

    /**
     * @param ExecutionContextInterface $context
     */
    #[Assert\Callback]
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
