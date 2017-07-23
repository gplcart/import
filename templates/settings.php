<?php
/**
* @package Importer
* @author Iurii Makukh
* @copyright Copyright (c) 2017, Iurii Makukh
* @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
*/
?>
<form method="post" class="form-horizontal">
  <input type="hidden" name="token" value="<?php echo $_token; ?>">
  <div class="form-group required<?php echo $this->error('limit', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Limit'); ?></label>
    <div class="col-md-4">
      <input name="settings[limit]" class="form-control" value="<?php echo $this->e($settings['limit']); ?>">
      <div class="help-block">
        <?php echo $this->error('limit'); ?>
        <div class="text-muted">
          <?php echo $this->text('How many items to process per one iteration. Depends on your PHP settings and server capability'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('delimiter', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Column delititer'); ?></label>
    <div class="col-md-4">
      <input name="settings[delimiter]" class="form-control" value="<?php echo $this->e($settings['delimiter']); ?>">
      <div class="help-block">
        <?php echo $this->error('delimiter'); ?>
        <div class="text-muted">
          <?php echo $this->text('A character to separate columns in your CSV file'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('multiple', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Multi-value delimiter'); ?></label>
    <div class="col-md-4">
      <input name="settings[multiple]" class="form-control" value="<?php echo $this->e($settings['multiple']); ?>">
      <div class="help-block">
        <?php echo $this->error('multiple'); ?>
        <div class="text-muted">
          <?php echo $this->text('A character to separate multiple values in a CSV row'); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group required<?php echo $this->error('header', ' has-error'); ?>">
    <label class="col-md-2 control-label"><?php echo $this->text('Mapping'); ?></label>
    <div class="col-md-4">
      <textarea name="settings[header]" rows="25" class="form-control"><?php echo $this->e($settings['header']); ?></textarea>
      <div class="help-block">
        <?php echo $this->error('header'); ?>
        <div class="text-muted">
          <?php echo $this->text('How to associate product fields with CSV columns. Each rule must consist of "product data key/column name" pair separated by whitespace. One rule per line. <a href="@url">Download default template</a>', array('@url' => $this->url('', array('download_template' => true)))); ?>
        </div>
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-md-6 col-md-offset-2">
      <div class="btn-toolbar">
        <button class="btn btn-danger reset" name="reset" value="1" onclick="return confirm(GplCart.text('Are you sure?'));"><?php echo $this->text('Reset to default'); ?></button>
        <a href="<?php echo $this->url("admin/module/list"); ?>" class="btn btn-default"><?php echo $this->text("Cancel"); ?></a>
        <button class="btn btn-default save" name="save" value="1"><?php echo $this->text('Save'); ?></button>
      </div>
    </div>
  </div>
</form>