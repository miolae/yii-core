<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use yii\helpers\StringHelper;
use yii\helpers\Yii;
use yii\validators\rules\NumberRule;

/**
 * NumberValidator validates that the attribute value is a number.
 *
 * The format of the number must match the regular expression specified in [[integerPattern]] or [[numberPattern]].
 * Optionally, you may configure the [[max]] and [[min]] properties to ensure the number
 * is within certain range.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 *
 * @method NumberValidator min(float|null $value)
 * @method NumberValidator max(float|null $value)
 * @method NumberValidator integerOnly(bool $value)
 */
class NumberValidator extends Validator
{
    const RULES = [
        'min'         => [
            'message' => '{attribute} must be no less than {min}.',
            'callable' => [self::class, 'checkMin'],
        ],
        'max'         => [
            'message' => '{attribute} must be no less than {max}.',
            'callable' => [self::class, 'checkMax'],
        ],
        'integerOnly' => [
            'class'    => NumberRule::class,
            'default'  => false,
            'message'  => false,
            'required' => true,
        ],
    ];

    /**
     * @var bool whether the attribute value can only be an integer. Defaults to false.
     */
    public $integerOnly = false;
    /**
     * @var int|float upper limit of the number. Defaults to null, meaning no upper limit.
     * @see tooBig for the customized message used when the number is too big.
     */
    public $max;
    /**
     * @var int|float lower limit of the number. Defaults to null, meaning no lower limit.
     * @see tooSmall for the customized message used when the number is too small.
     */
    public $min;
    /**
     * @var string user-defined error message used when the value is bigger than [[max]].
     */
    public $tooBig;
    /**
     * @var string user-defined error message used when the value is smaller than [[min]].
     */
    public $tooSmall;
    /**
     * @var string the regular expression for matching integers.
     */
    public $integerPattern = '/^\s*[+-]?\d+\s*$/';
    /**
     * @var string the regular expression for matching numbers. It defaults to a pattern
     * that matches floating numbers with optional exponential part (e.g. -1.23e-10).
     */
    public $numberPattern = '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/';

    public static function checkMin($minimum, $current)
    {
        return $current >= $minimum;
    }

    public static function checkMax($maximum, $current)
    {
        return $current <= $maximum;
    }
}
