<?php
/**
 * Stud dog filter form.
 *
 * Override: {theme}/puppypress/parts/stud-dog-filter.php
 *
 * @package PuppyPress
 * @version 1.0.0
 *
 * @var array $args {
 *     @type PuppyPress\Frontend\StudDogFilter $filter Current filter.
 *     @type string                          $action Form action URL (empty: current page).
 *     @type array                           $sizes  Slug => name.
 *     @type array                           $hds    Slug => name.
 *     @type array                           $eds    Slug => name.
 *     @type array                           $exams  Slug => name.
 *     @type array                           $titles Slug => name.
 * }
 */

use PuppyPress\Frontend\StudDogFilter;

defined( 'ABSPATH' ) || exit;

$pp_filter = $args['filter'];
$pp_action = $args['action'] ? $args['action'] : remove_query_arg( array( StudDogFilter::PARAM_ZIP, StudDogFilter::PARAM_SIZE, StudDogFilter::PARAM_HD, StudDogFilter::PARAM_ED, StudDogFilter::PARAM_EXAMS, StudDogFilter::PARAM_TITLES, StudDogFilter::PARAM_APPROVAL, 'paged' ) );

$pp_selects = array(
	StudDogFilter::PARAM_SIZE => array( __( 'Size', 'puppypress' ), $args['sizes'], $pp_filter->size ),
	StudDogFilter::PARAM_HD   => array( __( 'HD', 'puppypress' ), $args['hds'], $pp_filter->hd ),
	StudDogFilter::PARAM_ED   => array( __( 'ED', 'puppypress' ), $args['eds'], $pp_filter->ed ),
);

$pp_multi = array(
	StudDogFilter::PARAM_EXAMS  => array( __( 'Exams', 'puppypress' ), $args['exams'], $pp_filter->exams ),
	StudDogFilter::PARAM_TITLES => array( __( 'Titles', 'puppypress' ), $args['titles'], $pp_filter->titles ),
);
?>
<details class="puppypress-filter" <?php echo $pp_filter->is_active() ? 'open' : ''; ?>>
	<summary><?php esc_html_e( 'Filter', 'puppypress' ); ?></summary>

	<form class="puppypress-filter__form" action="<?php echo esc_url( $pp_action ); ?>" method="get">
		<div class="puppypress-filter__fields">
			<p class="puppypress-filter__field">
				<label for="puppypress-filter-zip"><?php esc_html_e( 'ZIP code', 'puppypress' ); ?></label>
				<input type="text" id="puppypress-filter-zip" name="<?php echo esc_attr( StudDogFilter::PARAM_ZIP ); ?>" value="<?php echo esc_attr( $pp_filter->zip ); ?>" inputmode="numeric" pattern="[0-9]{1,5}" title="<?php esc_attr_e( 'Please enter a ZIP code or the beginning of a ZIP code.', 'puppypress' ); ?>">
			</p>

			<?php foreach ( $pp_selects as $pp_param => $pp_select ) : ?>
				<?php list( $pp_label, $pp_options, $pp_value ) = $pp_select; ?>
				<?php if ( $pp_options ) : ?>
					<p class="puppypress-filter__field">
						<label for="puppypress-filter-<?php echo esc_attr( $pp_param ); ?>"><?php echo esc_html( $pp_label ); ?></label>
						<select id="puppypress-filter-<?php echo esc_attr( $pp_param ); ?>" name="<?php echo esc_attr( $pp_param ); ?>">
							<option value=""><?php esc_html_e( 'All', 'puppypress' ); ?></option>
							<?php foreach ( $pp_options as $pp_slug => $pp_name ) : ?>
								<option value="<?php echo esc_attr( $pp_slug ); ?>" <?php selected( $pp_value, (string) $pp_slug ); ?>><?php echo esc_html( $pp_name ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
				<?php endif; ?>
			<?php endforeach; ?>

			<?php foreach ( $pp_multi as $pp_param => $pp_select ) : ?>
				<?php list( $pp_label, $pp_options, $pp_values ) = $pp_select; ?>
				<?php if ( $pp_options ) : ?>
					<fieldset class="puppypress-filter__field puppypress-filter__field--multi">
						<legend><?php echo esc_html( $pp_label ); ?> <small>(<?php esc_html_e( 'all selected must apply', 'puppypress' ); ?>)</small></legend>
						<?php foreach ( $pp_options as $pp_slug => $pp_name ) : ?>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $pp_param ); ?>[]" value="<?php echo esc_attr( $pp_slug ); ?>" <?php checked( in_array( (string) $pp_slug, $pp_values, true ) ); ?>>
								<?php echo esc_html( $pp_name ); ?>
							</label>
						<?php endforeach; ?>
					</fieldset>
				<?php endif; ?>
			<?php endforeach; ?>

			<p class="puppypress-filter__field puppypress-filter__field--checkbox">
				<label>
					<input type="checkbox" name="<?php echo esc_attr( StudDogFilter::PARAM_APPROVAL ); ?>" value="1" <?php checked( $pp_filter->approval ); ?>>
					<?php esc_html_e( 'Approval available', 'puppypress' ); ?>
				</label>
			</p>
		</div>

		<p class="puppypress-filter__actions">
			<button type="submit" class="button"><?php esc_html_e( 'Apply', 'puppypress' ); ?></button>
			<?php if ( $pp_filter->is_active() ) : ?>
				<a class="puppypress-filter__reset" href="<?php echo esc_url( $pp_action ); ?>"><?php esc_html_e( 'Reset', 'puppypress' ); ?></a>
			<?php endif; ?>
		</p>
	</form>
</details>
