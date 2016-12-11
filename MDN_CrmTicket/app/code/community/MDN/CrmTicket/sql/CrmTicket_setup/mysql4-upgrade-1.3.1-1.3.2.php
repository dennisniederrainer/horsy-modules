<?php

$installer = $this;
$installer->startSetup();

$installer->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('crm_tag')}  (
`ctg_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`ctg_name` VARCHAR( 30 ) NOT NULL,
`ctg_bg_color` VARCHAR( 6 ) NOT NULL,
`ctg_text_color` VARCHAR( 6 ) NOT NULL,
INDEX (  `ctg_id` )
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS {$this->getTable('crm_ticket_tag')}  (
`ctt_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`ctt_ct_id` INT NOT NULL ,
`ctt_ctg_id` INT NOT NULL,
INDEX (  `ctt_id` )
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

INSERT INTO {$this->getTable('crm_tag')} (`ctg_id`, `ctg_name`, `ctg_bg_color`, `ctg_text_color`) VALUES
(1, 'URGENT', 'FE2E2E', 'FFFFFF'),
(2, 'QUOTE', '088A29', 'FFFFFF'),
(3, 'RMA', 'FF8000', '000000'),
(4, 'FAVORITE', 'FFFF00', '000000'),
(5, 'LEAD', '8000FF', 'FFFFFF');

"

);

$installer->getConnection()->addColumn($installer->getTable('crm_email_router_rule'), 'crr_tag_id', 'int(11) NOT NULL');


$installer->endSetup();
