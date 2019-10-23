# symfony-env-loader

This package let's you load env files on a tier based setup.
You can have the following files depending on you environments:

.env
.env.dist
.env.dev
.env.test
.env.staging
.env.production
.env.local

The .env.local should have at least a APP_ENV=dev file in order for the package to fetch the correct dev env file for you local development environment.

Furthermore you have to create a bootstrap.php file in your config folder in order to call the EnvironmentVars class.
And put this in your bootstrap.php:

```
require dirname(__DIR__).'/vendor/autoload.php';

use Kennisnet\DTO\AppEnv;
use Kennisnet\Env\EnvironmentVars;

$basePath = dirname(__DIR__);
EnvironmentVars::setAppEnvClassName(AppEnv::class);
EnvironmentVars::loadEnv($basePath);
```

Lastly replace the `autoload.php` line with `bootstrap.php` in your `public/index.php file`
Here is a example:

public/index.php
`require dirname(__DIR__).'/config/bootstrap.php';`

