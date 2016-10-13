<?php

$installer = $this;
$installer->startSetup();

// footer content
$block = Mage::getModel('cms/block')->load('topline_content','identifier');
if($block->getId()){$block->delete();}

$pagecontent = <<<EOF
<span>Kostenlose Hotline {{config path="general/store_information/phone"}}</span>
<span>service@horsebrands.de</span>
<span>14 Tage Rückgaberecht</span>
<span>Versandkostenfrei ab einem Bestellwert von 30€</span>
EOF;

$cmsBlock = Mage::getModel('cms/block');
$cmsBlock->setTitle('Horsebrands Topline');
$cmsBlock->setIdentifier('topline_content');
$cmsBlock->setContent($pagecontent);
$cmsBlock->setIsActive(true);
$cmsBlock->setStores(array(0));
$cmsBlock->save();

$installer->endSetup();
