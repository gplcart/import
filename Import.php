<?php

/**
 * @package Importer
 * @author Iurii Makukh
 * @copyright Copyright (c) 2017, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\import;

use gplcart\core\Module;

/**
 * Main class for Importer module
 */
class Import extends Module
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Module info
     * @return array
     */
    public function info()
    {
        return array(
            'name' => 'Importer',
            'version' => '1.0.0-dev',
            'description' => 'Allows to import products from CSV files',
            'author' => 'Iurii Makukh ',
            'core' => '1.x',
            'license' => 'GPL-3.0+',
            'configure' => 'admin/module/settings/import',
            'settings' => $this->getDefaultSettings()
        );
    }

    /**
     * Returns an array of default module settings
     * @return array
     */
    protected function getDefaultSettings()
    {
        return array(
            'limit' => 10,
            'delimiter' => ',',
            'multiple' => '|',
            'mode' => 'create_update',
            'update' => array('price', 'stock'),
            'header' => array(
                'product_id' => 'Product ID', 'title' => 'Title', 'sku' => 'SKU',
                'price' => 'Price', 'currency' => 'Currency', 'stock' => 'Stock',
                'product_class_id' => 'Product class ID', 'store_id' => 'Store ID',
                'category_id' => 'Category ID', 'brand_category_id' => 'Brand category ID',
                'alias' => 'Alias', 'images' => 'Images',
                'status' => 'Enabled', 'description' => 'Description',
                'meta_title' => 'Meta title', 'meta_description' => 'Meta description',
                'related' => 'Related product ID', 'width' => 'Width', 'height' => 'Height',
                'length' => 'Length', 'size_unit' => 'Size unit',
                'weight' => 'Weight', 'weight_unit' => 'Weight unit',
            ),
        );
    }

    /**
     * Implements hook "route.list"
     * @param mixed $routes
     */
    public function hookRouteList(&$routes)
    {
        // Settings
        $routes['admin/module/settings/import'] = array(
            'access' => 'module_edit',
            'handlers' => array(
                'controller' => array('gplcart\\modules\\import\\controllers\\Settings', 'editSettings')
            )
        );

        // Import page
        $routes['admin/tool/import'] = array(
            'menu' => array('admin' => 'Import'),
            'access' => 'import_product',
            'handlers' => array(
                'controller' => array('gplcart\\modules\\import\\controllers\\Import', 'doImport')
            )
        );
    }

    /**
     * Implements hook "hook.cron"
     */
    public function hookCron()
    {
        // Automatically delete uploaded files older than 1 day
        $lifespan = 86400;
        $directory = GC_PRIVATE_DIR . '/import';
        if (is_dir($directory)) {
            gplcart_file_delete($directory, array('csv'), $lifespan);
        }
    }

    /**
     * Implements hook "user.role.permissions"
     * @param array $permissions
     */
    public function hookUserRolePermissions(array &$permissions)
    {
        $permissions['import_product'] = 'Importer: import products';
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
