<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Tasks\Admin
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Tasks\Admin;

use phpOMS\Application\ApplicationAbstract;
use phpOMS\Config\SettingsInterface;
use phpOMS\Message\Http\HttpRequest;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Module\InstallerAbstract;
use phpOMS\Module\ModuleInfo;

/**
 * Installer class.
 *
 * @package Modules\Tasks\Admin
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class Installer extends InstallerAbstract
{
    /**
     * Path of the file
     *
     * @var string
     * @since 1.0.0
     */
    public const PATH = __DIR__;

    /**
     * {@inheritdoc}
     */
    public static function install(ApplicationAbstract $app, ModuleInfo $info, SettingsInterface $cfgHandler) : void
    {
        parent::install($app, $info, $cfgHandler);

        /* Attributes */
        $fileContent = \file_get_contents(__DIR__ . '/Install/attributes.json');
        if ($fileContent === false) {
            return;
        }

        /** @var array $attributes */
        $attributes = \json_decode($fileContent, true);
        $attrTypes  = self::createTaskAttributeTypes($app, $attributes);
        $attrValues = self::createTaskAttributeValues($app, $attrTypes, $attributes);
    }

    /**
     * Install default attribute types
     *
     * @param ApplicationAbstract $app        Application
     * @param array               $attributes Attribute definition
     *
     * @return array
     *
     * @since 1.0.0
     */
    private static function createTaskAttributeTypes(ApplicationAbstract $app, array $attributes) : array
    {
        /** @var array<string, array> $taskAttrType */
        $taskAttrType = [];

        /** @var \Modules\Tasks\Controller\ApiAttributeController $module */
        $module = $app->moduleManager->get('Tasks', 'ApiAttribute');

        /** @var array $attribute */
        foreach ($attributes as $attribute) {
            $response = new HttpResponse();
            $request  = new HttpRequest();

            $request->header->account = 1;
            $request->setData('name', $attribute['name'] ?? '');
            $request->setData('title', \reset($attribute['l11n']));
            $request->setData('language', \array_keys($attribute['l11n'])[0] ?? 'en');
            $request->setData('repeatable', $attribute['repeatable'] ?? false);
            $request->setData('internal', $attribute['internal'] ?? false);
            $request->setData('is_required', $attribute['is_required'] ?? false);
            $request->setData('custom', $attribute['is_custom_allowed'] ?? false);
            $request->setData('validation_pattern', $attribute['validation_pattern'] ?? '');
            $request->setData('datatype', (int) $attribute['value_type']);

            $module->apiTaskAttributeTypeCreate($request, $response);

            $responseData = $response->getData('');
            if (!\is_array($responseData)) {
                continue;
            }

            $taskAttrType[$attribute['name']] = \is_array($responseData['response'])
                ? $responseData['response']
                : $responseData['response']->toArray();

            $isFirst = true;
            foreach ($attribute['l11n'] as $language => $l11n) {
                if ($isFirst) {
                    $isFirst = false;
                    continue;
                }

                $response = new HttpResponse();
                $request  = new HttpRequest();

                $request->header->account = 1;
                $request->setData('title', $l11n);
                $request->setData('language', $language);
                $request->setData('type', $taskAttrType[$attribute['name']]['id']);

                $module->apiTaskAttributeTypeL11nCreate($request, $response);
            }
        }

        return $taskAttrType;
    }

    /**
     * Create default attribute values for types
     *
     * @param ApplicationAbstract $app          Application
     * @param array               $taskAttrType Attribute types
     * @param array               $attributes   Attribute definition
     *
     * @return array
     *
     * @since 1.0.0
     */
    private static function createTaskAttributeValues(ApplicationAbstract $app, array $taskAttrType, array $attributes) : array
    {
        /** @var array<string, array> $taskAttrValue */
        $taskAttrValue = [];

         /** @var \Modules\Tasks\Controller\ApiAttributeController $module */
         $module = $app->moduleManager->get('Tasks', 'ApiAttribute');

        foreach ($attributes as $attribute) {
            $taskAttrValue[$attribute['name']] = [];

            /** @var array $value */
            foreach ($attribute['values'] as $value) {
                $response = new HttpResponse();
                $request  = new HttpRequest();

                $request->header->account = 1;
                $request->setData('value', $value['value'] ?? '');
                $request->setData('unit', $value['unit'] ?? '');
                $request->setData('default',true);
                $request->setData('type', $taskAttrType[$attribute['name']]['id']);

                if (isset($value['l11n']) && !empty($value['l11n'])) {
                    $request->setData('title', \reset($value['l11n']));
                    $request->setData('language', \array_keys($value['l11n'])[0] ?? 'en');
                }

                $module->apiTaskAttributeValueCreate($request, $response);

                $responseData = $response->getData('');
                if (!\is_array($responseData)) {
                    continue;
                }

                $attrValue = \is_array($responseData['response'])
                    ? $responseData['response']
                    : $responseData['response']->toArray();

                $taskAttrValue[$attribute['name']][] = $attrValue;

                $isFirst = true;
                foreach (($value['l11n'] ?? []) as $language => $l11n) {
                    if ($isFirst) {
                        $isFirst = false;
                        continue;
                    }

                    $response = new HttpResponse();
                    $request  = new HttpRequest();

                    $request->header->account = 1;
                    $request->setData('title', $l11n);
                    $request->setData('language', $language);
                    $request->setData('value', $attrValue['id']);

                    $module->apiTaskAttributeValueL11nCreate($request, $response);
                }
            }
        }

        return $taskAttrValue;
    }
}
