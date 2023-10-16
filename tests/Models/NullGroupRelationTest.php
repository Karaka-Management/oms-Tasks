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

use Modules\Tasks\Models\NullGroupRelation;

/**
 * @internal
 */
final class NullGroupRelationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Tasks\Models\NullGroupRelation
     * @group framework
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Tasks\Models\GroupRelation', new NullGroupRelation());
    }

    /**
     * @covers Modules\Tasks\Models\NullGroupRelation
     * @group framework
     */
    public function testId() : void
    {
        $null = new NullGroupRelation(2);
        self::assertEquals(2, $null->getId());
    }

    /**
     * @covers Modules\Tasks\Models\NullGroupRelation
     * @group framework
     */
    public function testJsonSerialize() : void
    {
        $null = new NullGroupRelation(2);
        self::assertEquals(['id' => 2], $null);
    }
}
