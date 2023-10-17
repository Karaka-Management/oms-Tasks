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

use Modules\Tasks\Models\NullTaskAttributeType;

/**
 * @internal
 */
final class NullTaskAttributeTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Tasks\Models\NullTaskAttributeType
     * @group module
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Tasks\Models\TaskAttributeType', new NullTaskAttributeType());
    }

    /**
     * @covers Modules\Tasks\Models\NullTaskAttributeType
     * @group module
     */
    public function testId() : void
    {
        $null = new NullTaskAttributeType(2);
        self::assertEquals(2, $null->id);
    }

    /**
     * @covers Modules\Tasks\Models\NullTaskAttributeType
     * @group module
     */
    public function testJsonSerialize() : void
    {
        $null = new NullTaskAttributeType(2);
        self::assertEquals(['id' => 2], $null->jsonSerialize());
    }
}