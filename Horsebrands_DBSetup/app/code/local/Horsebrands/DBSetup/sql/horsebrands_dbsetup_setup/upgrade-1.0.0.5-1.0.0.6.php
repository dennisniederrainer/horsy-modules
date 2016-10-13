<?php

$installer = $this;
$installer->startSetup();

/* Dein Verein Home */
$page = Mage::getModel('cms/page')->load('deinverein-home','identifier');
if($page->getId()){$page->delete();}

$pagecontent = <<<EOF
<div class="deinverein-container">
  <div class="teaser-wrapper">
    <img src="http://placehold.it/1280x400" alt="Teaser Image" />
    <div class="buttons-container text-center">
      <a href="{{store url='deinverein-anmeldung'}}" class="button large black-transparent">Dein Verein anmelden ></a>
    </div>
  </div>
  <div class="deinverein-vorteile-wrapper section grey-bg">
    <div class="line-through-heading">
      <h1 class="uppercase grey-bg">Deine Vorteile auf einen Blick</h1>
    </div>
    <div class="content-wrapper">
      <div class="row">
        <div class="columns small-12 medium-6 large-3">
          <div class="usp-column">
            <div class="usp-icon-header">
              <img src="http://placehold.it/100x100" alt="Icon" />
            </div>
            <h3 class="uppercase">Druck & Stick</h3>
            <p>
              Habemus omnes antelis? Habem sime quo publin vit.Unu sulum oravem tanu me maio ina, nondu-cotia? Forum facci tarei sesit vo, ce quasdacrena, nostabiti
            </p>
          </div>
        </div>
        <div class="columns small-12 medium-6 large-3">
          <div class="usp-column">
            <div class="usp-icon-header">
              <img src="http://placehold.it/100x100" alt="Icon" />
            </div>
            <h3 class="uppercase">Eigener Vereinsshop</h3>
            <p>
              Habemus omnes antelis? Habem sime quo publin vit.Unu sulum oravem tanu me maio ina, nondu-cotia? Forum facci tarei sesit vo, ce quasdacrena, nostabiti
            </p>
          </div>
        </div>
        <div class="columns small-12 medium-6 large-3">
          <div class="usp-column">
            <div class="usp-icon-header">
              <img src="http://placehold.it/100x100" alt="Icon" />
            </div>
            <h3 class="uppercase">Kostenfrei</h3>
            <p>
              Habemus omnes antelis? Habem sime quo publin vit.Unu sulum oravem tanu me maio ina, nondu-cotia? Forum facci tarei sesit vo, ce quasdacrena, nostabiti
            </p>
          </div>
        </div>
        <div class="columns small-12 medium-6 large-3">
          <div class="usp-column">
            <div class="usp-icon-header">
              <img src="http://placehold.it/100x100" alt="Icon" />
            </div>
            <h3 class="uppercase">Immer verfügbar</h3>
            <p>
              Habemus omnes antelis? Habem sime quo publin vit.Unu sulum oravem tanu me maio ina, nondu-cotia? Forum facci tarei sesit vo, ce quasdacrena, nostabiti
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="deinverein-produkte-wrapper section">
    <div class="line-through-heading">
      <h1 class="uppercase">Unsere Produkte - Was wählst Du?</h1>
    </div>
{{block type="catalog/product_list" product_skus="shw005,shw003,shirt001" template="catalog/product/list/deinverein_featured_products.phtml"}}
    <div class="buttons-container text-center">
      <a href="{{store url='deinverein-produkte'}}" class="button large black-transparent">Mehr Produkte ></a>
    </div>
  </div>
  <div class="deinverein-referenzen-wrapper section grey-bg">
    <div class="line-through-heading">
      <h1 class="uppercase grey-bg">Referenzen</h1>
    </div>
    {{block type="core/template" template="deinverein/references.phtml"}}
  </div>
  <div class="deinverein-seo-wrapper section">
    {{block type="cms/block" block_id="deinverein-home-seo"}}
  </div>
</div>
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
$cmsPage->setTitle('Dein Verein Home');
$cmsPage->setIdentifier('deinverein-home');
$cmsPage->setContent($pagecontent);
$cmsPage->setIsActive(true);
// $cmsPage->setLayoutUpdateXml($design);
$cmsPage->setStores(array(0));
$cmsPage->save();

/* Dein Verein References */
$block = Mage::getModel('cms/block')->load('deinverein-references','identifier');
if($block->getId()){$block->delete();}

$pagecontent = <<<EOF
<div id="deinverein-references">
  <div class="deinverein-reference">
    <img src="http://placehold.it/150x75" alt="Referenz"  />
  </div>
  <div class="deinverein-reference">
    <img src="http://placehold.it/150x75" alt="Referenz"  />
  </div>
  <div class="deinverein-reference">
    <img src="http://placehold.it/150x75" alt="Referenz"  />
  </div>
  <div class="deinverein-reference">
    <img src="http://placehold.it/150x75" alt="Referenz"  />
  </div>
  <div class="deinverein-reference">
    <img src="http://placehold.it/150x75" alt="Referenz"  />
  </div>
  <div class="deinverein-reference">
    <img src="http://placehold.it/150x75" alt="Referenz"  />
  </div>
  <div class="deinverein-reference">
    <img src="http://placehold.it/150x75" alt="Referenz"  />
  </div>
</div>
EOF;

$cmsBlock = Mage::getModel('cms/block');
$cmsBlock->setTitle('Dein Verein Referenzen');
$cmsBlock->setIdentifier('deinverein-references');
$cmsBlock->setContent($pagecontent);
$cmsBlock->setIsActive(true);
$cmsBlock->setStores(array(0));
$cmsBlock->save();

/* Dein Verein References */
$block = Mage::getModel('cms/block')->load('deinverein-home-seo','identifier');
if($block->getId()){$block->delete();}

$pagecontent = <<<EOF
<div class="row">
  <div class="columns medium-3 small-12">
    <div class="seo-text">
      <h2>Seo Text</h2>
      <p>
        asitiores moloratur, sincius vendes dunt. Uciditas undende ndipsapitem hit, oditae sequam et di aut aliqui consequi omnimilles sin eaque voluptate pa etur sam excea no- net, eveliquia core, si conseque atiatatius. Dolum laboreicipsa quam, quae eum cons- ersped qui re et alit venim volupta quis- sum quatem doluptae lam ipidemposto teceatquam velestiist voles enducit unt es rercipsum, consend andemposae. Faccul- parum sum fuga. Nam que nime es corita venimpeditis iliquas et debitibusto volupta sperum qui nostior iorate nos num nihil min consed que maiorectur?
        Pa nus cum venis autem int oditamet rem quas era seque cusant fugiatur?
        To dolupta estrum, si ipis minit mint ex eatusapel is non plia as exerent modis eos dolore expla eosaerum inturerum veni iunti ab iusam que si nem harum doluptaspis
        dit Busciissitate et am im aut lab illacerum quaspe moluptia pra aut volupta dolupta dolupide cus, quo comnim ut prae vo-
        loreh enimint iunt et aut am faccusae est, idestotaqui velis ad mo officitam, que cor mossitatia quunt et rendel idus volorrum necuptas res re omnit etureruptas qui aut poreria sam estia solupti aspitatem et aspi- cia simaxim dere odi tenis molupta tinctem- pe pore etur sunto is sit dunt rerspere ped quam, que pa explabo rempori busande
      </p>
    </div>
  </div>
  <div class="columns medium-6 small-12">
    <div class="seo-text">
      <h2>Seo Text</h2>
      <p>
        Cipic tem quae eum earchilit eumquat iberupta namenditati temquid uciet, as vendebist, exerovit dolor magniae. Unto endaes exerit dis abo. Ebitaspe odic totam fuga. Sitae debis ex endis pore porem. Taquam, omnis iumquibusci ut voloreicias experibus as core exerum audit aut possimodi ommoluptist, aut aped ma con reptatem labo. Ut volor aut est, oc- cumque pla sam et ut aceati duntur?
        Demque rest, acepuda sum reprepelibus ut intus is mo et volendu citibus volo dolorib ereseque nis cum secaborum quos aut qui venis nestis magnimet porro quiatem porepel laborro ribust et resed modion cusam, volupiet fuga. Pa vendis ministrum, conse acias as aliquia ditiore, consendis explaut emporestrum eos es vernatq uasimil igendae int occulli
      </p>
    </div>
    <div class="seo-text">
      <h2>Seo Text</h2>
      <p>
        Cipic tem quae eum earchilit eumquat iberupta namenditati temquid uciet, as vendebist, exerovit dolor magniae. Unto endaes exerit dis abo. Ebitaspe odic totam fuga. Sitae debis ex endis pore porem. Taquam, omnis iumquibusci ut voloreicias experibus as core exerum audit aut possimodi ommoluptist, aut aped ma con reptatem labo. Ut volor aut est, oc- cumque pla sam et ut aceati duntur?
        Demque rest, acepuda sum reprepelibus ut intus is mo et volendu citibus volo dolorib ereseque nis cum secaborum quos aut qui venis nestis magnimet porro quiatem porepel laborro ribust et resed modion cusam, volupiet fuga. Pa vendis ministrum, conse acias as aliquia ditiore, consendis explaut emporestrum eos es vernatq uasimil igendae int occulli Nequia di ipsa nit quisci dolupit ibusam sunt min con res dolorer fernati onsere nus si rep- tiberion netur, sit maximpelicti tem nobitat et haribea iumque lant, erferum quam harupid ex eum auta pa eos et que volent aut eum ea perumet maiosapid quae nectur? Ipsae corem vel ipienih illaudi imi, corist, que doluptatqui nustiost, cum qui delique ma dunt atem etur auta et que dolorepel ipsaepra nis vendi iur audae corpore restibus nonseque apiciaspic temquisitat.
        Molore nonsequae latem etus es maximusant doluptam id ut odi quidest, omnimustias mos int est, te comnimi nvelignimus pratur asperfe rspiderunti ad quat eliqui berat labo- reius.
        Ipsapitas et voloremo blatur?
        Iquunte net officipit accaece atiscim veles sam sum quunt min nossitio. Minimil itatem
      </p>
    </div>
  </div>
  <div class="columns medium-3 small-12">
    <div class="seo-text">
      <h2>Seo Text</h2>
      <p>
        asitiores moloratur, sincius vendes dunt. Uciditas undende ndipsapitem hit, oditae sequam et di aut aliqui consequi omnimilles sin eaque voluptate pa etur sam excea no- net, eveliquia core, si conseque atiatatius. Dolum laboreicipsa quam, quae eum cons- ersped qui re et alit venim volupta quis- sum quatem doluptae lam ipidemposto teceatquam velestiist voles enducit unt es rercipsum, consend andemposae. Faccul- parum sum fuga. Nam que nime es corita venimpeditis iliquas et debitibusto volupta sperum qui nostior iorate nos num nihil min consed que maiorectur?
        Pa nus cum venis autem int oditamet rem quas era seque cusant fugiatur?
        To dolupta estrum, si ipis minit mint ex eatusapel is non plia as exerent modis eos dolore expla eosaerum inturerum veni iunti ab iusam que si nem harum doluptaspis
        dit Busciissitate et am im aut lab illacerum quaspe moluptia pra aut volupta dolupta dolupide cus, quo comnim ut prae vo-
        loreh enimint iunt et aut am faccusae est, idestotaqui velis ad mo officitam, que cor mossitatia quunt et rendel idus volorrum necuptas res re omnit etureruptas qui aut poreria sam estia solupti aspitatem et aspi- cia simaxim dere odi tenis molupta tinctem- pe pore etur sunto is sit dunt rerspere ped quam, que pa explabo rempori busande
      </p>
    </div>
  </div>
</div>
EOF;

$cmsBlock = Mage::getModel('cms/block');
$cmsBlock->setTitle('Dein Verein SEO Block');
$cmsBlock->setIdentifier('deinverein-home-seo');
$cmsBlock->setContent($pagecontent);
$cmsBlock->setIsActive(true);
$cmsBlock->setStores(array(0));
$cmsBlock->save();

$installer->endSetup();
