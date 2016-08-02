<?php global $wordtwit_pro; $current_scheme = get_user_option('admin_color'); ?>
<div id="wordtwit-accounts" class="<?php echo $current_scheme; ?> wrap">
	<h2><?php _e( 'Twitter Accounts', 'wordtwit-pro' ); ?>
	<?php if ( wordtwit_user_can_add_account() && wordtwit_has_defined_custom_twitter_app() ) { ?>
		<a href="#" id="twitter-add-button" class="add-new-h2"><?php _e( 'Add Account', 'wordtwit-pro' ); ?> &raquo;</a>
	<?php } ?>
	</h2>

	<?php if ( !wordtwit_has_defined_custom_twitter_app() ) { ?>

		<?php _e( 'To configure Twitter accounts, you must set up a custom Twitter application and configure WordTwit to use it.', 'wordtwit-pro' ); ?>
		<br /><br />
	 	<?php echo sprintf( __( '%sView the Help Tutorial &raquo;%s', 'wordtwit-pro' ), '<a href="http://www.bravenewcode.com/creating-a-custom-application-for-wordtwit/">', '</a>' ); ?>
	 	&nbsp;|&nbsp;
		<?php echo sprintf( __( '%s Configure on Twitter Now &raquo;%s', 'wordtwit-pro' ), '<a href="https://dev.twitter.com/apps">', '</a>' ); ?>


	<?php } else if ( !wordtwit_has_accounts() ) { ?>

		<?php _e( 'You have not added any Twitter accounts yet', 'wordtwit-pro' ); ?>.<br /><br />
		<?php _e( "Click the 'Add Account' button above to add your first Twitter account", 'wordtwit-pro' ); ?>.<br /><br />

	<?php } else { ?>

		<table id="wordtwit-account-list" class="wp-list-table widefat fixed posts" cellspacing="0">
			<thead>
				<tr>
					<!-- <th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th> -->
					<th scope="col" class="manage-column col-avatar desc"><?php _e( 'Screen Name', 'wordtwit-pro' ); ?></th>
					<th scope="col" class="manage-column desc col-type"><?php _e( 'Type', 'wordtwit-pro' ); ?></th>
					<th scope="col" class="manage-column desc col-followers"><?php _e( 'Followers', 'wordtwit-pro' ); ?></th>
					<th scope="col" class="manage-column desc col-updates"><?php _e( 'Updates', 'wordtwit-pro' ); ?></th>
					<th scope="col" class="manage-column desc col-order"><?php _e( 'Publish Order', 'wordtwit-pro' ); ?> <img class="account-loader" src="<?php echo WORDTWIT_URL; ?>/admin/images/ajax-loader.gif" alt="Ajax Loader" style="display: none;" /></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<!-- <th scope="col" class="manage-column column-cb check-column"><input type="checkbox" /></th> -->
					<th scope="col" class="manage-column col-avatar desc"><?php _e( 'Screen Name', 'wordtwit-pro' ); ?></th>
					<th scope="col" class="manage-column desc col-type"><?php _e( 'Type', 'wordtwit-pro' ); ?></th>
					<th scope="col" class="manage-column desc col-followers"><?php _e( 'Followers', 'wordtwit-pro' ); ?></th>
					<th scope="col" class="manage-column desc col-updates"><?php _e( 'Updates', 'wordtwit-pro' ); ?></th>
					<th scope="col" class="manage-column desc col-order"><?php _e( 'Publish Order', 'wordtwit-pro' ); ?></th>
				</tr>
			</tfoot>
			<tbody>
			<?php $account_count = 0; ?>
			<?php while ( wordtwit_has_accounts() ) { ?>
				<?php wordtwit_the_account(); ?>
				<tr class="can-sort <?php if ( $account_count % 2 == 1 ) echo 'alternate '; ?><?php if ( wordtwit_is_account_global() ) echo 'shared '; else echo 'private'; ?>" valign="top" data-name="<?php wordtwit_the_account_screen_name(); ?>">
					<!-- <th scope="row" class="check-column"><input type="checkbox" /></th> -->
					<td class="col-avatar">
						<img src="<?php wordtwit_the_account_avatar(); ?>" />
						<a href="http://twitter.com/<?php wordtwit_the_account_screen_name(); ?>" class="screenname" target="_blank"><?php wordtwit_the_account_screen_name(); ?></a><br />
						<small><?php wordtwit_the_account_location(); ?></small>
						<div class="row-actions">
							<?php /* Global account and administrator, can do everything */ ?>
							<?php if ( wordtwit_current_user_can_modify_account() || wordtwit_current_user_can_delete_account() ) { ?>
							<a href="<?php wordtwit_the_account_refresh_url(); ?>"><?php _e( 'Refresh', 'wordtwit-pro' ); ?></a> |
							<?php } ?>

							<?php if ( wordtwit_current_user_can_delete_account() ) { ?>
							<a href="<?php wordtwit_the_account_delete_url(); ?>"><?php _e( 'Remove', 'wordtwit-pro' ); ?></a>
							<?php } ?>
							<?php if ( wordtwit_current_user_can_modify_account() ) { ?>
								<?php if ( wordtwit_is_account_global() ) { ?>
								| <a href="<?php wordtwit_the_account_type_change_url( 'local' ); ?>"><?php _e( 'Make Private', 'wordtwit-pro' ); ?></a>
									<?php if ( wordtwit_is_multisite_enabled() && is_super_admin() ) { ?>
									| <a href="<?php wordtwit_the_account_type_change_url( 'sitewide' ); ?>"><?php _e( 'Make Site-wide', 'wordtwit-pro' ); ?></a>
									<?php } ?>
								<?php } else { ?>

								| <a href="<?php wordtwit_the_account_type_change_url( 'global' ); ?>"><?php _e( 'Make Shared', 'wordtwit-pro' ); ?></a>
								<?php } ?>
							<?php } ?>

						</div>
					</td>
					<td class="col-type">
					<?php if ( wordtwit_is_account_site_wide() ) { ?>
						<?php _e( 'Site-wide', 'wordtwit-pro' ); ?><br />
						<small><?php _e( 'All sites can publish to this account', 'wordtwit-pro' ); ?></small>
					<?php } else if ( wordtwit_is_account_global() ) { ?>
						<?php _e( 'Shared', 'wordtwit-pro' ); ?><br />
						<small><?php _e( 'Others can publish to this account', 'wordtwit-pro' ); ?></small>
					<?php } else { ?>
						<?php _e( 'Private', 'wordtwit-pro' ); ?><br />
						<small><?php echo sprintf( __( 'Owned by %s', 'wordtwit-pro' ), wordtwit_get_account_owner() ); ?></small>
					<?php } ?>
					</td>
					<td class="col-followers"><?php echo number_format( wordtwit_get_account_followers() ); ?></td>
					<td class="col-updates">
						<?php echo number_format( wordtwit_get_account_status_updates() ) ; ?>
					</td>
					<td class="col-order">
						<img class="drag-icon" src="<?php echo WORDTWIT_URL; ?>/admin/images/drag-icon.png" alt="Drag Icon" />
					</td>
				</tr>
				<?php $account_count++; ?>
			<?php } ?>
			</tbody>
		</table>
		<div id="notes">
			<?php _e( "* Change the order of your account's published Tweets by dragging accounts up or down", "wordtwit-pro" ); ?>
		</div>
	<?php } ?>
</div>