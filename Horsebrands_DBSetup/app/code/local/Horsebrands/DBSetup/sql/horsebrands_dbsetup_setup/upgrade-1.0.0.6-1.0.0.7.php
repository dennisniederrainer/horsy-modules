<?php

$installer = $this;
$installer->startSetup();

/* Dein Verein References */
$block = Mage::getModel('cms/block')->load('deinverein-register-description','identifier');
if($block->getId()){$block->delete();}

$pagecontent = <<<EOF
<p>Hier eine charmante Beschreibung.</p>
EOF;

$cmsBlock = Mage::getModel('cms/block');
$cmsBlock->setTitle('Dein Verein | Anmeldung Beschreibungstext');
$cmsBlock->setIdentifier('deinverein-register-description');
$cmsBlock->setContent($pagecontent);
$cmsBlock->setIsActive(true);
$cmsBlock->setStores(array(0));
$cmsBlock->save();


/* Dein Verein Anmeldung */
$page = Mage::getModel('cms/page')->load('anmeldung','identifier');
if($page->getId()){$page->delete();}

$pagecontent = <<<EOF
<div class="deinverein-container">
  <div class="teaser-wrapper">
    <img src="http://placehold.it/1280x400?text=JETZT+ANMELDEN!" alt="Teaser Image" />
  </div>
  <div class="deinverein-register-description">
    {{block type="cms/block" block_id="deinverein-register-description"}}
  </div>
  {{block type="core/template" template="deinverein/register/form.phtml"}}
</div>
EOF;

$design = <<<EOF
<remove name="breadcrumbs" />
EOF;

$cmsPage = Mage::getModel('cms/page');
$cmsPage->setTitle('Dein Verein Anmeldung');
$cmsPage->setIdentifier('anmeldung');
$cmsPage->setContent($pagecontent);
$cmsPage->setIsActive(true);
$cmsPage->setLayoutUpdateXml($design);
$cmsPage->setStores(array(0));
$cmsPage->save();

$installer->endSetup();
