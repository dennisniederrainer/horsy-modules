<?php

class Horsebrands_Invitefriends_Model_Resource_Invite extends Mage_Core_Model_Resource_Db_Abstract {

  public function _construct() {
    $this->_init('invitefriends/invite', 'invite_id');
  }
}
