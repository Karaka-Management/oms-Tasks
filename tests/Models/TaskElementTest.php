<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Tasks\tests\Models;

use Modules\Admin\Models\NullAccount;
use Modules\Admin\Models\NullGroup;
use Modules\Tasks\Models\TaskElement;
use Modules\Tasks\Models\TaskPriority;
use Modules\Tasks\Models\TaskStatus;

/**
 * @internal
 */
final class TaskElementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testDefault() : void
    {
        $task = new TaskElement();

        self::assertEquals(0, $task->getId());
        self::assertEquals(0, $task->createdBy->getId());
        self::assertEquals((new \DateTime('now'))->format('Y-m-d'), $task->createdAt->format('Y-m-d'));
        self::assertEquals((new \DateTime('now'))->modify('+1 day')->format('Y-m-d'), $task->due->format('Y-m-d'));
        self::assertEquals(TaskStatus::OPEN, $task->getStatus());
        self::assertEquals('', $task->description);
        self::assertEquals('', $task->descriptionRaw);
        self::assertEquals([], $task->getTo());
        self::assertEquals([], $task->getCC());
        self::assertEquals(0, $task->task);
        self::assertEquals(TaskPriority::NONE, $task->getPriority());
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testCreatedByInputOutput() : void
    {
        $task = new TaskElement();

        $task->createdBy = new NullAccount(1);
        self::assertEquals(1, $task->createdBy->getId());
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testDueInputOutput() : void
    {
        $task = new TaskElement();

        $task->due = ($date = new \DateTime('2000-05-07'));
        self::assertEquals($date->format('Y-m-d'), $task->due->format('Y-m-d'));
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testStatusInputOutput() : void
    {
        $task = new TaskElement();

        $task->setStatus(TaskStatus::DONE);
        self::assertEquals(TaskStatus::DONE, $task->getStatus());
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testPriorityInputOutput() : void
    {
        $task = new TaskElement();

        $task->setPriority(TaskPriority::MEDIUM);
        self::assertEquals(TaskPriority::MEDIUM, $task->getPriority());
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testDescriptionInputOutput() : void
    {
        $task = new TaskElement();

        $task->description = 'Description';
        self::assertEquals('Description', $task->description);
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testDescriptionRawInputOutput() : void
    {
        $task = new TaskElement();

        $task->descriptionRaw = 'DescriptionRaw';
        self::assertEquals('DescriptionRaw', $task->descriptionRaw);
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testTaskInputOutput() : void
    {
        $task = new TaskElement();

        $task->task = 2;
        self::assertEquals(2, $task->task);
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testAccountToInputOutput() : void
    {
        $task = new TaskElement();

        $task->addTo(new NullAccount(3));
        $task->addTo(new NullAccount(3)); // test duplicate
        self::assertTrue($task->isToAccount(3));
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testGroupToInputOutput() : void
    {
        $task = new TaskElement();

        $task->addGroupTo(new NullGroup(4));
        $task->addGroupTo(new NullGroup(4)); // test duplicate
        self::assertTrue($task->isToGroup(4));
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testAccountCCInputOutput() : void
    {
        $task = new TaskElement();

        $task->addCC(new NullAccount(5));
        $task->addCC(new NullAccount(5)); // test duplicate
        self::assertTrue($task->isCCAccount(5));
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testGroupCCInputOutput() : void
    {
        $task = new TaskElement();

        $task->addGroupCC(new NullGroup(6));
        $task->addGroupCC(new NullGroup(6)); // test duplicate
        self::assertTrue($task->isCCGroup(6));
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testInvalidAccountTo() : void
    {
        $task = new TaskElement();

        self::assertFalse($task->isToAccount(7));
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testInvalidAccountCC() : void
    {
        $task = new TaskElement();

        self::assertFalse($task->isCCAccount(8));
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testInvalidGroupTo() : void
    {
        $task = new TaskElement();

        self::assertFalse($task->isToGroup(9));
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testInvalidGroupCC() : void
    {
        $task = new TaskElement();

        self::assertFalse($task->isCCGroup(10));
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testInvalidStatus() : void
    {
        $this->expectException(\phpOMS\Stdlib\Base\Exception\InvalidEnumValue::class);

        $task = new TaskElement();
        $task->setStatus(9999);
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testInvalidPriority() : void
    {
        $this->expectException(\phpOMS\Stdlib\Base\Exception\InvalidEnumValue::class);

        $task = new TaskElement();
        $task->setPriority(9999);
    }
}
