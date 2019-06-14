<?php

/*
 * PHPacto - Contract testing solution
 *
 * Copyright (c) 2019  Damian Długosz
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Bigfoot\PHPacto\Serializer;

use Bigfoot\PHPacto\Matcher\Rules\ComparisonRule;
use Bigfoot\PHPacto\Matcher\Rules\EachItemRule;
use Bigfoot\PHPacto\Matcher\Rules\EqualsRule;
use Bigfoot\PHPacto\Matcher\Rules\ExistsRule;
use Bigfoot\PHPacto\Matcher\Rules\GreaterRule;
use Bigfoot\PHPacto\Matcher\Rules\NumericRule;
use Bigfoot\PHPacto\Matcher\Rules\Rule;
use Bigfoot\PHPacto\Matcher\Rules\StringEqualsRule;
use Bigfoot\PHPacto\Matcher\Rules\StringRule;

class RuleNormalizerTest extends SerializerAwareTestCase
{
    public function format_provider()
    {
        return [
            [null],
            ['json'],
            ['yaml'],
        ];
    }

    /**
     * @dataProvider format_provider
     */
    public function test_it_supports_normalization(?string $format)
    {
        /** @var RuleNormalizer $normalizer */
        $normalizer = $this->getMockBuilder(RuleNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsNormalization'])
            ->getMock();

        $rule = $this->rule->empty();

        self::assertTrue($normalizer->supportsNormalization($rule, $format));
    }

    /**
     * @dataProvider format_provider
     */
    public function test_it_supports_normalization_comparison(?string $format)
    {
        /** @var RuleNormalizer $normalizer */
        $normalizer = $this->getMockBuilder(RuleNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsNormalization'])
            ->getMock();

        $rule = $this->rule->empty(ComparisonRule::class);

        self::assertTrue($normalizer->supportsNormalization($rule, $format));
    }

    /**
     * @dataProvider format_provider
     */
    public function test_it_supports_denormalization(?string $format)
    {
        /** @var RuleNormalizer $normalizer */
        $normalizer = $this->getMockBuilder(RuleNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsDenormalization'])
            ->getMock();

        self::assertTrue($normalizer->supportsDenormalization([], Rule::class, $format));
    }

    /**
     * @dataProvider format_provider
     */
    public function test_it_supports_denormalization_comparison(?string $format)
    {
        /** @var RuleNormalizer $normalizer */
        $normalizer = $this->getMockBuilder(RuleNormalizer::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['supportsDenormalization'])
            ->getMock();

        self::assertTrue($normalizer->supportsDenormalization([], ComparisonRule::class, $format));
    }

    public function test_normalize()
    {
        $rule = $this->rule->hasSample(5);

        $expected = [
            '@rule' => \get_class($rule),
            'sample' => 5,
        ];

        self::assertSame($expected, $this->normalizer->normalize($rule));
    }

    public function test_normalize_equals()
    {
        $rule = new NumericRule(5);

        $expected = 5;

        self::assertSame($expected, $this->normalizer->normalize($rule));
    }

    public function test_normalize_string()
    {
        $rule = new StringRule('string');

        $expected = 'string';

        self::assertSame($expected, $this->normalizer->normalize($rule));
    }

    public function test_normalize_with_alias()
    {
        $rule = new ExistsRule();

        $expected = [
            '@rule' => 'exists',
        ];

        self::assertSame($expected, $this->normalizer->normalize($rule));
    }

    public function test_denormalize_equals()
    {
        $data = 5;

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(NumericRule::class, $rule);
        self::assertEquals(5, $rule->getSample());
    }

    public function test_denormalize_string_equals()
    {
        $data = 'string';

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(StringRule::class, $rule);
        self::assertEquals('string', $rule->getSample());
    }

    public function test_denormalize_with_alias()
    {
        $data = [
            '@rule' => 'greater',
            'value' => 5,
            'sample' => 6,
        ];

        $rule = $this->normalizer->denormalize($data, Rule::class);

        self::assertInstanceOf(GreaterRule::class, $rule);
        self::assertEquals(5, $rule->getValue());
        self::assertEquals(6, $rule->getSample());
    }

//    public function test_normalize_recursive()
//    {
//        $rule = new EachItemRule(new StringEqualsRule('a'), ['a']);
//
//        $data = $this->normalizer->normalize($rule);
//        var_dump($data);
//
//        $rule = $this->normalizer->denormalize($data, Rule::class);
//
//        self::assertInstanceOf(EachItemRule::class, $rule);
//        self::assertInstanceOf(StringEqualsRule::class, $stringRule = $rule->getValue());
//    }
//
//    public function test_normalize_array()
//    {
//        $serializer = SerializerFactory::getInstance();
//
//        $rule = [new EqualsRule(0)];
//
//        $data = $serializer->normalize($rule);
//
//        $rule = $serializer->denormalize($data, Rule::class);
//
//        self::assertCount(1, $rule);
//        self::assertInstanceOf(EqualsRule::class, $rule[0]);
//    }
//
//    public function test_denormalize_nested_array()
//    {
//        $serializer = SerializerFactory::getInstance();
//
//        $data = [1, [2]];
//
//        $rules = $serializer->denormalize($data, Rule::class);
//
//        self::assertCount(2, $rules);
//        self::assertInstanceOf(EqualsRule::class, $rules[0]);
//
//        self::assertCount(1, $rules[1]);
//        self::assertInstanceOf(EqualsRule::class, $rules[1][0]);
//    }
}
