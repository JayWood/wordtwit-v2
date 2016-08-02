<div id="wordtwit-post-widget-no-accounts">
	<div class="wt-tweet-status">
		<p><?php _e( "Status", "wordtwit-pro" ); ?>:	
		<span>
			<?php _e( "No accounts configured yet", "wordtwit-pro" ); ?>
			<?php if ( false && wordtwit_user_can_add_account() ) { ?>
				<br /><?php echo '<a href="' . admin_url( 'admin.php?page=wordtwit_account_configuration' ) . '">' . __( 'Add an account', 'wordtwit-pro' ) . ' &raquo;</a>'; ?>
			<?php } ?>
		</span>
	</div>
</div>