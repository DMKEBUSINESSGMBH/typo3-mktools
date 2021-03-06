<?php

tx_rnbase_util_Extensions::addStaticFile('mktools', 'Configuration/TypoScript/action/', 'MK Tools - Actions');
tx_rnbase_util_Extensions::addStaticFile('mktools', 'Configuration/TypoScript/onsiteseo/', 'MK Tools - Onsite Seo');
tx_rnbase_util_Extensions::addStaticFile('mktools', 'Configuration/TypoScript/tsbasic/', 'MK Tools - Basis TypoScript');
tx_rnbase_util_Extensions::addStaticFile('mktools', 'Configuration/TypoScript/contentrenderer', 'MK Tools - Ajax Content Renderer');

// default TS für den content replacer
if (tx_mktools_util_miscTools::isContentReplacerActive()) {
    tx_rnbase_util_Extensions::addStaticFile('mktools', 'Configuration/TypoScript/contentreplacer', 'MK Tools - Content Replacer');
}

tx_rnbase_util_Extensions::addStaticFile('mktools', 'Configuration/TypoScript/contentmodal', 'MK Tools - Ajax Modal Renderer');

// Robots-Meta Tag
if (tx_mktools_util_miscTools::isSeoRobotsMetaTagActive()) {
    // default TS
    tx_rnbase_util_Extensions::addStaticFile('mktools', 'Configuration/TypoScript/seorobotsmetatag', 'MK Tools - SEO Robots Meta Tag');
}

if (tx_mktools_util_miscTools::shouldFalImagesBeAddedToCalEvent()) {
    // default TS
    tx_rnbase_util_Extensions::addStaticFile('mktools', 'Configuration/TypoScript/cal', 'MK Tools - FAL Images für Cal Event');
}

if (tx_mktools_util_miscTools::shouldFalImagesBeAddedToTtNews()) {
    // default TS
    tx_rnbase_util_Extensions::addStaticFile('mktools', 'Configuration/TypoScript/tt_news', 'MK Tools - FAL Images für TT_News');
}
