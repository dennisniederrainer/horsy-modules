<?php

class Horsebrands_Invitefriends_Model_Resource_Invite_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

  protected function _construct() {
    $this->_init('invitefriends/invite');
  }
}
