<?php require_once( WORDTWIT_DIR . '/include/post-box-functions.php' ); ?>
<?php $settings = wordtwit_get_settings(); ?>

<div id="wordtwit-post-widget"<?php if ( wordtwit_is_post_box_area_disabled() ) echo ' class="disabled"';?>>
	<img id="wt-post-box-spinner" src="<?php echo WORDTWIT_URL; ?>/admin/images/ajax-loader.gif" alt="" style="display: none;" />
	<div class="wt-tweet-status">
		<p><?php _e( "Status", "wordtwit-pro" ); ?>:	
		<?php if ( wordtwit_post_is_enabled() ) { ?>
			<?php if ( wordtwit_get_tweet_status() == WORDTWIT_TWEET_UNPUBLISHED || wordtwit_get_tweet_status() == WORDTWIT_TWEET_IS_DEFERRED ) { ?>
				<span><?php _e( "Unpublished", "wordtwit-pro" ); ?></span>
			<?php } else if ( wordtwit_get_tweet_status() == WORDTWIT_TWEET_SCHEDULED ) { ?>
				<span><?php _e( "Scheduled", "wordtwit-pro" ); ?></span>
			<?php } else if ( wordtwit_get_tweet_status() == WORDTWIT_TWEET_IS_OLD ) { ?>
				<span><?php _e( "Unpublished - Old Post", "wordtwit-pro" ); ?></span>
			<?php } else if ( wordtwit_get_tweet_status() == WORDTWIT_TWEET_PUBLISHED ) { ?>
				<span><?php _e( "Published", "wordtwit-pro" ); ?></span>
				<?php if ( wordtwit_get_pending_tweet_count() && wordtwit_get_publish_tweet_count() ) { ?>
					<a href="admin.php?page=tweet_queue">
						<span class="pending">
							(<?php echo sprintf( _n( '%d Tweeted', '%d Tweeted', wordtwit_get_publish_tweet_count(), 'wordtwit-pro' ), wordtwit_get_publish_tweet_count() ); ?>, <?php echo sprintf( _n( '%d Pending', '%d Pending', wordtwit_get_pending_tweet_count(), 'wordtwit-pro' ), wordtwit_get_pending_tweet_count() ); ?>)
						</span>
					</a>
				<?php } else if ( wordtwit_get_pending_tweet_count() ) { ?>
					<a href="admin.php?page=tweet_queue">
						<span class="pending">
							<?php echo sprintf( _n( "(%d Pending)", "(%d Pending)", wordtwit_get_pending_tweet_count(), "wordtwit-pro" ), wordtwit_get_pending_tweet_count() ); ?>
						</span>
					</a>
				<?php } else if ( wordtwit_get_publish_tweet_count() ) { ?>
					<a href="admin.php?page=tweet_queue">
						<span class="pending">
							<?php echo sprintf( _n( "(%d Tweeted)", "(%d Tweeted)", wordtwit_get_publish_tweet_count(), "wordtwit-pro" ), wordtwit_get_publish_tweet_count() ); ?>
						</span>
					</a>
				<?php } ?> 
				</p>
				<!--
				<p class="wt-tweet-p"><?php _e( "Accounts", "wordtwit-pro" ); ?>: 
					<span>
						<?php $accounts = wordtwit_get_post_tweet_accounts(); ?>
						<?php if ( count( $accounts ) ) { ?>
							<?php $new_accounts = array(); ?>
							<?php foreach( $accounts as $name => $info ) { ?>
								<?php if ( $info->active ) { $new_accounts[] = $name; } else { $new_accounts[] = sprintf( __( '%s (Missing)', 'wordtwit-pro' ), $name ); } ?>
							<?php } ?>
							<?php echo implode( ', ', $new_accounts ); ?>
						<?php } ?>
					</span>
				</p>
			
				<p class="wt-tweet-p"><?php _e( "Tweet", "wordtwit-pro" ); ?>: <span><?php wordtwit_the_post_tweet(); ?></span></p>
					-->
			<?php } ?>
		<?php } else { ?>
			<span><?php _e( 'Disabled for this post', 'wordtwit-pro' ); ?></span>
		<?php } ?>
		
		<?php if ( wordtwit_get_tweet_status() == WORDTWIT_TWEET_UNPUBLISHED ) { ?>
			<a href="#" id="wt-edit-link" class="wt-unbold"><?php _e( 'Edit', 'wordtwit-pro' ); ?></a>
		<?php } ?>
		<div id="wt-notweet" style="display:none">
			<select id="wt-select-status">
				<option value="1"<?php if ( wordtwit_post_is_enabled() ) echo ' selected'; ?>><?php _e( 'Will be tweeted', 'wordtwit-pro' ); ?></option>
				<option value="0"<?php if ( !wordtwit_post_is_enabled() ) echo ' selected'; ?>><?php _e( 'Do not tweet', 'wordtwit-pro' ); ?></option>
			</select>
			<input type="button" class="button" id="wt-status-button" value="<?php _e( "Ok", "wordtwit-pro" ); ?>" />
		</div>
	</div><!-- wt-tweet-status -->

<div id="disabled-wrapper" <?php if ( wordtwit_is_post_box_area_disabled() ) { echo "style='display:none'"; } ?>>		
	<p class="wt-tweet-mode">
		<?php _e( "Mode", "wordtwit-pro" ); ?>: 
		<?php if ( wordtwit_get_tweet_mode() == WORDTWIT_TWEET_AUTOMATIC ) { ?>
			<span id="tweet-mode-text"><?php _e( "Automatic", "wordtwit-pro" ); ?></span> 
			<span id="wt-reset-link" style="display:none" class="wt-unbold"><a href="#"><?php _e( "Reset", "wordtwit-pro" ); ?></a></span>
		<?php } else { ?>
			<span id="tweet-mode-text" class="manual"><?php _e( "Manual", "wordtwit-pro" ); ?></span> 
			<span id="wt-reset-link" class="wt-unbold"><a href="#"><?php _e( "Reset", "wordtwit-pro" ); ?></a></span>
		<?php } ?>
	</p>
	
	<span id="reset-link" style="display:none">
		<a href="#"><?php _e( "Reset", "wordtwit-pro" ); ?></a>
	</span>

	<p class="wt-tweet-text"><?php wordtwit_the_post_tweet(); ?></p>
	
	<div id="wt-count">&nbsp;</div>
	
	<ul id="wt-option-bar">
		<!-- do not need to be internationalized, text here is replaced by images -->
		<li id="wt-accounts" class="toggle"><span>Accounts</span></li>
		<li id="wt-tags" class="toggle"><span>Hashtags</span></li>
		<li id="wt-schedule" class="toggle"><span>Schedule</span></li>
	</ul>
	
	<div id="wt-widget-bottom-wrap" <?php global $post; if ( !isset( $_COOKIE[ 'wordtwit-pro-' . $post->ID ] ) ) { echo "style='display:none'"; } ?>>		
		<!-- Accounts -->
		<div id="wt-accounts-box" class="wt-widget-bottom" <?php if ( wordtwit_post_box_cookie_check( 'wt-accounts' ) ) { echo "style='display:none'"; } ?>>
			<h4 class="wt-h4"><?php _e( "Twitter Accounts", "wordtwit-pro" ); ?></h4>
			<?php if ( wordtwit_has_accounts() ) { ?>
				<ul id="wt-account-list">
				<?php while ( wordtwit_has_accounts() ) { ?>
					<?php wordtwit_the_account(); ?>
					
					<?php if ( wordtwit_current_user_can_tweet_from_account() ) { ?>
					<li>
						<input type="checkbox" name="account_<?php wordtwit_the_account_screen_name(); ?>"<?php if ( wordtwit_post_is_account_enabled( wordtwit_get_account_screen_name() ) ) echo ' checked'; ?> />
						<img src="<?php wordtwit_the_account_avatar(); ?>" alt="twitter avatar" />
						<span>
							<?php wordtwit_the_account_screen_name(); ?><br />
							<span class="acct-type"><?php if ( wordtwit_is_account_global() ) _e( 'shared', 'wordtwit-pro' ); else _e( 'private', 'wordtwit-pro' ); ?></span>
						</span>
					</li>
					<?php } ?>
				<?php } ?>
				</ul>
			<?php } else { ?>
				<p><?php _e( 'There are currently no accounts configured.', 'wordtwit-pro' ); ?></p>
				<p><a href="admin.php?page=wordtwit_account_configuration"><?php _e( 'Add An Account', 'wordtwit-pro' ); ?> &raquo;</a></p>
			<?php } ?>
		</div>	
		
		<!-- Tags -->
		<div id="wt-tags-box" class="wt-widget-bottom" <?php if ( wordtwit_post_box_cookie_check( 'wt-tags' ) ) { echo "style='display:none'"; } ?>>
			<h4 class="wt-h4"><?php _e( "Hashtags", "wordtwit-pro" ); ?></h4>
			<div class="wt-automatic">
				<ul class="tagchecklist">
				<?php if ( wordtwit_post_has_hash_tags() ) { ?>
					<?php foreach( wordtwit_get_post_hash_tags() as $tag ) { ?>
						<li id="<?php echo $tag; ?>-hashtag"><a href="#" class="hash-delete">X</a><span><?php echo $tag; ?></span></li>
					<?php } ?>
				<?php } ?>
				</ul>
				<p>
					<input type="text" id="wt-add-hashtag" name="wt-add-hashtag" class="newtag" size="24" placeholder="<?php _e( "Add Hashtags", "wordtwit-pro" ); ?>&hellip;" />
					<input type="button" class="button" value="<?php _e( "Add", "wordtwit-pro" ); ?>" />
				</p>
				<p class="howto"><?php _e( "Separate multiple hashtags with commas", "wordtwit-pro" ); ?></p>
				<p><a href="#" id="hashtag-toggle"><?php _e( "Choose from recent Hashtags", "wordtwit-pro" ); ?> &raquo;</a></p>
				<?php $hash_tags = wordtwit_get_recent_hash_tags(); ?>
				<ul id="hashtag-cloud" <?php if ( !isset( $_COOKIE['wordtwit-pro-hashcloud' ] ) ) { echo "style='display:none'"; } else { echo 'class="open"';} ?>>
					<?php if ( $hash_tags && count( $hash_tags ) ) { ?>
						<?php foreach( $hash_tags as $name => $tag ) { ?>
						<li class="<?php echo $name; ?>-hashtag">
							<span class="name"><?php echo $name; ?></span><span class="count"><?php echo $tag->count; ?></span>
						</li>
					<?php } ?>
				<?php } ?>
				</ul>
				<br class="clearer" />
			</div>
			<div class="wt-manual" style="display:none">
				<p><?php _e( "Tweet mode is manual", "wordtwit-pro" ); ?>.</p>
				<p><?php _e( "Reset to Automatic to re-enable hashtags", "wordtwit-pro" ); ?>.</p>
			</div>
		</div>
		
		<!-- schedule -->
		<div id="wt-schedule-box" class="wt-widget-bottom" <?php if ( wordtwit_post_box_cookie_check( 'wt-schedule' ) ) { echo "style='display:none'"; } ?>>
			<h4 class="wt-h4"><?php _e( "Schedule", "wordtwit-pro" ); ?></h4>
			<p><?php _e( "Publish this tweet", "wordtwit-pro" ); ?>: 
			<select id="wt-select-times">
				<option value="1"<?php if ( 1 == wordtwit_post_get_tweet_times() ) echo ' selected'; ?>>
					<?php _e( '1 time', 'wordtwit-pro' ); ?>
				</option>
				<?php for( $i = 2; $i <= 5; $i++ ) { ?>
					<option value="<?php echo $i; ?>"<?php if ( $i == wordtwit_post_get_tweet_times() ) echo ' selected'; ?>>
						<?php echo sprintf( __( '%d times', 'wordtwit-pro' ), $i ); ?>
					</option>
				<?php } ?>
			</select>
			</p>
			<div id="wt-sheduling">
				<p><?php _e( "Separated by", "wordtwit-pro" ); ?>:
				<select id="wt-select-mins">
					<?php for ( $i = 0; $i <= 45; $i += 15 ) { ?>
						<option value="<?php echo $i; ?>"<?php if ( wordtwit_post_get_tweet_seperated_mins() == $i ) echo ' selected';?>>
							<?php echo sprintf( __( '%d mins', 'wordtwit-pro' ), $i ); ?>
						</option>
					<?php } ?>
					<?php for ( $h = 1; $h <= 12; $h++ ) { ?>
						<?php for ( $i = 0; $i <= 30; $i += 30 ) { ?>
							<option value="<?php echo ( $h * 60 + $i ); ?>"<?php if ( wordtwit_post_get_tweet_seperated_mins() == ( $h*60 + $i ) ) echo ' selected';?>>
								<?php if ( $i == 0 ) { ?>
									<?php echo sprintf( _n( '%d hour', '%d hours', $h, 'wordtwit-pro' ), $h ); ?>
								<?php } else { ?>
									<?php echo sprintf( _n( '%d hour', '%d hours', $h, 'wordtwit-pro' ), $h ); ?><?php echo sprintf( __( ', %d mins', 'wordtwit-pro' ), $i ); ?>
								<?php } ?>
							</option>
						<?php } ?>
					<?php } ?>					
				</select>
			</p>
			<p class="howto"><?php _e( "Tweet URLs will be changed to pass Twitter's duplicate tweet restrictions.", "wordtwit-pro" ); ?></p>
		</div>
		<h4 class="wt-h4"><?php _e( "Delay", "wordtwit-pro" ); ?></h4>
			<p><?php _e( "Delay the first tweet by", "wordtwit-pro" ); ?>:
				<select id="wt-select-delay">
					<?php for ( $i = 0; $i <= 60; $i += 15 ) { ?>
						<option value="<?php echo $i; ?>"<?php if ( $i == wordtwit_post_get_tweet_delay() ) echo ' selected';?>>
							<?php echo sprintf( __( '%d mins', 'wordtwit-pro' ), $i ); ?>
						</option>
					<?php } ?>
					<?php for ( $i = 1; $i <= 12; $i += 1 ) { ?>
						<option value="<?php echo ( $i*60 ); ?>"<?php if ( ( $i * 60 ) == wordtwit_post_get_tweet_delay() ) echo ' selected';?>>
							<?php echo sprintf( _n( '%d hour', '%d hours', $i, 'wordtwit-pro' ), $i ); ?>
						</option>
					<?php } ?>
				</select>
			</p>
		</div><!-- schedule div -->
	</div><!-- wt-widget-bottom-wrap -->
		
	<?php if ( wordtwit_get_tweet_status() == WORDTWIT_TWEET_SCHEDULED ) { ?>
		<div class="wt-button-wrap">
			<a class="button wt-tweet-now-button" href="#"><?php _e( "Tweet Now", "wordtwit-pro" ); ?></a>
		</div>
	<?php } ?>
	</div><!-- disabled-wrapper -->
	
	<?php if ( ( wordtwit_get_tweet_status() == WORDTWIT_TWEET_PUBLISHED || wordtwit_get_tweet_status() == WORDTWIT_TWEET_IS_DEFERRED ) && wordtwit_has_post_active_accounts() ) { ?>
		<div class="wt-button-wrap">
			<a class="button wt-tweet-now-button" href="<?php wordtwit_the_retweet_post_url(); ?>"><?php _e( "Publish Now", "wordtwit-pro" ); ?></a>
		</div>		
	<?php } else if ( wordtwit_get_tweet_status() == WORDTWIT_TWEET_IS_OLD ) { ?>		
		<div class="wt-button-wrap">
			<a class="button wt-tweet-now-button" href="<?php wordtwit_the_publish_now_post_url(); ?>"><?php _e( "Publish Now", "wordtwit-pro" ); ?></a>
		</div>				
	<?php } ?>
</div>