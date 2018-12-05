<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php echo $this->menu_title ?></h1>
	<form method="POST" action="options.php">
		<?php
			settings_fields( $this->menu_slug );
			do_settings_sections( $this->menu_slug );
			submit_button( 'Save setting' );
		?>
	</form>
</div>
<style>
	.form-table textarea[type="textarea"] {
		margin-left: 10px;
		width: 90%;
		max-width: 500px;
	}
	.form-table p[class="description"] {
		margin-left: 10px;
	}
</style>
