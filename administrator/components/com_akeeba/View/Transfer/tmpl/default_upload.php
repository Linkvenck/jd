<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

/** @var  $this  \Akeeba\Backup\Admin\View\Transfer\Html */
?>

<div id="akeeba-transfer-upload" class="akeeba-panel--primary" style="display: none;">
	<header class="akeeba-block-header">
        <h3>
	        <?php echo \JText::_('COM_AKEEBA_TRANSFER_HEAD_UPLOAD'); ?>
        </h3>
	</header>

	<div class="akeeba-block--failure" style="display: none" id="akeeba-transfer-upload-error">
	</div>

	<div id="akeeba-transfer-upload-area-upload" style="display: none">
		<div id="backup-steps">
			<div class="akeeba-label--orange" id="akeeba-transfer-upload-lbl-kickstart">
				<?php echo \JText::_('COM_AKEEBA_TRANSFER_LBL_UPLOAD_KICKSTART'); ?>
			</div>
			<div class="akeeba-label--grey" id="akeeba-transfer-upload-lbl-archive">
				<?php echo \JText::_('COM_AKEEBA_TRANSFER_LBL_UPLOAD_BACKUP'); ?>
			</div>
		</div>
		<div id="backup-status" class="well">
			<div id="backup-step">
				&#9729; <span id="akeeba-transfer-upload-percent"></span>
			</div>
			<div id="backup-substep">
				&#128190; <span id="akeeba-transfer-upload-size"></span>
			</div>
		</div>
	</div>

	<div id="akeeba-transfer-upload-area-kickstart" style="display: none">
		<p>
			<a class="akeeba-btn--green--large" id="akeeba-transfer-upload-btn-kickstart" href="" target="_blank">
				<span class="akion-arrow-right-b"></span>
				<?php echo \JText::_('COM_AKEEBA_TRANSFER_BTN_OPEN_KICKSTART'); ?>
			</a>
		</p>

		<div class="akeeba-block--info">
			<?php echo \JText::_('COM_AKEEBA_TRANSFER_LBL_OPEN_KICKSTART_INFO'); ?>
		</div>
	</div>

</div>
