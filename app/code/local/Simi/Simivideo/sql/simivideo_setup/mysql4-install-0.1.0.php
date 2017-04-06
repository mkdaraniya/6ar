<?php
/**
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category  
 * @package   Simivideo
 * @copyright   Copyright (c) 2012 
 * @license   
 */

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * create simivideo table
 */
$installer->run("

DROP TABLE IF EXISTS {$this->getTable('simivideo_videos')};

CREATE TABLE {$this->getTable('simivideo_videos')} (
  `video_id` int(11) unsigned NOT NULL auto_increment,
  `video_url` varchar(255) NULL default '',
  `video_key` varchar(255) NULL default '',
  `video_title` varchar(255) NULL default '',
  `product_ids` text NULL default '',
  `status` int(11) NULL, 
  PRIMARY KEY (`video_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();

