<?php
class FrmProSettings extends FrmSettings{
    public $option_name = 'frmpro_options';

    // options
    public $edit_msg;
    public $update_value;
    public $already_submitted;
    public $cal_date_format;
    public $date_format;

    /**
     * @return array
     */
	function default_options() {
        return array(
            'edit_msg'          => __( 'Your submission was successfully saved.', 'formidable' ),
            'update_value'      => __( 'Update', 'formidable' ),
            'already_submitted' => __( 'You have already submitted that form', 'formidable' ),
			'date_format'       => 'm/d/Y',
			'cal_date_format'   => $this->get_cal_date(),
        );
    }

    function set_default_options() {
        $this->fill_with_defaults();
    }

	function update( $params ) {
        $this->date_format = $params['frm_date_format'];
        $this->get_cal_date();

        $this->fill_with_defaults($params);
    }

	/**
	 * Get the conversions from php date format to datepicker
	 * Set the cal_date_format to make sure it's not empty
	 *
	 * @since 2.0.2
	 */
	function get_cal_date() {
		$formats = FrmProAppHelper::display_to_datepicker_format();
		if ( isset( $formats[ $this->date_format ] ) ) {
			$this->cal_date_format = $formats[ $this->date_format ];
		} else {
			$this->cal_date_format = 'mm/dd/yy';
		}
	}

	function store() {
        // Save the posted value in the database
        update_option( $this->option_name, $this);

        delete_transient($this->option_name);
        set_transient($this->option_name, $this);
    }
}