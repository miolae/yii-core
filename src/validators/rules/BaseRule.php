<?php

namespace yii\validators\rules;

use yii\base\BaseObject;
use yii\exceptions\InvalidConfigException;

/**
 * Class BaseRule
 *
 * @package yii\validators\rules
 *
 * @property-write callable $validationMethod
 * @property-write string   $regex
 */
class BaseRule extends BaseObject
{
    public $value;
    public $message;
    protected $validationMethod;
    protected $regex;

    /**
     * @param $value
     *
     * @return bool
     * @throws InvalidConfigException
     */
    public function validate($value): bool
    {
        if (!empty($this->validationMethod)) {
            return call_user_func_array($this->validationMethod, [$this->value, $value]);
        }

        if (empty($this->regex)) {
            $message = 'You must have either validationMethod or regex set for ' . static::class;
            throw new InvalidConfigException($message);
        }

        return $this->validateRegex($value);
    }

    public function setValidationMethod(Callable $method)
    {
        $this->validationMethod = $method;
    }

    public function setRegex(string $regex)
    {
        $this->regex = $regex;
    }

    protected function validateRegex($value): bool
    {
        return preg_match($this->regex, $value);
    }
}
