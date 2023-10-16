<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Tasks\tests\Models;

use Modules\Tasks\Models\NullTaskElement;

/**
 * @internal
 */
final class NullTaskElementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Tasks\Models\NullTaskElement
     * @group module
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Tasks\Models\TaskElement', new NullTaskElement());
    }

    /**
     * @covers Modules\Tasks\Models\NullTaskElement
     * @group module
     */
    public function testId() : void
    {
        $null = new NullTaskElement(2);
        self::assertEquals(2, $null->id);
    }

    /**
     * @covers Modules\Tasks\Models\NullTaskElement
     * @group module
     */
    public function testJsonSerialize() : void
    {
        $null = new NullTaskElement(2);
        self::assertEquals(['id' => 2], $null);
    }
}
