<?php
/**
 * PHPCompatibility, an external standard for PHP_CodeSniffer.
 *
 * @package   PHPCompatibility
 * @copyright 2012-2020 PHPCompatibility Contributors
 * @license   https://opensource.org/licenses/LGPL-3.0 LGPL3
 * @link      https://github.com/PHPCompatibility/PHPCompatibility
 */

namespace PHPCompatibility\Tests\FunctionDeclarations;

use PHPCompatibility\Tests\BaseSniffTestCase;

/**
 * Test the RemovedOptionalBeforeRequiredParam sniff.
 *
 * @group removedOptionalBeforeRequiredParam
 * @group functiondeclarations
 *
 * @covers \PHPCompatibility\Sniffs\FunctionDeclarations\RemovedOptionalBeforeRequiredParamSniff
 *
 * @since 10.0.0
 */
class RemovedOptionalBeforeRequiredParamUnitTest extends BaseSniffTestCase
{

    /**
     * Base message for the PHP 8.0 deprecation.
     *
     * @var string
     */
    const PHP80_MSG = 'Declaring an optional parameter before a required parameter is deprecated since PHP 8.0.';

    /**
     * Base message for the PHP 8.1 deprecation.
     *
     * @var string
     */
    const PHP81_MSG = 'Declaring an optional parameter with a nullable type before a required parameter is soft deprecated since PHP 8.0 and hard deprecated since PHP 8.1';

    /**
     * Base message for the PHP 8.3 deprecation.
     *
     * @var string
     */
    const PHP83_MSG = 'Declaring an optional parameter with a null stand-alone type or a union type including null before a required parameter is soft deprecated since PHP 8.0 and hard deprecated since PHP 8.3';

    /**
     * Base message for the PHP 8.4 deprecation.
     *
     * @var string
     */
    const PHP84_MSG = 'Declaring an optional parameter with a non-nullable type and a null default value before a required parameter is deprecated since PHP 8.4';

    /**
     * Verify that the sniff throws a warning for optional parameters before required.
     *
     * @dataProvider dataRemovedOptionalBeforeRequiredParam80
     *
     * @param int $line The line number where a warning is expected.
     *
     * @return void
     */
    public function testRemovedOptionalBeforeRequiredParam80($line)
    {
        $file = $this->sniffFile(__FILE__, '8.0');
        $this->assertWarning($file, $line, self::PHP80_MSG);
    }

    /**
     * Data provider.
     *
     * @see testRemovedOptionalBeforeRequiredParam80()
     *
     * @return array
     */
    public static function dataRemovedOptionalBeforeRequiredParam80()
    {
        return [
            [13],
            [14],
            [16],
            [17],
            [20],
            [31],
            [38],
            [51],
            [57],
            [58],
            [59],
            [126],
        ];
    }


    /**
     * Verify the sniff does not throw false positives for valid code.
     *
     * @dataProvider dataNoFalsePositives80
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testNoFalsePositives80($line)
    {
        $file = $this->sniffFile(__FILE__, '8.0');
        $this->assertNoViolation($file, $line);
    }

    /**
     * Data provider.
     *
     * @see testNoFalsePositives80()
     *
     * @return array
     */
    public static function dataNoFalsePositives80()
    {
        $cases = [];
        // No errors expected on the first 9 lines.
        for ($line = 1; $line <= 9; $line++) {
            $cases['line ' . $line] = [$line];
        }

        // Don't error on variadic parameters.
        $cases['line 23 - variadic params'] = [23];
        $cases['line 24 - variadic params'] = [24];
        $cases['line 26 - variadic params'] = [26];

        // Constructor property promotion - valid example.
        $cases['line 46 - constructor property promotion'] = [46];

        // Constant expression containing null in default value for optional param.
        $cases['line 52 - constant expression'] = [52];

        // New in initializers tests.
        $cases['line 60 - new in initializers'] = [60];
        $cases['line 61 - new in initializers'] = [61];

        // Not deprecated, false positive checks for PHP 8.1 deprecation.
        $cases['line 67 - related to PHP 8.1 deprecation'] = [67];
        $cases['line 68 - related to PHP 8.1 deprecation'] = [68];

        // Deprecated, but only flagged as of PHP 8.1.
        $cases['line 71 - deprecated in PHP 8.1']  = [71];
        $cases['line 75 - deprecated in PHP 8.1']  = [75];
        $cases['line 125 - deprecated in PHP 8.1'] = [125];

        // Not deprecated, false positive checks for PHP 8.3 deprecation.
        $cases['line 81 - related to PHP 8.3 deprecation'] = [81];
        $cases['line 82 - related to PHP 8.3 deprecation'] = [82];
        $cases['line 83 - related to PHP 8.3 deprecation'] = [83];

        // Deprecated, but only flagged as of PHP 8.3.
        $cases['line 86 - deprecated in PHP 8.3']  = [86];
        $cases['line 87 - deprecated in PHP 8.3']  = [87];
        $cases['line 88 - deprecated in PHP 8.3']  = [88];
        $cases['line 89 - deprecated in PHP 8.3']  = [89];
        $cases['line 90 - deprecated in PHP 8.3']  = [90];
        $cases['line 91 - deprecated in PHP 8.3']  = [91];
        $cases['line 95 - deprecated in PHP 8.3']  = [95];
        $cases['line 124 - deprecated in PHP 8.3'] = [124];

        // Not deprecated, false positive checks for PHP 8.4 deprecation.
        $cases['line 102 - related to PHP 8.4 deprecation'] = [102];
        $cases['line 103 - related to PHP 8.4 deprecation'] = [103];
        $cases['line 104 - related to PHP 8.4 deprecation'] = [104];
        $cases['line 105 - related to PHP 8.4 deprecation'] = [105];
        $cases['line 107 - related to PHP 8.4 deprecation'] = [107];
        $cases['line 113 - related to PHP 8.4 deprecation'] = [113];
        $cases['line 114 - related to PHP 8.4 deprecation'] = [114];

        // Deprecated as of PHP 8.4.
        $cases['line 111 - deprecated in PHP 8.4'] = [111];
        $cases['line 112 - deprecated in PHP 8.4'] = [112];
        $cases['line 116 - deprecated in PHP 8.4'] = [116];
        $cases['line 117 - deprecated in PHP 8.4'] = [117];
        $cases['line 118 - deprecated in PHP 8.4'] = [118];
        $cases['line 123 - deprecated in PHP 8.4'] = [123];

        // Add parse error test case.
        $cases['line 131 - parse error'] = [131];

        return $cases;
    }


    /**
     * Verify that the sniff throws a warning for optional parameters with a nullable type before required.
     *
     * @dataProvider dataRemovedOptionalBeforeRequiredParam81
     *
     * @param int    $line The line number where a warning is expected.
     * @param string $msg  The expected warning message.
     *
     * @return void
     */
    public function testRemovedOptionalBeforeRequiredParam81($line, $msg = self::PHP80_MSG)
    {
        $file = $this->sniffFile(__FILE__, '8.1');
        $this->assertWarning($file, $line, $msg);
    }

    /**
     * Data provider.
     *
     * @see testRemovedOptionalBeforeRequiredParam81()
     *
     * @return array
     */
    public static function dataRemovedOptionalBeforeRequiredParam81()
    {
        $data   = self::dataRemovedOptionalBeforeRequiredParam80();
        $data[] = [71, self::PHP81_MSG];
        $data[] = [75, self::PHP81_MSG];
        $data[] = [125, self::PHP81_MSG];
        return $data;
    }


    /**
     * Verify the sniff does not throw false positives for valid code.
     *
     * @dataProvider dataNoFalsePositives81
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testNoFalsePositives81($line)
    {
        $file = $this->sniffFile(__FILE__, '8.1');
        $this->assertNoViolation($file, $line);
    }

    /**
     * Data provider.
     *
     * @see testNoFalsePositives81()
     *
     * @return array
     */
    public static function dataNoFalsePositives81()
    {
        $cases = self::dataNoFalsePositives80();
        unset(
            $cases['line 71 - deprecated in PHP 8.1'],
            $cases['line 75 - deprecated in PHP 8.1'],
            $cases['line 125 - deprecated in PHP 8.1']
        );

        return $cases;
    }


    /**
     * Verify that the sniff throws a warning for optional parameters with a union type which includes null before required.
     *
     * @dataProvider dataRemovedOptionalBeforeRequiredParam83
     *
     * @param int    $line The line number where a warning is expected.
     * @param string $msg  The expected warning message.
     *
     * @return void
     */
    public function testRemovedOptionalBeforeRequiredParam83($line, $msg = self::PHP80_MSG)
    {
        $file = $this->sniffFile(__FILE__, '8.3');
        $this->assertWarning($file, $line, $msg);
    }

    /**
     * Data provider.
     *
     * @see testRemovedOptionalBeforeRequiredParam83()
     *
     * @return array
     */
    public static function dataRemovedOptionalBeforeRequiredParam83()
    {
        $data   = self::dataRemovedOptionalBeforeRequiredParam81();
        $data[] = [86, self::PHP83_MSG];
        $data[] = [87, self::PHP83_MSG];
        $data[] = [88, self::PHP83_MSG];
        $data[] = [89, self::PHP83_MSG];
        $data[] = [90, self::PHP83_MSG];
        $data[] = [91, self::PHP83_MSG];
        $data[] = [95, self::PHP83_MSG];
        $data[] = [124, self::PHP83_MSG];
        return $data;
    }


    /**
     * Verify the sniff does not throw false positives for valid code.
     *
     * @dataProvider dataNoFalsePositives83
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testNoFalsePositives83($line)
    {
        $file = $this->sniffFile(__FILE__, '8.3');
        $this->assertNoViolation($file, $line);
    }

    /**
     * Data provider.
     *
     * @see testNoFalsePositives83()
     *
     * @return array
     */
    public static function dataNoFalsePositives83()
    {
        $cases = self::dataNoFalsePositives81();
        unset(
            $cases['line 86 - deprecated in PHP 8.3'],
            $cases['line 87 - deprecated in PHP 8.3'],
            $cases['line 88 - deprecated in PHP 8.3'],
            $cases['line 89 - deprecated in PHP 8.3'],
            $cases['line 90 - deprecated in PHP 8.3'],
            $cases['line 91 - deprecated in PHP 8.3'],
            $cases['line 95 - deprecated in PHP 8.3'],
            $cases['line 124 - deprecated in PHP 8.3']
        );

        return $cases;
    }


    /**
     * Verify that the sniff throws a warning for optional parameters with a union type which includes null before required.
     *
     * @dataProvider dataRemovedOptionalBeforeRequiredParam84
     *
     * @param int    $line The line number where a warning is expected.
     * @param string $msg  The expected warning message.
     *
     * @return void
     */
    public function testRemovedOptionalBeforeRequiredParam84($line, $msg = self::PHP80_MSG)
    {
        $file = $this->sniffFile(__FILE__, '8.4');
        $this->assertWarning($file, $line, $msg);
    }

    /**
     * Data provider.
     *
     * @see testRemovedOptionalBeforeRequiredParam84()
     *
     * @return array
     */
    public static function dataRemovedOptionalBeforeRequiredParam84()
    {
        $data   = self::dataRemovedOptionalBeforeRequiredParam83();
        $data[] = [67, self::PHP84_MSG];
        $data[] = [83, self::PHP84_MSG];
        $data[] = [111, self::PHP84_MSG];
        $data[] = [112, self::PHP84_MSG];
        $data[] = [116, self::PHP84_MSG];
        $data[] = [117, self::PHP84_MSG];
        $data[] = [118, self::PHP84_MSG];
        $data[] = [123, self::PHP84_MSG];
        return $data;
    }


    /**
     * Verify the sniff does not throw false positives for valid code.
     *
     * @dataProvider dataNoFalsePositives84
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testNoFalsePositives84($line)
    {
        $file = $this->sniffFile(__FILE__, '8.4');
        $this->assertNoViolation($file, $line);
    }

    /**
     * Data provider.
     *
     * @see testNoFalsePositives84()
     *
     * @return array
     */
    public static function dataNoFalsePositives84()
    {
        $cases = self::dataNoFalsePositives83();
        unset(
            $cases['line 67 - related to PHP 8.1 deprecation'],
            $cases['line 83 - related to PHP 8.3 deprecation'],
            $cases['line 111 - deprecated in PHP 8.4'],
            $cases['line 112 - deprecated in PHP 8.4'],
            $cases['line 116 - deprecated in PHP 8.4'],
            $cases['line 117 - deprecated in PHP 8.4'],
            $cases['line 118 - deprecated in PHP 8.4'],
            $cases['line 123 - deprecated in PHP 8.4']
        );

        return $cases;
    }


    /**
     * Verify no notices are thrown at all.
     *
     * @return void
     */
    public function testNoViolationsInFileOnValidVersion()
    {
        $file = $this->sniffFile(__FILE__, '7.4');
        $this->assertNoViolation($file);
    }
}
