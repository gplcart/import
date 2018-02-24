<?php

/**
 * @package Importer
 * @author Iurii Makukh
 * @copyright Copyright (c) 2017, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\import\controllers;

use gplcart\core\controllers\backend\Controller;

/**
 * Handles incoming requests and outputs data related to Importer module
 */
class Settings extends Controller
{

    /**
     * Route page callback
     * Display the module settings page
     */
    public function editSettings()
    {
        $this->downloadTemplateSettings();
        $this->setTitleEditSettings();
        $this->setBreadcrumbEditSettings();

        $this->setData('settings', $this->module->getSettings('import'));

        $this->submitSettings();
        $this->setDataEditSettings();
        $this->outputEditSettings();
    }

    /**
     * Downloads a CSV template file
     */
    protected function downloadTemplateSettings()
    {
        if ($this->isQuery('download_template')) {
            $this->download(__DIR__ . '/../templates/data.csv');
        }
    }

    /**
     * Prepare data before rendering
     */
    protected function setDataEditSettings()
    {
        $header = $this->getData('settings.header');

        if (is_array($header)) {

            $string = '';
            foreach ($header as $key => $value) {
                $string .= "$key $value\n";
            }

            $this->setData('settings.header', trim($string));
        }
    }

    /**
     * Set title on the module settings page
     */
    protected function setTitleEditSettings()
    {
        $title = $this->text('Edit %name settings', array('%name' => $this->text('Importer')));
        $this->setTitle($title);
    }

    /**
     * Set breadcrumbs on the module settings page
     */
    protected function setBreadcrumbEditSettings()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Modules'),
            'url' => $this->url('admin/module/list')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Saves the submitted settings
     */
    protected function submitSettings()
    {
        if ($this->isPosted('reset')) {
            $this->updateSettings(array());
        } else if ($this->isPosted('save') && $this->validateSettings()) {
            $this->updateSettings($this->getSubmitted());
        }
    }

    /**
     * Validate submitted module settings
     */
    protected function validateSettings()
    {
        $this->setSubmitted('settings');

        $this->validateElement('limit', 'required');
        $this->validateElement('multiple', 'required');
        $this->validateElement('delimiter', 'required');
        $this->validateElement('limit', 'numeric');

        $this->validateHeaderSettings();

        return !$this->hasErrors();
    }

    /**
     * Validate header mapping
     */
    protected function validateHeaderSettings()
    {
        $errors = $header = array();
        $lines = gplcart_string_explode_multiline($this->getSubmitted('header', ''));

        foreach ($lines as $pos => $line) {

            $pos++;
            $data = array_filter(array_map('trim', explode(' ', $line, 2)));

            if (count($data) != 2) {
                $errors[] = $pos;
                continue;
            }

            list($key, $label) = $data;

            if (preg_match('/^[a-z_]+$/', $key) !== 1) {
                $errors[] = $pos;
                continue;
            }

            $header[$key] = $label;
        }

        if (empty($errors)) {
            $this->setSubmitted('header', $header);
        } else {
            $this->setError('header', $this->text('Error on line @num', array('@num' => implode(',', $errors))));
        }
    }

    /**
     * Update module settings
     * @param array $settings
     */
    protected function updateSettings(array $settings)
    {
        $this->controlAccess('module_edit');
        $this->module->setSettings('import', $settings);
        $this->redirect('', $this->text('Settings have been updated'), 'success');
    }

    /**
     * Render and output the module settings page
     */
    protected function outputEditSettings()
    {
        $this->output('import|settings');
    }

}
