<?php

class Horsebrands_ProductReturn_Block_Front_NewRequest extends MDN_ProductReturn_Block_Front_NewRequest {

	public function getReasonSelect($name)
    {
        $retour  = '<select class="select-reason" name="' . $name . '" id="' . $name . '">';
        $reasons = mage::getModel('ProductReturn/RmaProducts')->getReasons(Mage::app()->getStore()->getStoreId());

        $retour .= '<option value="" disabled>Bitte ausw&auml;hlen</option>';
        foreach ($reasons as $key => $label) {
            $selected = '';
            $retour .= '<option value="' . $key . '" ' . $selected . '>' . $label . '</option>';
        }

        $retour .= '</select>';

        return $retour;

    }

	public function getQtySelect($name, $max) {
        $retour = '<select name="' . $name . '" id="' . $name . '">';

		if($max > 0) {
			$retour .= '<option value="0" selected>Bitte w&auml;hlen...</option>';
	        for ($i = 1; $i <= $max; $i++) {
	            $retour .= '<option value="' . $i . '">' . $i . '</option>';
	        }

		} else {
			$retour .= '<option value="0" selected>Artikel bereits zur&uuml;ckgesendet.</option>';
		}

        $retour .= '</select>';

        return $retour;
    }

}
