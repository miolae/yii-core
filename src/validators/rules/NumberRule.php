<?php

namespace yii\validators\rules;

class NumberRule extends BaseRule
{
    public function setMessage($message)
    {
        if (empty($message)) {
            if ($this->value) {
                $this->message = '{attribute} must be a integer.';
            } else {
                $this->message = '{attribute} must be an number.';
            }

        }
    }
}
