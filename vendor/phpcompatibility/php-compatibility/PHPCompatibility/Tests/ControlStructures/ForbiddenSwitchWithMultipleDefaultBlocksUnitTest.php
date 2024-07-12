<?php
/**
 * PHPCompatibility, an external standard for PHP_CodeSniffer.
 *
 * @package   PHPCompatibility
 * @copyright 2012-2020 PHPCompatibility Contributors
 * @license   https://opensource.org/licenses/LGPL-3.0 LGPL3
 * @link      https://github.com/PHPCompatibility/PHPCompatibility
 */

namespace PHPCompatibility\Tests\ControlStructures;

use PHPCompatibility\Tests\BaseSniffTestCase;

/**
 * Test the ForbiddenSwitchWithMultipleDefaultBlocks sniff.
 *
 * @group forbiddenSwitchWithMultipleDefaultBlocks
 * @group controlStructures
 *
 * @covers \PHPCompatibility\Sniffs\ControlStructures\ForbiddenSwitchWithMultipleDefaultBlocksSniff
 *
 * @since 7.0.0
 */
class ForbiddenSwitchWithMultipleDefaultBlocksUnitTest extends BaseSniffTestCase
{

    /**
     * testForbiddenSwitchWithMultipleDefaultBlocks
     *
     * @dataProvider dataForbiddenSwitchWithMultipleDefaultBlocks
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testForbiddenSwitchWithMultipleDefaultBlocks($line)
    {
        $file = $this->sniffFile(__FILE__, '7.0');
        $this->assertError($file, $line, 'Switch statements can not have multiple default blocks since PHP 7.0');
    }

    /**
     * Data provider.
     *
     * @see testForbiddenSwitchWithMultipleDefaultBlocks()
     *
     * @return array
     */
    public static function dataForbiddenSwitchWithMultipleDefaultBlocks()
    {
        return [
            [3],
            [47],
            [56],
            [67],
            [78],
            [90],
            [106],
        ];
    }


    /**
     * testNoFalsePositives
     *
     * @dataProvider dataNoFalsePositives
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testNoFalsePositives($line)
    {
        $file = $this->sniffFile(__FILE__, '7.0');
        $this->assertNoViolation($file, $line);
    }

    /**
     * Data provider.
     *
     * @see testNoFalsePositives()
     *
     * @return array
     */
    public static function dataNoFalsePositives()
    {
        return [
            [14],
            [23],
            [43],
            [120],
            [134], // Live coding.
        ];
    }


    /**
     * Verify no notices are thrown at all.
     *
     * @return void
     */
    public function testNoViolationsInFileOnValidVersion()
    {
        $file = $this->sniffFile(__FILE__, '5.6');
        $this->assertNoViolation($file);
    }
}
