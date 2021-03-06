<?php

/**
 * @package Importer
 * @author Iurii Makukh
 * @copyright Copyright (c) 2017, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\import\handlers;

use gplcart\core\Config;
use gplcart\core\models\FileTransfer;
use gplcart\core\models\Product;
use gplcart\core\models\Translation;
use gplcart\core\models\User;
use gplcart\core\models\Validator;
use gplcart\modules\import\helpers\Csv;

/**
 * Handler for Importer module
 */
class Import
{

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * User model instance
     * @var \gplcart\core\models\User $user
     */
    protected $user;

    /**
     * Validator model instance
     * @var \gplcart\core\models\Validator $validator
     */
    protected $validator;

    /**
     * File transfer model instance
     * @var \gplcart\core\models\FileTransfer $file_transfer
     */
    protected $file_transfer;

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * CSV class instance
     * @var \gplcart\modules\import\helpers\Csv $csv
     */
    protected $csv;

    /**
     * An array of errors
     * @var array
     */
    protected $errors = array();

    /**
     * An array of parsed CSV rows
     * @var array
     */
    protected $rows = array();

    /**
     * An array of the current job
     * @var array
     */
    protected $job = array();

    /**
     * An array of the current line data
     * @var array
     */
    protected $data = array();

    /**
     * @param Config $config
     * @param Product $product
     * @param User $user
     * @param FileTransfer $file_transfer
     * @param Translation $translation
     * @param Validator $validator
     * @param Csv $csv
     */
    public function __construct(Config $config, Product $product, User $user,
                                FileTransfer $file_transfer, Translation $translation,
                                Validator $validator, Csv $csv)
    {
        $this->csv = $csv;
        $this->user = $user;
        $this->config = $config;
        $this->product = $product;
        $this->validator = $validator;
        $this->translation = $translation;
        $this->file_transfer = $file_transfer;
    }

    /**
     * Processes one import iteration
     * @param array $job
     */
    public function process(array &$job)
    {
        $this->job = &$job;
        $this->rows = array();
        $this->errors = array();

        $this->csv->open($this->job['data']['filepath'], $this->job['data']['filesize'])
            ->setLimit($this->job['data']['limit'])
            ->setHeader($this->job['data']['header'])
            ->setDelimiter($this->job['data']['delimiter']);

        if (empty($this->job['context']['offset'])) {
            $this->rows = $this->csv->skipHeader()->parse();
        } else {
            $this->rows = $this->csv->setOffset($this->job['context']['offset'])->parse();
        }

        $this->import();
        $this->finish();
    }

    /**
     * Returns a total number of errors and logs them
     * @return integer
     */
    protected function countErrors()
    {
        $count = 0;

        foreach ($this->errors as $line => $errors) {
            $errors = array_filter($errors);
            $count += count($errors);
            $this->logErrors($line, $errors);
        }

        return $count;
    }

    /**
     * Logs all errors happened on the line
     * @param integer $line
     * @param array $errors
     * @return boolean
     */
    protected function logErrors($line, array $errors)
    {
        $line_message = $this->translation->text('Line @num', array('@num' => $line));
        return gplcart_file_csv($this->job['log']['errors'], array($line_message, implode(PHP_EOL, $errors)));
    }

    /**
     * Finishes the current iteration
     */
    protected function finish()
    {
        if (empty($this->rows)) {
            $this->job['status'] = false;
            $this->job['done'] = $this->job['total'];
        } else {
            $offset = $this->csv->getOffset();
            $this->job['context']['offset'] = $offset;
            $this->job['done'] = $offset ? $offset : $this->job['total'];
        }

        $this->job['errors'] += $this->countErrors();
        $vars = array('@num' => $this->job['context']['line']);
        $this->job['message']['process'] = $this->translation->text('Last processed line: @num', $vars);
    }

    /**
     * Adds/updates an array of products
     */
    protected function import()
    {
        foreach ($this->rows as $row) {
            if ($this->prepare($row) && $this->validate()) {
                $this->set();
            }
        }
    }

    /**
     * Prepare a data taken from a CSV row
     * @param array $row
     * @return boolean
     */
    protected function prepare(array $row)
    {
        $this->job['context']['line']++;
        $this->data = array_filter(array_map('trim', $row));

        if (isset($this->data['product_id']) && $this->data['product_id'] !== '') {
            $product_id = $this->data['product_id'];
            $this->data = array_intersect_key($this->data, array_flip($this->job['data']['update']));
            $this->data['update'] = $this->data['product_id'] = $product_id;
            $skip = $this->job['data']['mode'] === 'create';
        } else {
            $skip = $this->job['data']['mode'] === 'update';
        }

        if ($skip) {
            return false;
        }

        if (empty($this->data['update']) && empty($this->data['user_id'])) {
            $this->data['user_id'] = $this->user->getId();
        }

        return true;
    }

    /**
     * Validates a data to be imported
     * @return boolean
     */
    public function validate()
    {
        if (empty($this->data['update']) && !$this->user->access('product_add')) {
            $this->setError($this->translation->text('No access to add products'));
            return false;
        }

        if (!empty($this->data['update']) && !$this->user->access('product_update')) {
            $this->setError($this->translation->text('No access to update products'));
            return false;
        }

        if (!empty($this->data['images']) && !$this->user->access('file_upload')) {
            $this->setError($this->translation->text('No access to upload files'));
            return false;
        }

        $result = $this->validator->run('product', $this->data);

        if ($result === true) {
            return true;
        }

        settype($result, 'array');
        $this->setError(gplcart_array_flatten($result));
        return false;
    }

    /**
     * Sets a error
     * @param string|array $error
     */
    protected function setError($error)
    {
        settype($error, 'array');

        $line = $this->job['context']['line'];
        $existing = empty($this->errors[$line]) ? array() : $this->errors[$line];
        $this->errors[$line] = gplcart_array_merge($existing, $error);
    }

    /**
     * Adds/updates a single product
     */
    protected function set()
    {
        if (!empty($this->data['images'])) {
            $this->data['images'] = $this->getImages($this->data['images']);
        }

        if (empty($this->data['update']) && $this->product->add($this->data)) {
            $this->job['inserted']++;
        }

        if (!empty($this->data['update']) && $this->product->update($this->data['product_id'], $this->data)) {
            $this->job['updated']++;
        }
    }

    /**
     * Returns an array of values from a string using a delimiter character
     * @param string $string
     * @return array
     */
    protected function explodeValues($string)
    {
        $delimiter = $this->job['data']['multiple'];
        return array_filter(array_map('trim', explode($delimiter, $string)));
    }

    /**
     * Returns an array of image data
     * @param string $string
     * @return array
     */
    protected function getImages($string)
    {
        $images = array();

        foreach ($this->explodeValues($string) as $image) {
            $path = $this->getImagePath($image);
            if (!empty($path)) {
                $images[] = array('path' => $path);
            }
        }

        return $images;
    }

    /**
     * Validates and returns a relative image path.
     * If given an absolute URL, the file will be downloaded
     * @param string $image
     * @return boolean|string
     */
    protected function getImagePath($image)
    {
        if (strpos($image, 'http') === 0) {
            return $this->downloadImage($image);
        }

        $path = trim($image, '/');
        $file = gplcart_file_absolute($path);

        if (!is_file($file)) {
            $vars = array('@name' => $path);
            $error = $this->translation->text('@name is unavailable', $vars);
            $this->setError($error);
            return false;
        }

        $result = $this->file_transfer->validate($file);

        if ($result === true) {
            return $path;
        }

        $this->setError($result);
        return false;
    }

    /**
     * Downloads a remote image
     * @param string $url
     * @return boolean|string
     */
    protected function downloadImage($url)
    {
        $dirname = $this->config->get('product_image_dirname', 'product');
        $path = gplcart_path_relative(GC_DIR_IMAGE, GC_DIR_FILE) . "/$dirname";

        $result = $this->file_transfer->download($url, 'image', $path);

        if ($result === true) {
            return $this->file_transfer->getTransferred(true);
        }

        $this->setError($result);
        return false;
    }

}
