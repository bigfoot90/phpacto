<?php

namespace Bigfoot\PHPacto\Matcher\Rules;

use Bigfoot\PHPacto\Matcher\Mismatches;

class StringContainsRuleTest extends RuleAbstractTest
{
    public function test_it_is_normalizable()
    {
        $rule = new StringContainsRule('amp', 'sample');

        $expected = [
            '@rule' => StringContainsRule::class,
            'value' => 'amp',
            'sample' => 'sample',
            'caseSensitive' => false
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($rule));
    }

    public function supportedValuesProvider()
    {
        return [
            [false, 100],
            [false, 1.0],
            [true, 'string'],
            [false, ''],
            [false, true],
            [false, false],
            [false, null],
            [false, []],
            [false, new class() {}],
            [false, new \stdClass()],
        ];
    }

    /**
     * @dataProvider supportedValuesProvider
     */
    public function testSupportedValues(bool $shouldBeSupported, $value)
    {
        $rule = self::getMockBuilder(StringContainsRule::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['assertSupport'])
            ->getMock();

        if (!$shouldBeSupported) {
            self::expectException(Mismatches\TypeMismatch::class);
        }

        $method = new \ReflectionMethod(StringContainsRule::class, 'assertSupport');
        $method->setAccessible(true);
        $method->invoke($rule, $value);

        self::assertTrue(true, 'No exceptions should be thrown');
    }

    public function matchesTrueProvider()
    {
        return [
            [true, 'a', 'a', true],
            [true, 'a', 'A', false],
            [true, 'b', 'abc', true],
            [true, 'B', 'abc', false],
        ];
    }

    public function matchesFalseProvider()
    {
        return [
            [false, 'a', 'A', true],
            [false, 'a', 'b', true],
            [false, 'a', 'b', false],
            [false, 'a', '', false],
            [false, 'a', '', true],
            [false, 'B', 'abc', true],
            [false, 'd', 'abc', false],
        ];
    }

    /**
     * @dataProvider matchesTrueProvider
     * @dataProvider matchesFalseProvider
     */
    public function testMatch(bool $shouldMatch, $ruleValue, $testValue, bool $caseSensitive)
    {
        $rule = new StringContainsRule($ruleValue, null, $caseSensitive);

        if (!$shouldMatch) {
            self::expectException(Mismatches\ValueMismatch::class);
        }

        $rule->assertMatch($testValue);

        self::assertTrue(true, 'No exceptions should be thrown');
    }
}
