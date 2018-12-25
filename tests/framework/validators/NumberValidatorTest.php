<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\tests\framework\validators;

use yii\tests\data\validators\models\FakedValidationModel;
use yii\tests\TestCase;
use yii\validators\NumberValidator;

/**
 * @group validators
 */
class NumberValidatorTest extends TestCase
{
    private $commaDecimalLocales = ['fr_FR.UTF-8', 'fr_FR.UTF8', 'fr_FR.utf-8', 'fr_FR.utf8', 'French_France.1252'];
    private $pointDecimalLocales = ['en_US.UTF-8', 'en_US.UTF8', 'en_US.utf-8', 'en_US.utf8', 'English_United States.1252'];
    private $oldLocale;

    public function firstDataProvider()
    {
        $min = 'min';
        $max = 'max';
        $int = 'integerOnly';

        $dataSet = [
            [
                'value'       => null,
                'validations' => [
                    'base' => false,
                    $min   => false,
                    $max   => false,
                    $int   => false,
                ],
            ],
            [
                'value'       => 'test',
                'validations' => [
                    'base' => false,
                    $min   => false,
                    $max   => false,
                    $int   => false,
                ],
            ],
            [
                'value'       => 0,
                'validations' => [
                    'base' => true,
                    $min   => true,
                    $max   => true,
                    $int   => true,
                ],
            ],
            [
                'value'       => -10,
                'validations' => [
                    'base' => true,
                    $min   => true,
                    $max   => true,
                    $int   => true,
                ],
            ],
            [
                'value'       => 15.45,
                'validations' => [
                    'base' => true,
                    $min   => true,
                    $max   => true,
                    $int   => false,
                ],
            ],
            [
                'value'       => '15.45',
                'validations' => [
                    'base' => true,
                    $min   => true,
                    $max   => true,
                    $int   => false,
                ],
            ],
            [
                'value'       => '15,45',
                'validations' => [
                    'base' => false, //due to locality
                    $min   => true,
                    $max   => true,
                    $int   => false,
                ],
            ],
            [
                'value'       => '12:45',
                'validations' => [
                    'base' => false,
                    $min   => false,
                    $max   => false,
                    $int   => false,
                ],
            ],
            [
                'value'       => '020',
                'validations' => [
                    'base' => true,
                    $min   => true,
                    $max   => true,
                    $int   => true,
                ],
            ],
            [
                'value'       => 0x14,
                'validations' => [
                    'base' => true,
                    $min   => true,
                    $max   => false,
                    $int   => true,
                ],
            ],
            [
                'value'       => '0x14',
                'validations' => [
                    'base' => false,
                    $min   => true,
                    $max   => true,
                    $int   => true,
                ],
            ],
            [
                'value'       => -9.99,
                'validations' => [
                    'base' => true,
                    $min   => true,
                    $max   => true,
                    $int   => false,
                ],
            ],
            [
                'value'       => -10.01,
                'validations' => [
                    'base' => true,
                    $min   => false,
                    $max   => true,
                    $int   => false,
                ],
            ],
            [
                'value'       => -11,
                'validations' => [
                    'base' => true,
                    $min   => false,
                    $max   => true,
                    $int   => true,
                ],
            ],
            [
                'value'       => 19,
                'validations' => [
                    'base' => true,
                    $min   => true,
                    $max   => true,
                    $int   => true,
                ],
            ],
            [
                'value'       => 20,
                'validations' => [
                    'base' => true,
                    $min   => true,
                    $max   => true,
                    $int   => true,
                ],
            ],
            [
                'value'       => 21,
                'validations' => [
                    'base' => true,
                    $min   => true,
                    $max   => false,
                    $int   => true,
                ],
            ],
            [
                'value'       => 19.99,
                'validations' => [
                    'base' => true,
                    $min   => true,
                    $max   => true,
                    $int   => false,
                ],
            ],
            [
                'value'       => 20.01,
                'validations' => [
                    'base' => true,
                    $min   => true,
                    $max   => false,
                    $int   => false,
                ],
            ],
        ];

        $rules = [
            bindec(001) => [
                'title' => $min,
                'value' => -10,
            ],
            bindec(010) => [
                'title' => $max,
                'value' => 20,
            ],
            bindec(100) => [
                'title' => $int,
                'value' => true,
            ],
        ];

        $result = [];
        foreach ($dataSet as $data) {
            $iterator = 2 ** count($rules) - 1;

            do {
                $dataResult = ['rules' => [], 'value' => $data['value'], 'result' => $data['validations']['base']];
                $resultKey = [(string)$data['value']];
                foreach ($rules as $key => $rule) {
                    if ($key & $iterator) {
                        $resultKey[] = $rule['title'];
                        $dataResult['rules'][$rule['title']] = $rule['value'];
                        $dataResult['result'] = ($dataResult['result'] && $data['validations'][$rule['title']]);
                    }
                }

                $resultKey = implode(', ', $resultKey);
                $result[$resultKey] = $dataResult;
                $iterator -= 1;
            } while ($iterator > 0);
        }

        return $result;
    }

    /** @dataProvider firstDataProvider */
    public function testSimpleData($rules, $value, $result) {
        /** @noinspection PhpUnhandledExceptionInspection */
        $validator = new NumberValidator();
        foreach ($rules as $title => $rule) {
            $validator->$title($rule);
        }

        $error = '';
        $resultActual = $validator->validate($value, $error);
        $this->assertEquals($result, $resultActual, $error);
    }

    private function setCommaDecimalLocale()
    {
        if ($this->oldLocale === false) {
            $this->markTestSkipped('Your platform does not support locales.');
        }

        if (setlocale(LC_NUMERIC, $this->commaDecimalLocales) === false) {
            $this->markTestSkipped('Could not set any of required locales: ' . implode(', ', $this->commaDecimalLocales));
        }
    }

    private function setPointDecimalLocale()
    {
        if ($this->oldLocale === false) {
            $this->markTestSkipped('Your platform does not support locales.');
        }

        if (setlocale(LC_NUMERIC, $this->pointDecimalLocales) === false) {
            $this->markTestSkipped('Could not set any of required locales: ' . implode(', ', $this->pointDecimalLocales));
        }
    }

    private function restoreLocale()
    {
        setlocale(LC_NUMERIC, $this->oldLocale);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->oldLocale = setlocale(LC_NUMERIC, 0);

        // destroy application, Validator must work without $this->app
        $this->destroyApplication();
    }

    public function testEnsureMessageOnInit()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $val = new NumberValidator();
        $this->assertInternalType('string', $val->message);
        $this->assertTrue($val->max === null);

        /** @noinspection PhpUnhandledExceptionInspection */
        $val = (new NumberValidator())->min(-1)->max(20)->integerOnly(true);
        $this->assertInternalType('string', $val->message);
        $this->assertInternalType('string', $val->tooSmall);
        $this->assertInternalType('string', $val->tooBig);
    }

    public function testValidateValueSimple()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $val = new NumberValidator();
        $this->assertTrue($val->validate(20));
        $this->assertTrue($val->validate(0));
        $this->assertTrue($val->validate(-20));
        $this->assertTrue($val->validate('20'));
        $this->assertTrue($val->validate(25.45));

        $this->setPointDecimalLocale();
        $this->assertFalse($val->validate('25,45'));
        $this->setCommaDecimalLocale();
        $this->assertTrue($val->validate('25,45'));
        $this->restoreLocale();

        $this->assertFalse($val->validate('12:45'));

        /** @noinspection PhpUnhandledExceptionInspection */
        $val = (new NumberValidator)->integerOnly(true);
        $this->assertTrue($val->validate(20));
        $this->assertTrue($val->validate(0));
        $this->assertFalse($val->validate(25.45));
        $this->assertTrue($val->validate('20'));
        $this->assertFalse($val->validate('25,45'));
        $this->assertTrue($val->validate('020'));
        $this->assertTrue($val->validate(0x14));
        $this->assertFalse($val->validate('0x14')); // todo check this
    }

    // Это я оставлю отдельно пока. Но тоже надо бы перенести.
    public function testValidateValueAdvanced()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $val = new NumberValidator();
        $this->assertTrue($val->validate('-1.23')); // signed float
        $this->assertTrue($val->validate('-4.423e-12')); // signed float + exponent
        $this->assertTrue($val->validate('12E3')); // integer + exponent
        $this->assertFalse($val->validate('e12')); // just exponent
        $this->assertFalse($val->validate('-e3'));
        $this->assertFalse($val->validate('-4.534-e-12')); // 'signed' exponent
        $this->assertFalse($val->validate('12.23^4')); // expression instead of value

        /** @noinspection PhpUnhandledExceptionInspection */
        $val = (new NumberValidator)->integerOnly(true);
        $this->assertFalse($val->validate('-1.23'));
        $this->assertFalse($val->validate('-4.423e-12'));
        $this->assertFalse($val->validate('12E3'));
        $this->assertFalse($val->validate('e12'));
        $this->assertFalse($val->validate('-e3'));
        $this->assertFalse($val->validate('-4.534-e-12'));
        $this->assertFalse($val->validate('12.23^4'));
    }

    public function testValidateValueWithLocaleWhereDecimalPointIsComma()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $val = new NumberValidator();

        $this->setPointDecimalLocale();
        $this->assertTrue($val->validate(.5));

        $this->setCommaDecimalLocale();
        $this->assertTrue($val->validate(.5));

        $this->restoreLocale();
    }

    public function testValidateValueMin()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $val = (new NumberValidator)->min(1);
        $this->assertTrue($val->validate(1));
        $this->assertFalse($val->validate(-1, $error));
        $this->assertContains('the input value must be no less than 1.', $error);
        $this->assertFalse($val->validate('22e-12'));
        $this->assertTrue($val->validate(PHP_INT_MAX + 1));

        /** @noinspection PhpUnhandledExceptionInspection */
        $val = (new NumberValidator)->min(1)->integerOnly(true);
        $this->assertTrue($val->validate(1));
        $this->assertFalse($val->validate(-1));
        $this->assertFalse($val->validate('22e-12'));
        $this->assertTrue($val->validate(PHP_INT_MAX + 1));
    }

    public function testValidateValueMax()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $val = (new NumberValidator)->max(1.25);
        $this->assertTrue($val->validate(1));
        $this->assertFalse($val->validate(1.5));
        $this->assertTrue($val->validate('22e-12'));
        $this->assertTrue($val->validate('125e-2'));

        /** @noinspection PhpUnhandledExceptionInspection */
        $val = (new NumberValidator)->max(1.25)->integerOnly(true);
        $this->assertTrue($val->validate(1));
        $this->assertFalse($val->validate(1.5));
        $this->assertFalse($val->validate('22e-12'));
        $this->assertFalse($val->validate('125e-2'));
    }

    public function testValidateValueRange()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $val = (new NumberValidator)->min(-10)->max(20);
        $this->assertTrue($val->validate(0));
        $this->assertTrue($val->validate(-10));
        $this->assertTrue($val->validate(-9.99));
        $this->assertFalse($val->validate(-10.01));
        $this->assertFalse($val->validate(-11));
        $this->assertFalse($val->validate(21));

        /** @noinspection PhpUnhandledExceptionInspection */
        $val = (new NumberValidator)->min(-10)->max(20)->integerOnly(true);
        $this->assertTrue($val->validate(0));
        $this->assertFalse($val->validate(-9.99));
        $this->assertFalse($val->validate(-10.01));
        $this->assertFalse($val->validate(-11));
        $this->assertFalse($val->validate(22));
        $this->assertFalse($val->validate('20e-1'));
    }

    public function testValidateAttribute()
    {
        $val = new NumberValidator();
        $model = new FakedValidationModel();
        $model->attr_number = '5.5e1';
        $val->validateAttribute($model, 'attr_number');
        $this->assertFalse($model->hasErrors('attr_number'));
        $model->attr_number = '43^32'; //expression
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $val = new NumberValidator(['min' => 10]);
        $model = new FakedValidationModel();
        $model->attr_number = 10;
        $val->validateAttribute($model, 'attr_number');
        $this->assertFalse($model->hasErrors('attr_number'));
        $model->attr_number = 5;
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $val = new NumberValidator(['max' => 10]);
        $model = new FakedValidationModel();
        $model->attr_number = 10;
        $val->validateAttribute($model, 'attr_number');
        $this->assertFalse($model->hasErrors('attr_number'));
        $model->attr_number = 15;
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $val = new NumberValidator(['max' => 10, 'integerOnly' => true]);
        $model = new FakedValidationModel();
        $model->attr_number = 10;
        $val->validateAttribute($model, 'attr_number');
        $this->assertFalse($model->hasErrors('attr_number'));
        $model->attr_number = 3.43;
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $val = new NumberValidator(['min' => 1]);
        $model = FakedValidationModel::createWithAttributes(['attr_num' => [1, 2, 3]]);
        $val->validateAttribute($model, 'attr_num');
        $this->assertTrue($model->hasErrors('attr_num'));

        // @see https://github.com/yiisoft/yii2/issues/11672
        $model = new FakedValidationModel();
        $model->attr_number = new \stdClass();
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
    }

    public function testValidateAttributeWithLocaleWhereDecimalPointIsComma()
    {
        $val = new NumberValidator();
        $model = new FakedValidationModel();
        $model->attr_number = 0.5;

        $this->setPointDecimalLocale();
        $val->validateAttribute($model, 'attr_number');
        $this->assertFalse($model->hasErrors('attr_number'));

        $this->setCommaDecimalLocale();
        $val->validateAttribute($model, 'attr_number');
        $this->assertFalse($model->hasErrors('attr_number'));

        $this->restoreLocale();
    }

    public function testEnsureCustomMessageIsSetOnValidateAttribute()
    {
        $val = new NumberValidator([
            'tooSmall' => '{attribute} is to small.',
            'min' => 5,
        ]);
        $model = new FakedValidationModel();
        $model->attr_number = 0;
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        $this->assertCount(1, $model->getErrors('attr_number'));
        $msgs = $model->getErrors('attr_number');
        $this->assertSame('attr_number is to small.', $msgs[0]);
    }

    public function testValidateObject()
    {
        $val = new NumberValidator();
        $value = new \stdClass();
        $this->assertFalse($val->validate($value));
    }

    public function testValidateResource()
    {
        $val = new NumberValidator();
        $fp = fopen('php://stdin', 'r');
        $this->assertFalse($val->validate($fp));

        $model = new FakedValidationModel();
        $model->attr_number = $fp;
        $val->validateAttribute($model, 'attr_number');
        $this->assertTrue($model->hasErrors('attr_number'));
        
        // the check is here for HHVM that
        // was losing handler for unknown reason
        if (is_resource($fp)) {
            fclose($fp);
        }
    }

    public function testValidateToString()
    {
        $val = new NumberValidator();
        $object = new TestClass('10');
        $this->assertTrue($val->validate($object));

        $model = new FakedValidationModel();
        $model->attr_number = $object;
        $val->validateAttribute($model, 'attr_number');
        $this->assertFalse($model->hasErrors('attr_number'));
    }
}

class TestClass
{
    public $foo;

    public function __construct($foo)
    {
        $this->foo = $foo;
    }

    public function __toString()
    {
        return $this->foo;
    }
}
