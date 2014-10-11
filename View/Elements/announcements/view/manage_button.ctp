<?php
/**
 * announcements manage_button elements
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>

<?php if ($contentEditable) : ?>
	<p class="text-right">
		<button class="btn btn-primary"
				tooltip="<?php echo __d('announcements', 'Manage'); ?>">
			<span class="glyphicon glyphicon-cog">
			</span>
		</button>
	</p>
<?php endif; ?>

