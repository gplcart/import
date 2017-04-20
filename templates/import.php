<?php
/**
 * @package Importer
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
?>
<div class="panel panel-default">
  <div class="panel-body">
    <form method="post" enctype="multipart/form-data" class="form-horizontal" onsubmit="return confirm(GplCart.text('Are you sure?'));">
      <input type="hidden" name="token" value="<?php echo $this->prop('token'); ?>">
      <div class="form-group required<?php echo $this->error('file', ' has-error'); ?>">
        <label class="col-md-2 control-label"><?php echo $this->text('CSV file'); ?></label>
        <div class="col-md-4">
          <input type="file" class="form-control" name="file">
          <div class="help-block">
            <?php echo $this->error('file'); ?>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-md-2 control-label"><?php echo $this->text('Mode'); ?></label>
        <div class="col-md-4">
          <select name="settings[mode]" class="form-control">
            <option value="create_update"<?php echo $settings['mode'] === 'create_update' ? ' selected' : ''; ?>><?php echo $this->text('Create and update'); ?></option>
            <option value="create"<?php echo $settings['mode'] === 'create' ? ' selected' : ''; ?>><?php echo $this->text('Only create'); ?></option>
            <option value="update"<?php echo $settings['mode'] === 'update' ? ' selected' : ''; ?>><?php echo $this->text('Only update'); ?></option>
          </select>
          <div class="help-block">
            <?php echo $this->text('<ul class="list-unstyled"><li><i>Create and update</i> - rows with specified product ID will be updated, otherwise created</li><li><i>Only update</i> - only update products using their unique IDs</li><li><i>Only create</i> - only create products from rows without product IDs</li></ul>'); ?>
          </div>
        </div>
      </div>
      <?php if (!$this->error(null, true)) { ?>
      <div class="form-group">
        <div class="col-md-4 col-md-offset-2">
          <a href="#import-advanced-settings" data-toggle="collapse"><?php echo $this->text('Columns'); ?> <span class="caret"></span></a>
        </div>
      </div>
      <?php } ?>
      <div id="import-advanced-settings" class="<?php echo $this->error(null, '', 'collapse'); ?>">
      <div class="form-group">
        <div class="col-md-4 col-md-offset-2">
          <?php foreach ($columns as $field => $label) { ?>
          <div class="checkbox">
            <label>
              <input type="checkbox" name="settings[update][]" value="<?php echo $this->e($field); ?>"<?php echo in_array($field, $settings['update']) ? ' checked' : ''; ?>> <?php echo $this->e($label); ?>
            </label>
          </div>
          <?php } ?>
          <div class="help-block">
            <?php echo $this->text('Select columns to be used as a data source when a product will be updating'); ?>
          </div>
        </div>
      </div>
      </div>
      <div class="form-group">
        <div class="col-md-4 col-md-offset-2 text-right">
          <button class="btn btn-default import" name="import" value="1"><?php echo $this->text('Import'); ?></button>
        </div>
      </div>
    </form>
  </div>
</div>
<?php if (!empty($job)) { ?>
<?php echo $job; ?>
<?php } ?>