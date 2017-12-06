<?php

/**
 * @package Importer
 * @author Iurii Makukh
 * @copyright Copyright (c) 2017, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\import\controllers;

use gplcart\core\helpers\Csv as CsvHelper;
use gplcart\core\models\File as FileModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to Import module
 */
class Import extends BackendController
{

    /**
     * File model class instance
     * @var \gplcart\core\models\File $file
     */
    protected $file;

    /**
     * CSV helper class
     * @var \gplcart\core\helpers\Csv $csv
     */
    protected $csv;

    /**
     * @param FileModel $file
     * @param CsvHelper $csv
     */
    public function __construct(FileModel $file, CsvHelper $csv)
    {
        parent::__construct();

        $this->csv = $csv;
        $this->file = $file;
    }

    /**
     * Displays the import page
     */
    public function doImport()
    {
        $this->downloadErrorsImport();

        $settings = $this->module->getSettings('import');
        $this->setData('settings', $settings);

        unset($settings['header']['product_id']);
        $this->setData('columns', $settings['header']);

        $this->submitImport();

        $this->setTitleDoImport();
        $this->setBreadcrumbDoImport();

        $this->outputDoImport();
    }

    /**
     * Sets titles on the import page
     */
    protected function setTitleDoImport()
    {
        $this->setTitle($this->text('Import'));
    }

    /**
     * Sets breadcrumbs on the import page
     */
    protected function setBreadcrumbDoImport()
    {
        $breadcrumb = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the import page
     */
    protected function outputDoImport()
    {
        $this->output('import|import');
    }

    /**
     * Downloads an error log file
     */
    protected function downloadErrorsImport()
    {
        $file = gplcart_file_private_temp('import_module_errors.csv');
        if ($this->isQuery('download_errors') && is_file($file)) {
            $this->download($file);
        }
    }

    /**
     * Start import
     */
    protected function submitImport()
    {
        if ($this->isPosted('import') && $this->validateImport()) {
            $this->setJobImport();
        }
    }

    /**
     * Validates submitted import data
     * @return boolean
     */
    protected function validateImport()
    {
        $this->setSubmitted('settings');

        $this->validateFileImport();
        $this->validateHeaderImport();

        return !$this->hasErrors();
    }

    /**
     * Validate uploaded CSV file
     * @return boolean
     */
    protected function validateFileImport()
    {
        $file = $this->request->file('file');

        if (empty($file)) {
            $this->setError('file', $this->text('File is required'));
            return false;
        }

        $result = $this->file->upload($file, 'csv', gplcart_file_private_module('import'));

        if ($result !== true) {
            $this->setError('file', $result);
            return false;
        }

        $uploaded = $this->file->getTransferred();

        $this->setSubmitted('filepath', $uploaded);
        $this->setSubmitted('filesize', filesize($uploaded));
        return true;
    }

    /**
     * Validates CSV header
     */
    public function validateHeaderImport()
    {
        if ($this->isError()) {
            return null;
        }

        $header = $this->module->getSettings('import', 'header');
        $delimiter = $this->module->getSettings('import', 'delimiter');

        $real_header = $this->csv->setFile($this->getSubmitted('filepath'))
                ->setHeader($header)
                ->setDelimiter($delimiter)
                ->getHeader();

        if ($header != $real_header) {
            $vars = array('@format' => implode(' | ', $header));
            $error = $this->language->text('Wrong header. Required columns: @format', $vars);
            $this->setError('file', $error);
        }
    }

    /**
     * Sets up import
     */
    protected function setJobImport()
    {
        $submitted = $this->getSubmitted();

        $settings = $this->module->getSettings('import');
        $settings['mode'] = $submitted['mode'];
        $settings['update'] = $submitted['update'];

        $this->module->setSettings('import', $settings);

        $job = array(
            'id' => 'import_product',
            'total' => $submitted['filesize'],
            'data' => array_merge($settings, $submitted),
            'redirect_message' => array(
                'finish' => 'Success. Inserted: %inserted, updated: %updated',
                'errors' => $this->text('Inserted: %inserted, updated: %updated, errors: %errors. <a href="@url">See error log</a>', array(
                    '@url' => $this->url('', array('download_errors' => true))))
            ),
            'log' => array(
                'errors' => gplcart_file_private_temp('import_module_errors.csv')
            )
        );

        $this->job->submit($job);
    }

}
