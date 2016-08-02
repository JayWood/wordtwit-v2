<?php while ( wordtwit_has_tabs() ) { ?>
	<?php wordtwit_the_tab(); ?>
	
	<div id="pane-content-pane-<?php wordtwit_the_tab_id(); ?>" class="pane-content" style="display: none;">
		<div class="left-area">
			<ul>
				<?php while ( wordtwit_has_tab_sections() ) { ?>
					<?php wordtwit_the_tab_section(); ?>
					<li><a id="tab-section-<?php wordtwit_the_tab_section_class_name(); ?>" rel="<?php wordtwit_the_tab_section_class_name(); ?>" href="#"><?php wordtwit_the_tab_name(); ?></a></li>
				<?php } ?>
			</ul>
		</div>
		<div class="right-area">
			<?php wordtwit_rewind_tab_settings(); ?>
			
			<?php while ( wordtwit_has_tab_sections() ) { ?>
				<?php wordtwit_the_tab_section(); ?>

				<div style="display: none;" class="setting-right-section" id="setting-<?php wordtwit_the_tab_section_class_name(); ?>">
					<?php while ( wordtwit_has_tab_section_settings() ) { ?>
						<?php wordtwit_the_tab_section_setting(); ?>

						<div class="wordtwit-setting type-<?php wordtwit_the_tab_setting_type(); ?>"<?php if ( wordtwit_get_tab_setting_class_name() ) echo ' id="setting_' .  wordtwit_get_tab_setting_class_name() . '"'; ?>>
							
							<?php if ( file_exists( dirname( __FILE__ ) . '/settings/' . wordtwit_get_tab_setting_type() . '.php' ) ) { ?>
								<?php include( 'settings/' . wordtwit_get_tab_setting_type() . '.php' ); ?>
							<?php } else { ?>
								<?php do_action( 'wordtwit_show_custom_setting', wordtwit_get_tab_setting_type() ); ?>
							<?php } ?>
						</div>
					<?php } ?>
				</div>				
			<?php } ?>	
			
			<br class="clearer" />		
		</div>
		<br class="clearer" />
	</div>
<?php } ?>