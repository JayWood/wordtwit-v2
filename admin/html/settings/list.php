<select name="<?php wordtwit_the_tab_setting_name(); ?>" id="<?php wordtwit_the_tab_setting_name(); ?>" class="list">
	<?php while ( wordtwit_tab_setting_has_options() ) { ?>
		<?php wordtwit_tab_setting_the_option(); ?>
		
		<option value="<?php wordtwit_tab_setting_the_option_key(); ?>"<?php if ( wordtwit_tab_setting_is_selected() ) echo " selected"; ?>><?php wordtwit_tab_setting_the_option_desc(); ?></option>
	<?php } ?>
</select>

<label class="list" for="<?php wordtwit_the_tab_setting_name(); ?>">
	<?php wordtwit_the_tab_setting_desc(); ?>	
</label>
<?php if ( wordtwit_the_tab_setting_has_tooltip() ) { ?>
<a href="#" class="wordtwit-tooltip" title="<?php wordtwit_the_tab_setting_tooltip(); ?>">?</a>	
<?php } ?>