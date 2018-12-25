<?php

namespace yii\validators\rules;

use yii\base\BaseObject;
use yii\exceptions\InvalidConfigException;
use yii\helpers\Yii;
use yii\i18n\I18N;

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

    public function messageFormat(array $params): string
    {
        if (isset($params['value'])) {
            $params['value'] = $this->getMessageValue($params['value']);
        }
        $params['test'] = (string)$this->value;

        /** @noinspection PhpUnhandledExceptionInspection */
        $i18n = Yii::get('i18n', null, false);
        if (isset($i18n)) {
            return $i18n->format($this->message, $params);
        }

        // FIXME This call is moved from abstract Validator "as is". Needs to be fixed.
        return I18N::substitute($this->message, $params);
    }

    /**
     * @param       $value
     *
     * @return mixed
     */
    protected function getMessageValue($value)
    {
        if (is_array($value)) {
            $result = 'array()';
        } elseif (is_object($value)) {
            $result = 'object';
        } else {
            $result = $value;
        }

        return $result;
    }

    protected function validateRegex($value): bool
    {
        return (bool)preg_match($this->regex, $value);
    }
}
