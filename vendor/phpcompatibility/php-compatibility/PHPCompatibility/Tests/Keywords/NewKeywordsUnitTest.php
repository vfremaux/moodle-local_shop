<?php
/**
 * PHPCompatibility, an external standard for PHP_CodeSniffer.
 *
 * @package   PHPCompatibility
 * @copyright 2012-2020 PHPCompatibility Contributors
 * @license   https://opensource.org/licenses/LGPL-3.0 LGPL3
 * @link      https://github.com/PHPCompatibility/PHPCompatibility
 */

namespace PHPCompatibility\Tests\Keywords;

use PHPCompatibility\Tests\BaseSniffTestCase;

/**
 * Test the NewKeywords sniff.
 *
 * @group newKeywords
 * @group keywords
 *
 * @covers \PHPCompatibility\Sniffs\Keywords\NewKeywordsSniff
 *
 * @since 5.5
 */
class NewKeywordsUnitTest extends BaseSniffTestCase
{

    /**
     * Test allow_url_include
     *
     * @return void
     */
    public function testDirMagicConstant()
    {
        $file = $this->sniffFile(__FILE__, '5.2');
        $this->assertError($file, 3, '__DIR__ magic constant is not present in PHP version 5.2 or earlier');
        $this->assertError($file, 122, '__DIR__ magic constant is not present in PHP version 5.2 or earlier');

        $file = $this->sniffFile(__FILE__, '5.3');
        $this->assertNoViolation($file, 3);
        $this->assertNoViolation($file, 122);
    }

    /**
     * Test insteadof
     *
     * @return void
     */
    public function testInsteadOf()
    {
        $file = $this->sniffFile(__FILE__, '5.3');
        $this->assertError($file, 15, '"insteadof" keyword (for traits) is not present in PHP version 5.3 or earlier');
        $this->assertError($file, 16, '"insteadof" keyword (for traits) is not present in PHP version 5.3 or earlier');

        $file = $this->sniffFile(__FILE__, '5.4');
        $this->assertNoViolation($file, 15);
        $this->assertNoViolation($file, 16);
    }

    /**
     * Test namespace keyword
     *
     * @return void
     */
    public function testNamespaceKeyword()
    {
        $file = $this->sniffFile(__FILE__, '5.2');
        $this->assertError($file, 20, '"namespace" keyword is not present in PHP version 5.2 or earlier');

        $file = $this->sniffFile(__FILE__, '5.3');
        $this->assertNoViolation($file, 20);
    }

    /**
     * Test against false positives for the namespace keyword.
     *
     * @return void
     */
    public function testNamespaceNoFalsePositives()
    {
        $file = $this->sniffFile(__FILE__, '5.2');
        $this->assertNoViolation($file, 117);
    }

    /**
     * testNamespaceConstant
     *
     * @return void
     */
    public function testNamespaceConstant()
    {
        $file = $this->sniffFile(__FILE__, '5.2');
        $this->assertError($file, 22, '__NAMESPACE__ magic constant is not present in PHP version 5.2 or earlier');

        $file = $this->sniffFile(__FILE__, '5.3');
        $this->assertNoViolation($file, 22);
    }

    /**
     * Test trait keyword
     *
     * @return void
     */
    public function testTraitKeyword()
    {
        $file = $this->sniffFile(__FILE__, '5.3');
        $this->assertError($file, 24, '"trait" keyword is not present in PHP version 5.3 or earlier');
        $this->assertError($file, 105, '"trait" keyword is not present in PHP version 5.3 or earlier');

        $file = $this->sniffFile(__FILE__, '5.4');
        $this->assertNoViolation($file, 24);
        $this->assertNoViolation($file, 105);
    }

    /**
     * Test trait magic constant
     *
     * @return void
     */
    public function testTraitConstant()
    {
        $file = $this->sniffFile(__FILE__, '5.3');
        $this->assertError($file, 26, '__TRAIT__ magic constant is not present in PHP version 5.3 or earlier');

        $file = $this->sniffFile(__FILE__, '5.4');
        $this->assertNoViolation($file, 26);
    }

    /**
     * Test the use keyword
     *
     * @return void
     */
    public function testUse()
    {
        $file = $this->sniffFile(__FILE__, '5.2');
        $this->assertError($file, 14, '"use" keyword (for traits/namespaces/anonymous functions) is not present in PHP version 5.2 or earlier');

        $file = $this->sniffFile(__FILE__, '5.3');
        $this->assertNoViolation($file, 14);
    }

    /**
     * Test yield
     *
     * @dataProvider dataYield
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testYield($line)
    {
        $file = $this->sniffFile(__FILE__, '5.4');
        $this->assertError($file, $line, '"yield" keyword (for generators) is not present in PHP version 5.4 or earlier');

        $file = $this->sniffFile(__FILE__, '5.5');
        $this->assertNoViolation($file, $line);
    }

    /**
     * Data provider.
     *
     * @see testYield()
     *
     * @return array
     */
    public static function dataYield()
    {
        return [
            [33],
        ];
    }

    /**
     * Test against false positives for the yield keyword.
     *
     * @return void
     */
    public function testYieldNoFalsePositives()
    {
        $file = $this->sniffFile(__FILE__, '5.4');
        $this->assertNoViolation($file, 120);
    }

    /**
     * Test yield from
     *
     * @dataProvider dataYieldFrom
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testYieldFrom($line)
    {
        $file = $this->sniffFile(__FILE__, '5.6');
        $this->assertError($file, $line, '"yield from" keyword (for generators) is not present in PHP version 5.6 or earlier');

        $file = $this->sniffFile(__FILE__, '7.0');
        $this->assertNoViolation($file, $line);
    }

    /**
     * Data provider.
     *
     * @see testYieldFrom()
     *
     * @return array
     */
    public static function dataYieldFrom()
    {
        return [
            [75],
            [76],
            [78],
        ];
    }

    /**
     * testFinally
     *
     * @return void
     */
    public function testFinally()
    {
        $file = $this->sniffFile(__FILE__, '5.4');
        $this->assertError($file, 9, '"finally" keyword (in exception handling) is not present in PHP version 5.4 or earlier');
        $this->assertError($file, 108, '"finally" keyword (in exception handling) is not present in PHP version 5.4 or earlier');

        $file = $this->sniffFile(__FILE__, '5.5');
        $this->assertNoViolation($file, 9);
        $this->assertNoViolation($file, 108);
    }

    /**
     * testFinallyNoFalsePositives
     *
     * @dataProvider dataFinallyNoFalsePositives
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testFinallyNoFalsePositives($line)
    {
        $file = $this->sniffFile(__FILE__, '5.4');
        $this->assertNoViolation($file, $line);
    }

    /**
     * Data provider.
     *
     * @see testConstNoFalsePositives()
     *
     * @return array
     */
    public static function dataFinallyNoFalsePositives()
    {
        return [
            [125],
        ];
    }

    /**
     * testConst
     *
     * @dataProvider dataConst
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testConst($line)
    {
        $file = $this->sniffFile(__FILE__, '5.2');
        $this->assertError($file, $line, '"const" keyword is not present in PHP version 5.2 or earlier');

        $file = $this->sniffFile(__FILE__, '5.3');
        $this->assertNoViolation($file, $line);
    }

    /**
     * Data provider.
     *
     * @see testConst()
     *
     * @return array
     */
    public static function dataConst()
    {
        return [
            [37],
            [44],
            [53],
            [62],
        ];
    }


    /**
     * testConstNoFalsePositives
     *
     * @dataProvider dataConstNoFalsePositives
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testConstNoFalsePositives($line)
    {
        $file = $this->sniffFile(__FILE__, '5.2');
        $this->assertNoViolation($file, $line);
    }

    /**
     * Data provider.
     *
     * @see testConstNoFalsePositives()
     *
     * @return array
     */
    public static function dataConstNoFalsePositives()
    {
        return [
            [40],
            [41],
            [49],
            [50],
            [58],
            [59],
        ];
    }


    /**
     * testCallable
     *
     * @return void
     */
    public function testCallable()
    {
        $file = $this->sniffFile(__FILE__, '5.3');
        $this->assertError($file, 67, '"callable" keyword is not present in PHP version 5.3 or earlier');

        $file = $this->sniffFile(__FILE__, '5.4');
        $this->assertNoViolation($file, 67);
    }

    /**
     * testGoto
     *
     * @return void
     */
    public function testGoto()
    {
        $file = $this->sniffFile(__FILE__, '5.2');
        $this->assertError($file, 69, '"goto" keyword is not present in PHP version 5.2 or earlier');

        $file = $this->sniffFile(__FILE__, '5.3');
        $this->assertNoViolation($file, 69);
    }

    /**
     * testNowdoc
     *
     * @return void
     */
    public function testNowdoc()
    {
        $file = $this->sniffFile(__FILE__, '5.2');
        $this->assertError($file, 89, 'nowdoc functionality is not present in PHP version 5.2 or earlier');
        $this->assertError($file, 93, 'nowdoc functionality is not present in PHP version 5.2 or earlier');

        $file = $this->sniffFile(__FILE__, '5.3');
        $this->assertNoViolation($file, 89);
        $this->assertNoViolation($file, 93);
    }

    /**
     * testQuotedHeredoc
     *
     * @return void
     */
    public function testQuotedHeredoc()
    {
        $file = $this->sniffFile(__FILE__, '5.2');
        $this->assertError($file, 96, '(Double) quoted Heredoc identifier is not present in PHP version 5.2 or earlier');

        $file = $this->sniffFile(__FILE__, '5.3');
        $this->assertNoViolation($file, 96);
    }

    /**
     * testQuotedHeredocNoFalsePositives
     *
     * @return void
     */
    public function testQuotedHeredocNoFalsePositives()
    {
        $file = $this->sniffFile(__FILE__, '5.2');
        $this->assertNoViolation($file, 82);
    }

    /**
     * Test arrow functions.
     *
     * @dataProvider dataArrowFunction
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testArrowFunction($line)
    {
        $file = $this->sniffFile(__FILE__, '7.3');
        $this->assertError($file, $line, 'The "fn" keyword for arrow functions is not present in PHP version 7.3 or earlier');

        $file = $this->sniffFile(__FILE__, '7.4');
        $this->assertNoViolation($file, $line);
    }

    /**
     * Data provider.
     *
     * @see testArrowFunction()
     *
     * @return array
     */
    public static function dataArrowFunction()
    {
        return [
            [150],
            [152],
            [154],
            [157],
            [158],
            [161],
        ];
    }

    /**
     * Test against false positives for the fn keyword for arrow functions.
     *
     * @dataProvider dataArrowFunctionNoFalsePositives
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testArrowFunctionNoFalsePositives($line)
    {
        $file = $this->sniffFile(__FILE__, '7.4');
        $this->assertNoViolation($file, $line);
    }

    /**
     * Data provider.
     *
     * @see testArrowFunctionNoFalsePositives()
     *
     * @return array
     */
    public static function dataArrowFunctionNoFalsePositives()
    {
        $data = [];

        for ($i = 130; $i <= 148; $i++) {
            $data[] = [$i];
        }

        return $data;
    }

    /**
     * Test match expressions.
     *
     * @dataProvider dataMatchExpressions
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testMatchExpressions($line)
    {
        $file = $this->sniffFile(__FILE__, '7.4');
        $this->assertError($file, $line, 'The "match" keyword is not present in PHP version 7.4 or earlier');

        $file = $this->sniffFile(__FILE__, '8.0');
        $this->assertNoViolation($file, $line);
    }

    /**
     * Data provider.
     *
     * @see testMatchExpressions()
     *
     * @return array
     */
    public static function dataMatchExpressions()
    {
        return [
            [189],
            [196],
            [202],
            [207],
            [212],
            [219],
            [222],
        ];
    }

    /**
     * Test against false positives for match expressions.
     *
     * @dataProvider dataMatchExpressionsNoFalsePositives
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testMatchExpressionsNoFalsePositives($line)
    {
        $file = $this->sniffFile(__FILE__, '8.0');
        $this->assertNoViolation($file, $line);
    }

    /**
     * Data provider.
     *
     * @see testMatchExpressionsNoFalsePositives()
     *
     * @return array
     */
    public static function dataMatchExpressionsNoFalsePositives()
    {
        $data = [];

        for ($i = 168; $i <= 187; $i++) {
            $data[] = [$i];
        }

        return $data;
    }

    /**
     * Test enums.
     *
     * @dataProvider dataEnum
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testEnum($line)
    {
        $file = $this->sniffFile(__FILE__, '8.0');
        $this->assertError($file, $line, 'The "enum" keyword is not present in PHP version 8.0 or earlier');

        $file = $this->sniffFile(__FILE__, '8.1');
        $this->assertNoViolation($file, $line);
    }

    /**
     * Data provider.
     *
     * @see testEnum()
     *
     * @return array
     */
    public static function dataEnum()
    {
        return [
            [247],
            [252],
        ];
    }

    /**
     * Test against false positives for enums.
     *
     * @dataProvider dataEnumNoFalsePositives
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testEnumNoFalsePositives($line)
    {
        $file = $this->sniffFile(__FILE__, '8.1');
        $this->assertNoViolation($file, $line);
    }

    /**
     * Data provider.
     *
     * @see testEnumNoFalsePositives()
     *
     * @return array
     */
    public static function dataEnumNoFalsePositives()
    {
        $data = [];

        for ($i = 225; $i <= 244; $i++) {
            $data[] = [$i];
        }

        return $data;
    }

    /**
     * testHaltCompiler
     *
     * @return void
     */
    public function testHaltCompiler()
    {
        /*
         * Usage of `__halt_compiler()` cannot be tested on its own token as the compiler
         * will be halted...
         * So testing that any violations created *after* the compiler is halted will
         * not be reported.
         */
        $file = $this->sniffFile(__FILE__, '5.2');
        $this->assertNoViolation($file, 260);
    }


    /**
     * Verify no notices are thrown at all.
     *
     * @return void
     */
    public function testNoViolationsInFileOnValidVersion()
    {
        $file = $this->sniffFile(__FILE__, '99.0'); // High version beyond newest addition.
        $this->assertNoViolation($file);
    }
}
