<?php

namespace Kennisnet\DTO;

use Env\Annotation\SecretValue;
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
     * @Assert\Callback({"\App\Dto\AppEnv","UrlValidator"})
     * @var string
     */
    public $DATABASE_URL;

    public static function UrlValidator($url, ExecutionContextInterface $context, $payload)
    {
        if (strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $context->addViolation('URL is invalid');
        }
    }
}
