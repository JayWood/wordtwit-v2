<?php if ( wordtwit_has_accounts() ) { ?>
	<table class="widefat post fixed">
		<tr>
			<th class="default">Default</th>
			<th class="avatar">Avatar</th>
			<th class="name">Screen Name</th>
			<th class="follower">Followers</th>
			<th class="actions">Actions</th>
		</tr>
	<?php while ( wordtwit_has_accounts() ) { ?>
		<?php wordtwit_the_account(); ?>		
		<tr>
			<td><input type="checkbox"<?php if ( wordtwit_account_is_default() ) echo ' checked'; ?> data-screen-name="<?php wordtwit_the_account_screen_name(); ?>" />
			<td><img src="<?php wordtwit_the_account_avatar(); ?>" /></td>
			<td><a href="http://twitter.com/<?php wordtwit_the_account_screen_name(); ?>" target="_blank"><?php wordtwit_the_account_screen_name(); ?></a></td>
			<td><?php echo number_format( wordtwit_get_account_followers() ); ?></td>
			<td><a href="<?php echo add_query_arg( array( 'wordtwit_delete_user' => wordtwit_get_account_screen_name(), 'wordtwit_nonce' => wp_create_nonce( 'wordtwit' ) ) ); ?>"><?php _e( 'Delete', 'wordtwit-pro' ); ?></a></td>
		</tr>
	<?php } ?>
	</table>
<?php } else { ?>
	<div class="warning">
	<?php _e( 'There are no Twitter accounts registered', 'wordtwit-pro' ); ?>
	</div>
<?php } ?>

<a id="twitter_auth" href="<?php wordtwit_the_twitter_authorize_url(); ?>"><img src="http://a0.twimg.com/images/dev/buttons/sign-in-with-twitter-d-sm.png" alt="<?php _e( 'Authorize New Account', 'wordtwit-pro' ); ?>" /></a>

