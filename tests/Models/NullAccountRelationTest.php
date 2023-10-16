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

use Modules\Tasks\Models\NullAccountRelation;

/**
 * @internal
 */
final class NullAccountRelationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Tasks\Models\NullAccountRelation
     * @group framework
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Tasks\Models\AccountRelation', new NullAccountRelation());
    }

    /**
     * @covers Modules\Tasks\Models\NullAccountRelation
     * @group framework
     */
    public function testId() : void
    {
        $null = new NullAccountRelation(2);
        self::assertEquals(2, $null->getId());
    }

    /**
     * @covers Modules\Tasks\Models\NullAccountRelation
     * @group framework
     */
    public function testJsonSerialize() : void
    {
        $null = new NullAccountRelation(2);
        self::assertEquals(['id' => 2], $null);
    }
}
