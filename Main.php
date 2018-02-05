<?php

/**
 * @package Importer
 * @author Iurii Makukh
 * @copyright Copyright (c) 2017, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\import;

/**
 * Main class for Importer module
 */
class Main
{

    /**
     * Implements hook "route.list"
     * @param mixed $routes
     */
    public function hookRouteList(&$routes)
    {
        $routes['admin/module/settings/import'] = array(
            'access' => 'module_edit',
            'handlers' => array(
                'controller' => array('gplcart\\modules\\import\\controllers\\Settings', 'editSettings')
            )
        );

        $routes['admin/tool/import'] = array(
            'menu' => array(
                'admin' => 'Import' // @text
            ),
            'access' => 'import_product',
            'handlers' => array(
                'controller' => array('gplcart\\modules\\import\\controllers\\Import', 'doImport')
            )
        );
    }

    /**
     * Implements hook "hook.cron"
     */
    public function hookCronRunAfter()
    {
        gplcart_file_empty(gplcart_file_private_module('import'), array('csv'), 24 * 60 * 60);
    }

    /**
     * Implements hook "user.role.permissions"
     * @param array $permissions
     */
    public function hookUserRolePermissions(array &$permissions)
    {
        $permissions['import_product'] = 'Importer: import products'; // @text
    }

    /**
     * Implements hook "job.handlers"
     * @param mixed $handlers
     */
    public function hookJobHandlers(array &$handlers)
    {
        $handlers['import_product'] = array(
            'handlers' => array(
                'process' => array('gplcart\\modules\\import\\handlers\\Import', 'process')
            ),
        );
    }

}
