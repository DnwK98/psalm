<?php

namespace Psalm\Tests;

use Psalm\Tests\Traits\InvalidCodeAnalysisTestTrait;
use Psalm\Tests\Traits\ValidCodeAnalysisTestTrait;

class CoreStubsTest extends TestCase
{
    use ValidCodeAnalysisTestTrait;
    use InvalidCodeAnalysisTestTrait;

    public function providerValidCodeParse(): iterable
    {
        yield 'RecursiveArrayIterator::CHILD_ARRAYS_ONLY (#6464)' => [
            'code' => '<?php

            new RecursiveArrayIterator([], RecursiveArrayIterator::CHILD_ARRAYS_ONLY);',
        ];
        yield 'proc_open() named arguments' => [
            'code' => '<?php

            proc_open(
                command: "ls",
                descriptor_spec: [],
                pipes: $pipes,
                cwd: null,
                env_vars: null,
                options: null
            );',
            'assertions' => [],
            'ignored_issues' => [],
            'php_version' => '8.0',
        ];
        yield 'Iterating over \DatePeriod (#5954) PHP7 Traversable' => [
            'code' => '<?php

            $period = new DatePeriod(
                new DateTimeImmutable("now"),
                DateInterval::createFromDateString("1 day"),
                new DateTime("+1 week")
            );
            $dt = null;
            foreach ($period as $dt) {
                echo $dt->format("Y-m-d");
            }',
            'assertions' => [
                '$period' => 'DatePeriod<DateTimeImmutable>',
                '$dt' => 'DateTimeInterface|null',
            ],
            'ignored_issues' => [],
            'php_version' => '7.3',
        ];
        yield 'Iterating over \DatePeriod (#5954) PHP8 IteratorAggregate' => [
            'code' => '<?php

            $period = new DatePeriod(
                new DateTimeImmutable("now"),
                DateInterval::createFromDateString("1 day"),
                new DateTime("+1 week")
            );
            $dt = null;
            foreach ($period as $dt) {
                echo $dt->format("Y-m-d");
            }',
            'assertions' => [
                '$period' => 'DatePeriod<DateTimeImmutable>',
                '$dt' => 'DateTimeImmutable|null',
            ],
            'ignored_issues' => [],
            'php_version' => '8.0',
        ];
        yield 'Iterating over \DatePeriod (#5954), ISO string' => [
            'code' => '<?php

            $period = new DatePeriod("R4/2012-07-01T00:00:00Z/P7D");
            $dt = null;
            foreach ($period as $dt) {
                echo $dt->format("Y-m-d");
            }',
            'assertions' => [
                '$period' => 'DatePeriod<string>',
                '$dt' => 'DateTime|null',
            ],
            'ignored_issues' => [],
            'php_version' => '8.0',
        ];
        yield 'DatePeriod implements only Traversable on PHP 7' => [
            'code' => '<?php

            $period = new DatePeriod("R4/2012-07-01T00:00:00Z/P7D");
            if ($period instanceof IteratorAggregate) {}',
            'assertions' => [],
            'ignored_issues' => [],
            'php_version' => '7.3',
        ];
        yield 'DatePeriod implements IteratorAggregate on PHP 8' => [
            'code' => '<?php

            $period = new DatePeriod("R4/2012-07-01T00:00:00Z/P7D");
            if ($period instanceof IteratorAggregate) {}',
            'assertions' => [],
            'ignored_issues' => ['RedundantCondition'],
            'php_version' => '8.0',
        ];
        yield 'sprintf yields a non-empty-string for non-empty-string value' => [
            'code' => '<?php

            /**
             * @param non-empty-string $foo
             * @return non-empty-string
             */
            function foo(string $foo): string
            {
                return sprintf("%s", $foo);
            }
            ',
        ];
        yield 'sprintf yields a string for possible empty string param' => [
            'code' => '<?php

            $a = sprintf("%s", "");
            ',
            'assertions' => [
                '$a===' => 'string',
            ],
        ];
        yield 'json_encode returns a non-empty-string provided JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE' => [
            'code' => '<?php
                $a = json_encode([], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            ',
            'assertions' => [
                '$a===' => 'non-empty-string',
            ],
        ];
        yield 'json_encode returns a non-empty-string with JSON_THROW_ON_ERROR' => [
            'code' => '<?php
                $a = json_encode([], JSON_THROW_ON_ERROR | JSON_HEX_TAG);
                $b = json_encode([], JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
                $c = json_encode([], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $d = json_encode([], JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);
                $e = json_encode([], JSON_PRESERVE_ZERO_FRACTION);
                $f = json_encode([], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            ',
            'assertions' => [
                '$a===' => 'non-empty-string',
                '$b===' => 'non-empty-string',
                '$c===' => 'non-empty-string',
                '$d===' => 'non-empty-string',
                '$e===' => 'false|non-empty-string',
                '$f===' => 'false|non-empty-string',
            ],
        ];
    }

    public function providerInvalidCodeParse(): iterable
    {
        yield 'json_decode invalid depth' => [
            'code' => '<?php
                json_decode("true", depth: -1);
            ',
            'error_message' => 'InvalidArgument',
        ];
        yield 'json_encode invalid depth' => [
            'code' => '<?php
                json_encode([], depth: 439877348953739);
            ',
            'error_message' => 'InvalidArgument',
        ];
    }
}
