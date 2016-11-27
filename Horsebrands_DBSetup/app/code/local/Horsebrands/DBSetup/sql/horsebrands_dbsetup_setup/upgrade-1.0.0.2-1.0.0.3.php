<?php

$installer = $this;
$installer->startSetup();

/* HOMEPAGE */
$page = Mage::getModel('cms/page')->load('home','identifier');
if($page->getId()){$page->delete();}

$pagecontent = <<<EOF
<section>
  <div id="sequence" class="seq">
    <ul class="seq-canvas">
      <li class="step1">
      <div class="content-position top-right">
        <div class="content">
          <h1>Reitwesten</h1>
          <p>debitatiur, que laud<br/>
          Olorionet voluptatium</p>
          <div class="button-container"><a href="{{store url=''}}" class="btn medium btn-transparent white" style="width:200px; border-radius:0;">Shop now ></a></div>
        </div>
      </div>
      </li>
    </ul>
  </div>
</section>

<section>
<h5>Beliebte Produkte <i class="fa fa-heart"></i></h5>
<div>Hier Produkte, wenn mehr Artikel gepflegt sind!</div>
</section>

<section class="topics">
  <div class="row">
    <div class="columns small-12 medium-3">
      <article class="topic-element">
        <div class="canvas">
          <img src="{{skin url='images/teaser/Banner_1.jpg'}}" alt="banner" class="img-responsive full-width" />
        </div>
        <div class="button-container btn-centered">
          <a href="{{store url='reiter/turnierbekleidung.html'}}" class="btn medium btn-transparent white">Turnier ></a>
        </div>
      </article>
    </div>

    <div class="columns small-12 medium-6">
      <article class="topic-element">
        <div class="canvas">
          <img src="{{skin url='images/teaser/Banner_2.jpg'}}" alt="banner" class="img-responsive full-width" />
        </div>
        <div class="button-container btn-centered">
          <a href="{{store url='reiter.html'}}" class="btn medium btn-transparent white">Springen ></a>
        </div>
      </article>
    </div>

    <div class="columns small-12 medium-3">
      <article class="topic-element">
        <div class="canvas">
          <img src="{{skin url='images/teaser/Banner_3.jpg'}}" alt="banner" class="img-responsive full-width" />
        </div>
        <div class="button-container btn-centered">
          <a href="{{store url='reiter/oberteile/sweatshirts-pullover.html'}}" class="btn medium btn-transparent white">Pullover ></a>
        </div>
      </article>
    </div>
  </div>
  <div class="row">
    <div class="columns small-12 medium-3">
      <article class="topic-element">
        <div class="canvas">
          <img src="{{skin url='images/teaser/Banner_4.jpg'}}" alt="banner" class="img-responsive full-width" />
        </div>
        <div class="button-container btn-centered">
          <a href="{{store url='reiter/oberteile.html'}}" class="btn medium btn-transparent white">Outfits ></a>
        </div>
      </article>
    </div>

    <div class="columns small-12 medium-9">
      <article class="topic-element">
        <div class="canvas">
          <img src="{{skin url='images/teaser/Banner_5.jpg'}}" alt="banner" class="img-responsive full-width" />
        </div>
        <div class="button-container btn-right">
          <a href="{{store url='reiter/jacken-mantel-westen.html'}}" class="btn medium btn-transparent white">Jacken ></a>
        </div>
      </article>
    </div>
  </div>
</section>
EOF;

// $design = <<<EOF
// <reference name="head">
//       <action method="addItem"><type>skin_css</type><name>css/slick-theme.css</name></action>
//       <action method="addItem"><type>skin_css</type><name>css/slick.css</name></action>
//       <action method="addItem"><type>skin_js</type><name>js/slick.min.js</name></action>
//       <action method="addItem"><type>skin_css</type><name>css/grafenfels/review_styles.css</name></action>
// </reference>
// EOF;

$cmsPage = Mage::getModel('cms/page');
$cmsPage->setTitle('Horsebrands Shop');
$cmsPage->setIdentifier('home');
$cmsPage->setContent($pagecontent);
$cmsPage->setIsActive(true);
// $cmsPage->setLayoutUpdateXml($design);
$cmsPage->setStores(array(0));
$cmsPage->save();

$installer->endSetup();
