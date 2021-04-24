<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Tasks
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Tasks\Controller;

use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\System\MimeType;

/**
 * Search class.
 *
 * @package Modules\Tasks
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
final class SearchController extends Controller
{
    /**
     * Api method to search for tags
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function searchTags(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        // join tags with tag l11n
        // join tags with tasks
        // return where tag l11n matches X

        $tags = [];

        // @todo: probably cleanup return for link generation + sort by best match
        $response->header->set('Content-Type', MimeType::M_JSON . '; charset=utf-8', true);

        $response->set($request->uri->__toString(), $tags);
    }
}
