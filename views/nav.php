<?php
/**
* @var string $tab
*/
?>
<ul class="ka-admin-nav subsubsub">
	<li><a href="<?php echo admin_url('index.php?page=koko-analytics'); ?>" 
							<?php
							if ($tab === '') {
								?>
		class="current"<?php } ?>><?php echo __('Stats', 'koko-analytics'); ?></a></li>
	<?php if (current_user_can('manage_koko_analytics')) { ?>
		<li><a href="<?php echo admin_url('index.php?page=koko-analytics&tab=settings'); ?>" 
								<?php
								if ($tab === 'settings') {
									?>
			class="current"<?php } ?>><?php echo __('Settings', 'koko-analytics'); ?></a></li>
	<?php } ?>
</ul>
