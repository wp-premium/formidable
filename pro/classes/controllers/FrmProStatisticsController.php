<?php

class FrmProStatisticsController{

    public static function show() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::show_reports' );
        FrmProGraphsController::show_reports();
    }

    public static function get_daily_entries() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::show_reports' );
        return '';
    }

    public static function graph_shortcode( $atts ) {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return FrmProGraphsController::graph_shortcode( $atts );
    }

    public static function get_google_graph() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function get_graph_values() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function convert_to_google() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function get_fields() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function get_form_posts() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function get_entry_ids() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function get_x_field() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function get_x_axis_inputs() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function get_graph_cols() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function get_graph_options() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function clean_inputs() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function mod_post_inputs() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function mod_x_inputs() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function format_f_inputs() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function get_user_id_values() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function get_final_x_axis_values(){
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function combine_dates(){
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function graph_by_period() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function get_multiple_id_values() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function get_x_axis_values(){
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function get_count_values() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function get_generic_inputs() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function field_opt_order_vals() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }

    public static function get_displayed_values() {
        _deprecated_function( __FUNCTION__, '2.01.02', 'FrmProGraphsController::graph_shortcode' );
        return '';
    }



    /**
	 * Returns stats requested through the [frm-stats] shortcode
	 *
	 * @param array $atts
     * @return string
	 */
    public static function stats_shortcode($atts){
        $defaults = array(
            'id' => false, //the ID of the field to show stats for
            'type' => 'total', //total, count, average, median, deviation, star, minimum, maximum, unique
            'user_id' => false, //limit the stat to a specific user id or "current"
            'value' => false, //only count entries with a specific value
            'round' => 100, //how many digits to round to
            'limit' => '', //limit the number of entries used in this calculation
            'drafts' => false, //don't include drafts by default
            //any other field ID in the form => the value it should be equal to
            //'entry_id' => show only for a specific entry ie if you want to show a star rating for a single entry
            //'thousands_sep' => set thousands separator

        );

        $sc_atts = shortcode_atts($defaults, $atts);
        // Combine arrays - DO NOT use array_merge here because numeric keys are renumbered
		$atts = (array) $atts + (array) $sc_atts;

        if ( ! $atts['id'] ) {
            return '';
        }

        $atts['user_id'] = FrmAppHelper::get_user_id_param($atts['user_id']);

        $new_atts = $atts;
        foreach ( $defaults as $unset => $val ) {
            unset($new_atts[$unset]);
        }

        return FrmProFieldsHelper::get_field_stats(
            $atts['id'], $atts['type'], $atts['user_id'], $atts['value'],
            $atts['round'], $atts['limit'], $new_atts, $atts['drafts']
        );
    }

}
