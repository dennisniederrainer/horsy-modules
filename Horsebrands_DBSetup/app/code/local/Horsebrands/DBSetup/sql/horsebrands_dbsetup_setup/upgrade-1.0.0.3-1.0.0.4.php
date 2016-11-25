<?php

$installer = $this;
$installer->startSetup();

// footer content
$block = Mage::getModel('cms/block')->load('footer_content','identifier');
if($block->getId()){$block->delete();}

$pagecontent = <<<EOF
<div class="row">
  <div class="columns medium-2 footer-col-main">
    <h1>Horsebrands</h1>
    <ul>
      <li><a href="#">Jobs</a></li>
      <li><a href="#">Magazin</a></li>
      <li><a href="#">Unser Versprechen</a></li>
      <li><a href="#">Presse</a></li>
      <li><a href="#">Impressum</a></li>
      <li><a href="#">Datenschutz</a></li>
      <li><a href="#">AGB</a></li>
    </ul>
  </div>
  <div class="columns medium-2 footer-col-contact">
    <h1>Kundenservice</h1>
    <ul>
      <li><a href="#">Kontakt</a></li>
      <li><a href="#">Versandarten</a></li>
      <li><a href="#">Zahlungsarten</a></li>
      <li><a href="#">Umtausch & R&uuml;cksendungen</a></li>
      <li><a href="#">Geschenkgutschein</a></li>
      <li><a href="#">FAQ</a></li>
    </ul>
  </div>
  <div class="columns medium-2 end footer-col-social">
    <h1>Verbinden</h1>
    <ul>
      <li><a href="#">Facebook</a></li>
      <li><a href="#">Instagram</a></li>
      <li><a href="#">Youtube</a></li>
    </ul>
  </div>
</div>
EOF;

$cmsBlock = Mage::getModel('cms/block');
$cmsBlock->setTitle('Horsebrands Footer');
$cmsBlock->setIdentifier('footer_content');
$cmsBlock->setContent($pagecontent);
$cmsBlock->setIsActive(true);
$cmsBlock->setStores(array(0));
$cmsBlock->save();

// Brand Banner
$block = Mage::getModel('cms/block')->load('homepage_brands_banner','identifier');
if($block->getId()){$block->delete();}

$pagecontent = <<<EOF
<div class="brands-wrapper">
  <div id="brands-container">
    <div class="brand">
      <img src="{{skin url='images/teaser/batman_monochrome.png'}}" alt="Brand Logo"  />
    </div>
    <div class="brand">
      <img src="{{skin url='images/teaser/batman_monochrome.png'}}" alt="Brand Logo"  />
    </div>
    <div class="brand">
      <img src="{{skin url='images/teaser/batman_monochrome.png'}}" alt="Brand Logo"  />
    </div>
    <div class="brand">
      <img src="{{skin url='images/teaser/batman_monochrome.png'}}" alt="Brand Logo"  />
    </div>
  </div>
</div>
EOF;

$cmsBlock = Mage::getModel('cms/block');
$cmsBlock->setTitle('Homepage Brands Banner');
$cmsBlock->setIdentifier('homepage_brands_banner');
$cmsBlock->setContent($pagecontent);
$cmsBlock->setIsActive(true);
$cmsBlock->setStores(array(0));
$cmsBlock->save();

// Homepage SEO
$block = Mage::getModel('cms/block')->load('homepage_seo_block','identifier');
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
$cmsBlock->setTitle('Homepage Seo Text');
$cmsBlock->setIdentifier('homepage_seo_block');
$cmsBlock->setContent($pagecontent);
$cmsBlock->setIsActive(true);
$cmsBlock->setStores(array(0));
$cmsBlock->save();

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
<section class="brands-section-wrapper">
  {{block type="cms/block" block_id="homepage_brands_banner"}}
</section>
<section class="seo-section-wrapper">
  {{block type="cms/block" block_id="homepage_seo_block"}}
</section>
EOF;

$cmsPage = Mage::getModel('cms/page');
$cmsPage->setTitle('Horsebrands Shop');
$cmsPage->setIdentifier('home');
$cmsPage->setContent($pagecontent);
$cmsPage->setIsActive(true);
$cmsPage->setStores(array(0));
$cmsPage->save();

// Adding Block Permission
$table = $installer->getTable('admin/permission_block');
$installer->getConnection()->insertIgnore($table, array(
    'block_name' => 'cms/block',
    'is_allowed' => 1
));

$installer->endSetup();
