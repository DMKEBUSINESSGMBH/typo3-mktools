#
# Table structure for table 'pages'
#
CREATE TABLE pages (
    mkrobotsmetatag int(11) NOT NULL default '0',
);

CREATE TABLE tx_cal_event (
    tx_mktools_fal_images int(11) NOT NULL default '0'
);

CREATE TABLE tt_news (
    tx_mktools_fal_images int(11) NOT NULL default '0',
    tx_mktools_fal_media int(11) NOT NULL default '0'
);

CREATE TABLE tt_content (
    tx_mktools_load_with_ajax SMALLINT UNSIGNED DEFAULT '0' NOT NULL,
);
