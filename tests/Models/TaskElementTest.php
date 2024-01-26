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
    private TaskElement $element;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->element = new TaskElement();
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testDefault() : void
    {
        self::assertEquals(0, $this->element->id);
        self::assertEquals(0, $this->element->createdBy->id);
        self::assertEquals((new \DateTime('now'))->format('Y-m-d'), $this->element->createdAt->format('Y-m-d'));
        self::assertEquals((new \DateTime('now'))->modify('+1 day')->format('Y-m-d'), $this->element->due->format('Y-m-d'));
        self::assertEquals(TaskStatus::OPEN, $this->element->status);
        self::assertEquals('', $this->element->description);
        self::assertEquals('', $this->element->descriptionRaw);
        self::assertEquals([], $this->element->getTo());
        self::assertEquals([], $this->element->getCC());
        self::assertEquals(0, $this->element->task);
        self::assertEquals(TaskPriority::NONE, $this->element->priority);
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testCreatedByInputOutput() : void
    {
        $this->element->createdBy = new NullAccount(1);
        self::assertEquals(1, $this->element->createdBy->id);
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testDueInputOutput() : void
    {
        $this->element->due = ($date = new \DateTime('2000-05-07'));
        self::assertEquals($date->format('Y-m-d'), $this->element->due->format('Y-m-d'));
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testDescriptionInputOutput() : void
    {
        $this->element->description = 'Description';
        self::assertEquals('Description', $this->element->description);
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testDescriptionRawInputOutput() : void
    {
        $this->element->descriptionRaw = 'DescriptionRaw';
        self::assertEquals('DescriptionRaw', $this->element->descriptionRaw);
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testTaskInputOutput() : void
    {
        $this->element->task = 2;
        self::assertEquals(2, $this->element->task);
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testAccountToInputOutput() : void
    {
        $this->element->addTo(new NullAccount(3));
        $this->element->addTo(new NullAccount(3)); // test duplicate
        self::assertTrue($this->element->isToAccount(3));

        $this->element->addTo(new NullGroup(4));
        self::assertCount(2, $this->element->getTo());
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testGroupToInputOutput() : void
    {
        $this->element->addGroupTo(new NullGroup(4));
        $this->element->addGroupTo(new NullGroup(4)); // test duplicate
        self::assertTrue($this->element->isToGroup(4));

        $this->element->addTo(new NullAccount(3));
        self::assertCount(2, $this->element->getTo());
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testAccountCCInputOutput() : void
    {
        $this->element->addCC(new NullAccount(5));
        $this->element->addCC(new NullAccount(5)); // test duplicate
        self::assertTrue($this->element->isCCAccount(5));

        $this->element->addCC(new NullGroup(6));
        self::assertCount(2, $this->element->getCC());
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testGroupCCInputOutput() : void
    {
        $this->element->addGroupCC(new NullGroup(6));
        $this->element->addGroupCC(new NullGroup(6)); // test duplicate
        self::assertTrue($this->element->isCCGroup(6));

        $this->element->addCC(new NullAccount(5));
        self::assertCount(2, $this->element->getCC());
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testInvalidAccountTo() : void
    {
        self::assertFalse($this->element->isToAccount(7));
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testInvalidAccountCC() : void
    {
        self::assertFalse($this->element->isCCAccount(8));
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testInvalidGroupTo() : void
    {
        self::assertFalse($this->element->isToGroup(9));
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testInvalidGroupCC() : void
    {
        self::assertFalse($this->element->isCCGroup(10));
    }

    /**
     * @covers Modules\Tasks\Models\TaskElement
     * @group module
     */
    public function testSerialize() : void
    {
        $this->element->task           = 2;
        $this->element->description    = 'Test';
        $this->element->descriptionRaw = 'TestRaw';
        $this->element->status         = TaskStatus::DONE;

        $serialized = $this->element->jsonSerialize();
        unset($serialized['createdAt']);
        unset($serialized['createdBy']);
        unset($serialized['due']);

        self::assertEquals(
            [
                'id'             => 0,
                'task'           => 2,
                'description'    => 'Test',
                'descriptionRaw' => 'TestRaw',
                'status'         => TaskStatus::DONE,
                'to'             => [],
                'cc'             => [],
            ],
            $serialized
        );
    }
}
