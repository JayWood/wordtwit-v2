<?php $settings = wordtwit_get_settings(); ?>
<ul>
	<li>
		<?php _e( "Server Time", "wordtwit-pro" ); ?>: 
		<?php if ( wordtwit_server_time_is_accurate() ) { ?>
			<span class="green-text">
				<?php _e( "Accurate", "wordtwit-pro" ); ?>
			</span>		
		<?php } else { ?>
			<span class="red-text">
				<?php _e( "Incorrect", "wordtwit-pro" ); ?>
			</span>
		<?php } ?>
	</li>
</ul>		