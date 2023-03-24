<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Tasks
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Tasks\Theme\Backend\Components\Tasks;

use phpOMS\Localization\L11nManager;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Views\View;

/**
 * Component view.
 *
 * @package Modules\Tasks
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 * @codeCoverageIgnore
 */
class ListView extends View
{
    /**
     * Tasks
     *
     * @var \Modules\Tasks\Models\Task[]
     * @since 1.0.0
     */
    protected array $tasks = [];

    /**
     * {@inheritdoc}
     */
    public function __construct(L11nManager $l11n, RequestAbstract $request, ResponseAbstract $response)
    {
        parent::__construct($l11n, $request, $response);
        $this->setTemplate('/Modules/Tasks/Theme/Backend/Components/Tasks/list');
    }

    /**
     * {@inheritdoc}
     */
    public function render(mixed ...$data) : string
    {
        /** @var array{0: \Modules\Tasks\Models\Task[]} $data */
        $this->tasks = $data[0];
        return parent::render();
    }
}
