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

use Modules\Tasks\Models\NullTaskAttribute;

/**
 * @internal
 */
final class NullTaskAttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Tasks\Models\NullTaskAttribute
     * @group framework
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Tasks\Models\TaskAttribute', new NullTaskAttribute());
    }

    /**
     * @covers Modules\Tasks\Models\NullTaskAttribute
     * @group framework
     */
    public function testId() : void
    {
        $null = new NullTaskAttribute(2);
        self::assertEquals(2, $null->getId());
    }

    /**
     * @covers Modules\Tasks\Models\NullTaskAttribute
     * @group framework
     */
    public function testJsonSerialize() : void
    {
        $null = new NullTaskAttribute(2);
        self::assertEquals(['id' => 2], $null);
    }
}
