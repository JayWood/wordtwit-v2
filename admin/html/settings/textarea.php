<label class="textarea" for="<?php wordtwit_the_tab_setting_name(); ?>">
	<?php wordtwit_the_tab_setting_desc(); ?>
</label>

<?php if ( wordtwit_the_tab_setting_has_tooltip() ) { ?>
<a href="#" class="wordtwit-tooltip" title="<?php wordtwit_the_tab_setting_tooltip(); ?>">?</a>
<?php } ?><br />	
<textarea rows="5" class="textarea"  id="<?php wordtwit_the_tab_setting_name(); ?>" name="<?php wordtwit_the_tab_setting_name(); ?>"><?php echo htmlspecialchars( wordtwit_get_tab_setting_value() ); ?></textarea>