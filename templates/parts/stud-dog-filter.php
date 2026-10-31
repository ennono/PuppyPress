<?php
/**
 * Stud dog filter form.
 *
 * Override: {theme}/rudelwerk/parts/stud-dog-filter.php
 *
 * @package Rudelwerk
 * @version 1.0.0
 *
 * @var array $args {
 *     @type Rudelwerk\Frontend\StudDogFilter $filter Current filter.
 *     @type string                          $action Form action URL (empty: current page).
 *     @type array                           $sizes  Slug => name.
 *     @type array                           $hds    Slug => name.
 *     @type array                           $eds    Slug => name.
 *     @type array                           $exams  Slug => name.
 *     @type array                           $titles Slug => name.
 * }
 */

use Rudelwerk\Frontend\StudDogFilter;

defined( 'ABSPATH' ) || exit;

$rw_filter = $args['filter'];
$rw_action = $args['action'] ? $args['action'] : remove_query_arg( array( StudDogFilter::PARAM_ZIP, StudDogFilter::PARAM_SIZE, StudDogFilter::PARAM_HD, StudDogFilter::PARAM_ED, StudDogFilter::PARAM_EXAMS, StudDogFilter::PARAM_TITLES, StudDogFilter::PARAM_APPROVAL, 'paged' ) );

$rw_selects = array(
	StudDogFilter::PARAM_SIZE => array( __( 'Size', 'rudelwerk' ), $args['sizes'], $rw_filter->size ),
	StudDogFilter::PARAM_HD   => array( __( 'HD', 'rudelwerk' ), $args['hds'], $rw_filter->hd ),
	StudDogFilter::PARAM_ED   => array( __( 'ED', 'rudelwerk' ), $args['eds'], $rw_filter->ed ),
);

$rw_multi = array(
	StudDogFilter::PARAM_EXAMS  => array( __( 'Exams', 'rudelwerk' ), $args['exams'], $rw_filter->exams ),
	StudDogFilter::PARAM_TITLES => array( __( 'Titles', 'rudelwerk' ), $args['titles'], $rw_filter->titles ),
);
?>
<details class="rudelwerk-filter" <?php echo $rw_filter->is_active() ? 'open' : ''; ?>>
	<summary><?php esc_html_e( 'Filter', 'rudelwerk' ); ?></summary>

	<form class="rudelwerk-filter__form" action="<?php echo esc_url( $rw_action ); ?>" method="get">
		<div class="rudelwerk-filter__fields">
			<p class="rudelwerk-filter__field">
				<label for="rudelwerk-filter-zip"><?php esc_html_e( 'ZIP code', 'rudelwerk' ); ?></label>
				<input type="text" id="rudelwerk-filter-zip" name="<?php echo esc_attr( StudDogFilter::PARAM_ZIP ); ?>" value="<?php echo esc_attr( $rw_filter->zip ); ?>" inputmode="numeric" pattern="[0-9]{1,5}" title="<?php esc_attr_e( 'Please enter a ZIP code or the beginning of a ZIP code.', 'rudelwerk' ); ?>">
			</p>

			<?php foreach ( $rw_selects as $rw_param => $rw_select ) : ?>
				<?php list( $rw_label, $rw_options, $rw_value ) = $rw_select; ?>
				<?php if ( $rw_options ) : ?>
					<p class="rudelwerk-filter__field">
						<label for="rudelwerk-filter-<?php echo esc_attr( $rw_param ); ?>"><?php echo esc_html( $rw_label ); ?></label>
						<select id="rudelwerk-filter-<?php echo esc_attr( $rw_param ); ?>" name="<?php echo esc_attr( $rw_param ); ?>">
							<option value=""><?php esc_html_e( 'All', 'rudelwerk' ); ?></option>
							<?php foreach ( $rw_options as $rw_slug => $rw_name ) : ?>
								<option value="<?php echo esc_attr( $rw_slug ); ?>" <?php selected( $rw_value, (string) $rw_slug ); ?>><?php echo esc_html( $rw_name ); ?></option>
							<?php endforeach; ?>
						</select>
					</p>
				<?php endif; ?>
			<?php endforeach; ?>

			<?php foreach ( $rw_multi as $rw_param => $rw_select ) : ?>
				<?php list( $rw_label, $rw_options, $rw_values ) = $rw_select; ?>
				<?php if ( $rw_options ) : ?>
					<fieldset class="rudelwerk-filter__field rudelwerk-filter__field--multi">
						<legend><?php echo esc_html( $rw_label ); ?> <small>(<?php esc_html_e( 'all selected must apply', 'rudelwerk' ); ?>)</small></legend>
						<?php foreach ( $rw_options as $rw_slug => $rw_name ) : ?>
							<label>
								<input type="checkbox" name="<?php echo esc_attr( $rw_param ); ?>[]" value="<?php echo esc_attr( $rw_slug ); ?>" <?php checked( in_array( (string) $rw_slug, $rw_values, true ) ); ?>>
								<?php echo esc_html( $rw_name ); ?>
							</label>
						<?php endforeach; ?>
					</fieldset>
				<?php endif; ?>
			<?php endforeach; ?>

			<p class="rudelwerk-filter__field rudelwerk-filter__field--checkbox">
				<label>
					<input type="checkbox" name="<?php echo esc_attr( StudDogFilter::PARAM_APPROVAL ); ?>" value="1" <?php checked( $rw_filter->approval ); ?>>
					<?php esc_html_e( 'Approval available', 'rudelwerk' ); ?>
				</label>
			</p>
		</div>

		<p class="rudelwerk-filter__actions">
			<button type="submit" class="button"><?php esc_html_e( 'Apply', 'rudelwerk' ); ?></button>
			<?php if ( $rw_filter->is_active() ) : ?>
				<a class="rudelwerk-filter__reset" href="<?php echo esc_url( $rw_action ); ?>"><?php esc_html_e( 'Reset', 'rudelwerk' ); ?></a>
			<?php endif; ?>
		</p>
	</form>
</details>
