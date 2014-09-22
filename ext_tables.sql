#
# Table structure for table 'pages'
#
CREATE TABLE pages (
    mkrobotsmetatag int(11) NOT NULL default '0',
    tx_mktools_fixedpostvartype  int(11) NOT NULL default '0'
);

#
# Table structure for table 'tx_mktools_fixedpostvartypes'
#
CREATE TABLE tx_mktools_fixedpostvartypes (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,

    title varchar(255) DEFAULT '' NOT NULL,
    identifier varchar(255) DEFAULT '' NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

CREATE TABLE tx_cal_event (
	tx_mktools_fal_images int(11) NOT NULL default '0'
);

CREATE TABLE tt_news (
	tx_mktools_fal_images int(11) NOT NULL default '0',
	tx_mktools_fal_media int(11) NOT NULL default '0'
);