<input type="text" autocomplete="off" class="text" id="<?php wordtwit_the_tab_setting_name(); ?>" name="<?php wordtwit_the_tab_setting_name(); ?>" value="<?php wordtwit_the_tab_setting_value(); ?>" />
<label class="text" for="<?php wordtwit_the_tab_setting_name(); ?>">
	<?php wordtwit_the_tab_setting_desc(); ?>
</label>			
<?php if ( wordtwit_the_tab_setting_has_tooltip() ) { ?>
<a href="#" class="wordtwit-tooltip" title="<?php wordtwit_the_tab_setting_tooltip(); ?>">?</a> 
<?php } ?>