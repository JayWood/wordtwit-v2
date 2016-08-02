
<div class='wordtwit-setting' id='twitboard'>
	<div class="box-holder round-3" id="right-now-box">

		<h3><?php _e( "Right Now", "wordtwit-pro" ); ?></h3>

		<p class="sub"><?php _e( "At a Glance", "wordtwit-pro" ); ?></p>

		<table class="fonty">
			<tbody>
				<tr>
					<td class="box-table-number"><a href="admin.php?page=tweet_queue"><?php wordtwit_the_bloginfo( 'published_tweets' ); ?></a></td>
					<td class="box-table-text"><a href="admin.php?page=tweet_queue"><?php echo _n( "Published Tweet", "Published Tweets", wordtwit_get_bloginfo( 'published_tweets' ), "wordtwit-pro" ); ?></a></td>
				</tr>	
				<tr>
					<td class="box-table-number"><a href="admin.php?page=tweet_queue"><?php wordtwit_the_bloginfo( 'scheduled_tweets' ); ?></a></td>
					<td class="box-table-text"><a href="admin.php?page=tweet_queue"><?php echo _n( "Scheduled Tweet", "Scheduled Tweets", wordtwit_get_bloginfo( 'scheduled_tweets' ), "wordtwit-pro" ); ?></a></td>
				</tr>
				<tr>
					<td class="box-table-number"><a href="admin.php?page=wordtwit_account_configuration"><?php wordtwit_the_bloginfo( 'total_accounts' ); ?></a></td>
					<td class="box-table-text"><a href="admin.php?page=wordtwit_account_configuration"><?php echo _n( "Twitter Account", "Twitter Accounts", wordtwit_get_bloginfo( 'total_accounts' ), "wordtwit-pro" ); ?></a></td>
				</tr>
			</tbody>
		</table>

		<div id="touchboard-ajax">&nbsp;</div>
		
	</div><!-- box-holder -->

	<div class="box-holder loading round-3" id="blog-news-box">
		<h3><?php _e( "BraveNewCode News", "wordtwit-pro" ); ?></h3>

		<p class="sub"><?php _e( "From the BraveNewCode Blog", "wordtwit-pro" ); ?></p>

		<div id="blog-news-box-ajax"></div>

	</div><!-- box-holder -->

	<br class="clearer" />

</div><!-- wordtwit-setting -->
