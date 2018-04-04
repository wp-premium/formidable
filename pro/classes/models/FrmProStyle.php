<?php
class FrmProStyle extends FrmStyle {
    function duplicate( $id ) {
        $this->id = $id;
        $default_style = $this->get_one();
        $style = $this->get_new();

        $style->post_content = array_merge( $style->post_content, $default_style->post_content );

        return $style;
    }
}
