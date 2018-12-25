<?php

namespace yii\validators\rules;

use yii\helpers\StringHelper;

class NumberRule extends BaseRule
{
    public $regex = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';
    public $regexInt = '/^\s*[+-]?\d+\s*$/';

    public function validate($value): bool
    {
        if ($this->isNotNumber($value)) {
            return false;
        }

        $regex = $this->value ? $this->regexInt : $this->regex;
        $value = StringHelper::normalizeNumber($value);

        return (bool)preg_match($regex, $value);
    }

    public function setMessage($message)
    {
        if (empty($message)) {
            if ($this->value) {
                $this->message = '{attribute} must be an integer.';
            } else {
                $this->message = '{attribute} must be a number.';
            }
        }
    }

    private function isNotNumber($value)
    {
        return is_array($value)
            || (is_object($value) && !method_exists($value, '__toString'))
            || (!is_object($value) && !is_scalar($value) && $value !== null);
    }
}
