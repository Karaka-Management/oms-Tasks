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

namespace Modules\Task\tests\Controller;

use Model\CoreSettings;
use Modules\Admin\Models\AccountPermission;
use Modules\Tasks\Models\TaskPriority;
use Modules\Tasks\Models\TaskStatus;
use phpOMS\Account\Account;
use phpOMS\Account\AccountManager;
use phpOMS\Account\PermissionType;
use phpOMS\Application\ApplicationAbstract;
use phpOMS\Dispatcher\Dispatcher;
use phpOMS\Event\EventManager;
use phpOMS\Localization\L11nManager;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Module\ModuleManager;
use phpOMS\Router\WebRouter;
use phpOMS\System\MimeType;
use phpOMS\Uri\HttpUri;
use phpOMS\Utils\TestUtils;

/**
 * @internal
 */
final class ControllerTest extends \PHPUnit\Framework\TestCase
{
    protected $app    = null;

    protected $module = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->app = new class() extends ApplicationAbstract
        {
            protected string $appName = 'Api';
        };

        $this->app->dbPool         = $GLOBALS['dbpool'];
        $this->app->unitId         = 1;
        $this->app->accountManager = new AccountManager($GLOBALS['session']);
        $this->app->appSettings    = new CoreSettings();
        $this->app->moduleManager  = new ModuleManager($this->app, __DIR__ . '/../../../Modules/');
        $this->app->dispatcher     = new Dispatcher($this->app);
        $this->app->eventManager   = new EventManager($this->app->dispatcher);
        $this->app->l11nManager    = new L11nManager();
        $this->app->eventManager->importFromFile(__DIR__ . '/../../../Web/Api/Hooks.php');

        $account = new Account();
        TestUtils::setMember($account, 'id', 1);

        $permission       = new AccountPermission();
        $permission->unit = 1;
        $permission->app  = 2;
        $permission->setPermission(
            PermissionType::READ
            | PermissionType::CREATE
            | PermissionType::MODIFY
            | PermissionType::DELETE
            | PermissionType::PERMISSION
        );

        $account->addPermission($permission);

        $this->app->accountManager->add($account);
        $this->app->router = new WebRouter();

        $this->module = $this->app->moduleManager->get('Tasks');

        TestUtils::setMember($this->module, 'app', $this->app);
    }

    /**
     * @covers \Modules\Tasks\Controller\ApiController
     * @group module
     */
    public function testCreateTask() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;
        $request->setData('title', 'Controller Test Title');
        $request->setData('plain', 'Controller Test Description');
        $request->setData('due', (new \DateTime())->format('Y-m-d H:i:s'));
        $request->setData('tags', '[{"title": "TestTitle", "color": "#f0f", "language": "en"}, {"id": 1}]');

        if (!\is_file(__DIR__ . '/test_tmp.md')) {
            \copy(__DIR__ . '/test.md', __DIR__ . '/test_tmp.md');
        }

        TestUtils::setMember($request, 'files', [
            'file1' => [
                'name'     => 'test.md',
                'type'     => MimeType::M_TXT,
                'tmp_name' => __DIR__ . '/test_tmp.md',
                'error'    => \UPLOAD_ERR_OK,
                'size'     => \filesize(__DIR__ . '/test_tmp.md'),
            ],
        ]);

        $request->setData('media', \json_encode([1]));

        $this->module->apiTaskCreate($request, $response);

        self::assertEquals('Controller Test Title', $response->getDataArray('')['response']->title);
        self::assertGreaterThan(0, $response->getDataArray('')['response']->id);
    }

    /**
     * @covers \Modules\Tasks\Controller\ApiController
     * @group module
     */
    public function testApiTaskGet() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;
        $request->setData('id', '1');

        $this->module->apiTaskGet($request, $response);

        self::assertEquals(1, $response->getDataArray('')['response']->id);
    }

    /**
     * @covers \Modules\Tasks\Controller\ApiController
     * @group module
     */
    public function testApiTaskSet() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;
        $request->setData('id', 1);
        $request->setData('title', 'New Title');
        $request->setData('description', 'New Content here');

        $this->module->apiTaskSet($request, $response);
        $this->module->apiTaskGet($request, $response);

        self::assertEquals('New Title', $response->getDataArray('')['response']->title);
    }

    /**
     * @covers \Modules\Tasks\Controller\ApiController
     * @group module
     */
    public function testCreateTaskElement() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;
        $request->setData('due', (new \DateTime())->format('Y-m-d H:i:s'));
        $request->setData('priority', TaskPriority::HIGH);
        $request->setData('status', TaskStatus::DONE);
        $request->setData('task', 1);
        $request->setData('plain', 'Controller Test');

        if (!\is_file(__DIR__ . '/test_tmp.md')) {
            \copy(__DIR__ . '/test.md', __DIR__ . '/test_tmp.md');
        }

        TestUtils::setMember($request, 'files', [
            'file1' => [
                'name'     => 'test.md',
                'type'     => MimeType::M_TXT,
                'tmp_name' => __DIR__ . '/test_tmp.md',
                'error'    => \UPLOAD_ERR_OK,
                'size'     => \filesize(__DIR__ . '/test_tmp.md'),
            ],
        ]);

        $request->setData('media', \json_encode([1]));

        $this->module->apiTaskElementCreate($request, $response);

        self::assertEquals('Controller Test', $response->getDataArray('')['response']->descriptionRaw);
        self::assertGreaterThan(0, $response->getDataArray('')['response']->id);
    }

    /**
     * @covers \Modules\Tasks\Controller\ApiController
     * @group module
     */
    public function testApiTaskElementGet() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;
        $request->setData('id', '1');

        $this->module->apiTaskElementGet($request, $response);

        self::assertEquals(1, $response->getDataArray('')['response']->id);
    }

    /**
     * @covers \Modules\Tasks\Controller\ApiController
     * @group module
     */
    public function testApiTaskElementSet() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;
        $request->setData('id', 1);
        $request->setData('plain', 'This is a changed description');

        $this->module->apiTaskElementSet($request, $response);
        $this->module->apiTaskElementGet($request, $response);

        self::assertEquals('This is a changed description', $response->getDataArray('')['response']->descriptionRaw);
    }

    /**
     * @covers \Modules\Tasks\Controller\ApiController
     * @group module
     */
    public function testInvalidTaskCreate() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;
        $request->setData('plain', 'Controller Test Description');
        $request->setData('due', (new \DateTime())->format('Y-m-d H:i:s'));

        $this->module->apiTaskCreate($request, $response);
        self::assertEquals(RequestStatusCode::R_400, $response->header->status);
    }

    /**
     * @covers \Modules\Tasks\Controller\ApiController
     * @group module
     */
    public function testInvalidTaskElementCreate() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;
        $request->setData('due', (new \DateTime())->format('Y-m-d H:i:s'));
        $request->setData('priority', TaskPriority::HIGH);
        $request->setData('status', TaskStatus::DONE);
        $request->setData('plain', 'Controller Test');

        $this->module->apiTaskElementCreate($request, $response);
        self::assertEquals(RequestStatusCode::R_400, $response->header->status);
    }

    public function testInvalidapiTaskAttributeValueL11nCreate() : void
    {
        $response = new HttpResponse();
        $request  = new HttpRequest(new HttpUri(''));

        $request->header->account = 1;
        $this->module->apiTaskAttributeValueL11nCreate($request, $response);
        self::assertEquals(RequestStatusCode::R_400, $response->header->status);
    }
}
