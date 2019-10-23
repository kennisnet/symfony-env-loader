<?php

namespace Kennisnet\Env;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class CheckReport
{
    /**
     * @var array
     */
    public $usedEnvFiles = [];

    /**
     * @var bool
     */
    public $valid;

    /**
     * @var ConstraintViolationList
     */
    public $errors;

    /**
     * @var array
     */
    public $diff;

    public function __construct()
    {
        $this->errors = new ConstraintViolationList();
    }

    public function __toString()
    {
        return strtr('
Environment settings are @valid.
Used envs files : @files
The diff is : 
@diff
-----------------------------------
@errors 
        ', [
            '@files'  => join(',', $this->usedEnvFiles),
            '@valid'  => $this->valid ? 'correct and valid' : 'Not correct or valid',
            '@diff'   => is_array($this->diff) ? json_encode($this->diff, JSON_PRETTY_PRINT) : '[]',
            '@errors' => $this->errors->count() > 0 ? join(PHP_EOL,
                                                           array_map(function (ConstraintViolation $violation) {
                                                               return $violation->getPropertyPath() . ' : ' . $violation->getMessage();
                                                           }, $this->errors->getIterator()->getArrayCopy())) : '',
        ]);
    }
}
