<?php

/**
 * Installation: MySQL Table Creation
 *
 * Zenbership Membership Software
 * Copyright (C) 2013-2016 Castlamp, LLC
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author      Castlamp
 * @link        http://www.castlamp.com/
 * @link        http://www.zenbership.com/
 * @copyright   (c) 2013-2016 Castlamp
 * @license     http://www.gnu.org/licenses/gpl-3.0.en.html
 * @project     Zenbership Membership Software
 */
$create   = array();
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_abuse` (
  `id` int(9) NOT NULL auto_increment,
  `ip` varchar(30) NOT NULL,
  `time` varchar(15) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_access_granters` (
  `id` int(9) NOT NULL auto_increment,
  `item_id` varchar(35) NOT NULL COMMENT 'ppSD_products ID or ppSD_',
  `type` enum('content','newsletter') NOT NULL,
  `grants_to` varchar(25) NOT NULL COMMENT 'ppSD_content ID ppSD_event_timeline ID or ppSD_events ID',
  `timeframe` varchar(12) NOT NULL COMMENT 'For subscription product, always matches that timeframe.',
  PRIMARY KEY  (`id`),
  KEY `item_id` (`item_id`,`grants_to`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_accounts` (
  `id` varchar(10) NOT NULL,
  `name` varchar(65) NOT NULL,
  `contact_frequency` varchar(12) NOT NULL,
  `created` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `default` tinyint(1) NOT NULL,
  `master_user` varchar(20) NOT NULL COMMENT 'This is a ppSD_member ID.',
  `owner` mediumint(6) NOT NULL,
  `public` tinyint(1) NOT NULL,
  `last_updated` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `last_updated_by` mediumint(6) NOT NULL,
  `last_action` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `source` mediumint(5) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `start_page` VARCHAR( 255 ) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `master_user` (`master_user`),
  KEY `owner` (`owner`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_account_data` (
  `account_id` varchar(10) NOT NULL,
  `address_line_1` varchar(125) NOT NULL,
  `address_line_2` varchar(75) NOT NULL,
  `city` varchar(45) NOT NULL,
  `state` varchar(3) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `country` varchar(45) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `office_phone` varchar(25) NOT NULL,
  `alt_phone` varchar(25) NOT NULL,
  `fax` varchar(25) NOT NULL,
  `company_name` varchar(50) NOT NULL,
  `url` varchar(125) NOT NULL,
  `industry` varchar(45) NOT NULL,
  `account_type` varchar(25) NOT NULL,
  `email_optout` tinyint(1) NOT NULL,
  `facebook` varchar(125) NOT NULL,
  `twitter` varchar(125) NOT NULL,
  `linkedin` varchar(125) NOT NULL,
  PRIMARY KEY  (`account_id`),
  KEY `company_name` (`company_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_account_types` (
  `id` mediumint(4) NOT NULL auto_increment,
  `type` varchar(35) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_activity_methods` (
  `id` varchar(35) NOT NULL,
  `icon` varchar(35) NOT NULL,
  `link` varchar(35) NOT NULL,
  `link_type` enum('popup','popup_large','link','slider') NOT NULL,
  `custom` tinyint(1) NOT NULL,
  `text` VARCHAR( 75 ) NOT NULL,
  `in_feed` TINYINT( 1 ) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_bounced_emails` (
  `id` int(10) NOT NULL auto_increment,
  `email_id` varchar(35) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `user_id` varchar(25) NOT NULL,
  `user_type` enum('member','contact','rsvp') NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `email_id` (`email_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cache` (
  `act_id` varchar(45) NOT NULL,
  `data` longtext NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `expires` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  PRIMARY KEY  (`act_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_calendars` (
  `id` mediumint(4) NOT NULL auto_increment,
  `name` varchar(65) NOT NULL,
  `template` mediumint(5) NOT NULL,
  `members_only` tinyint(1) NOT NULL,
  `owner` mediumint(6) NOT NULL,
  `public` tinyint(1) NOT NULL,
  `created` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `style` tinyint(1) NOT NULL COMMENT '1 = Calendar, 2 = Long List, 3 = Cloud',
  PRIMARY KEY  (`id`,`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_campaigns` (
  `id` varchar(11) NOT NULL,
  `when_type` enum('after_join','exact_date') NOT NULL,
  `criteria_id` int(9) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `type` enum('email','sms','facebook','twitter') NOT NULL,
  `user_type` enum('member','contact','rsvp','account') NOT NULL,
  `name` varchar(85) NOT NULL,
  `kill_condition` enum('on_open','unsubscribe','purchase','register','form_submit','rsvp') NOT NULL,
  `owner` mediumint(5) NOT NULL,
  `public` tinyint(1) NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '1 = Active, 2 = Paused',
  `update_activity` tinyint(1) NOT NULL,
  `last_sent` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `optin_type` enum('criteria','single_optin','double_optin') NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `criteria_id` (`criteria_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_campaign_items` (
  `id` int(9) NOT NULL auto_increment,
  `title` varchar(85) NOT NULL,
  `campaign_id` varchar(11) NOT NULL,
  `msg_id` varchar(35) NOT NULL,
  `when_timeframe` varchar(12) NOT NULL,
  `when_date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `template_id` varchar(10) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `msg_id` (`msg_id`),
  KEY `template_id` (`template_id`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_campaign_logs` (
  `id` int(9) NOT NULL auto_increment,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `user_id` varchar(35) NOT NULL,
  `user_type` enum('member','contact','rsvp') NOT NULL,
  `trackback_id` varchar(27) NOT NULL,
  `campaign_id` varchar(11) NOT NULL,
  `saved_id` varchar(35) NOT NULL COMMENT 'Matches ppSD_saved_emails',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_campaign_subscriptions` (
  `id` int(11) NOT NULL auto_increment,
  `campaign_id` varchar(11) NOT NULL,
  `user_id` varchar(25) NOT NULL,
  `user_type` enum('member','contact') NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `subscribed_by` enum('user',  'employee',  'condition',  'criteria') NOT NULL,
  `subscribed_by_id` varchar(15) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `optin_id` varchar(20) NOT NULL,
  `optin_date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Campaigns can be criteria based or subscription-based.'";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_campaign_unsubscribe` (
  `id` int(9) NOT NULL auto_increment,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `user_id` varchar(20) NOT NULL,
  `user_type` enum('member','contact','rsvp') NOT NULL,
  `campaign_id` varchar(11) NOT NULL,
  `by` enum('user','staff','kill_condition','bounce') NOT NULL,
  `reason` varchar(255) NOT NULL,
  `staff` mediumint(5) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`,`campaign_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_captcha` (
  `id` int(10) NOT NULL auto_increment,
  `captcha` varchar(25) NOT NULL,
  `redirect` mediumtext NOT NULL,
  `type` enum('staff','user') NOT NULL,
  `username` varchar(150) NOT NULL COMMENT 'Can me ip, staff username, or member id',
  `form_session` varchar(40) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `form_session` (`form_session`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cart_billing` (
  `id` varchar(13) NOT NULL,
  `email` varchar(125) NOT NULL,
  `method` enum('Credit Card','Check','PayPal','Invoice','Other') NOT NULL,
  `card_type` enum('','Visa','Mastercard','Amex','Diners','Discover','JCB') NOT NULL,
  `gateway` varchar(35) NOT NULL COMMENT 'ppSD_payment_gateways',
  `gateway_id_1` varchar(45) NOT NULL,
  `gateway_id_2` varchar(45) NOT NULL,
  `company_name` varchar(125) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `cc_number` varchar(255) NOT NULL,
  `card_exp_yy` varchar(255) NOT NULL,
  `card_exp_mm` varchar(255) NOT NULL,
  `address_line_1` varchar(255) NOT NULL,
  `address_line_2` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) NOT NULL,
  `zip` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `member_id` varchar(20) NOT NULL,
  `salt` varchar(25) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `gateway_id` (`gateway`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cart_categories` (
  `id` mediumint(5) NOT NULL auto_increment,
  `name` varchar(40) NOT NULL,
  `description` text NOT NULL,
  `meta_title` varchar(69) NOT NULL,
  `meta_desc` varchar(156) NOT NULL,
  `meta_keywords` varchar(150) NOT NULL,
  `subcategory` mediumint(5) NOT NULL,
  `template_id` varchar(65) NOT NULL,
  `cols` tinyint(1) NOT NULL,
  `search_index` tinyint(1) NOT NULL,
  `public` tinyint(1) NOT NULL,
  `owner` mediumint(6) NOT NULL,
  `created` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `hide` TINYINT( 1 ) NOT NULL,
  `members_only` TINYINT( 0 ) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `subcategory` (`subcategory`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cart_coupon_codes` (
  `id` varchar(15) NOT NULL,
  `description` varchar(150) NOT NULL,
  `dollars_off` decimal(8,2) NOT NULL,
  `percent_off` smallint(3) NOT NULL,
  `products` text NOT NULL,
  `max_use_overall` int(7) NOT NULL,
  `date_start` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `date_end` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `current_customers_only` tinyint(1) NOT NULL,
  `max_use_per_user` int(6) NOT NULL,
  `cart_minimum` decimal(8,2) NOT NULL,
  `type` enum('dollars_off','percent_off','shipping') NOT NULL,
  `flat_shipping` decimal(6,2) NOT NULL,
  `created` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `owner` mediumint(6) NOT NULL,
  `public` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cart_coupon_codes_used` (
  `order_id` varchar(14) NOT NULL,
  `member_id` varchar(20) NOT NULL,
  `code` varchar(15) NOT NULL,
  `savings` decimal(8,2) NOT NULL,
  `tax` decimal(8,2) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  PRIMARY KEY  (`order_id`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cart_items` (
  `id` int(9) NOT NULL auto_increment,
  `cart_session` varchar(14) NOT NULL,
  `product_id` varchar(35) NOT NULL,
  `qty` int(7) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `option1` varchar(35) NOT NULL,
  `option2` varchar(35) NOT NULL,
  `option3` varchar(35) NOT NULL,
  `option4` varchar(35) NOT NULL,
  `option5` varchar(35) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `cart_session` (`cart_session`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cart_items_complete` (
  `id` int(9) NOT NULL auto_increment,
  `cart_session` varchar(14) NOT NULL,
  `product_id` varchar(35) NOT NULL,
  `qty` int(7) NOT NULL,
  `unit_price` decimal(8,2) NOT NULL,
  `subscription_id` varchar(22) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `tax` decimal(8,2) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL,
  `savings` decimal(8,2) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `option1` varchar(35) NOT NULL,
  `option2` varchar(35) NOT NULL,
  `option3` varchar(35) NOT NULL,
  `option4` varchar(35) NOT NULL,
  `option5` varchar(35) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `cart_session` (`cart_session`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cart_refunds` (
  `id` int(9) NOT NULL auto_increment,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `total` decimal(8,2) NOT NULL,
  `reason` text NOT NULL,
  `order_id` varchar(14) NOT NULL,
  `type` TINYINT( 1 ) NOT NULL COMMENT  '1 = Refund / 2 = Chargeback',
  `chargeback_fee` DECIMAL( 10, 2 ) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cart_sessions` (
  `id` varchar(14) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `last_activity` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `date_completed` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `status` tinyint(1) NOT NULL COMMENT '0 = unpaid, 1 = paid, 2 = pending payment, 3 = Partial refund, 4= full refung, 9 = rejected',
  `member_id` varchar(20) NOT NULL,
  `member_type` enum('member','contact','rsvp','other') NOT NULL,
  `code` varchar(15) NOT NULL,
  `ip` varchar(35) NOT NULL,
  `payment_gateway` varchar(35) NOT NULL COMMENT 'Matches the \"code\" in ppSD_payments_gateways',
  `gateway_order_id` varchar(50) NOT NULL,
  `gateway_resp_code` varchar(8) NOT NULL,
  `state` varchar(3) NOT NULL,
  `country` varchar(80) NOT NULL,
  `zip` VARCHAR(8) NOT NULL,
  `return_path` varchar(255) NOT NULL,
  `reg_session` varchar(40) NOT NULL,
  `gateway_msg` varchar(125) NOT NULL,
  `shipping_rule` mediumint(5) NOT NULL,
  `shipping_name` varchar(125) NOT NULL,
  `card_id` varchar(13) NOT NULL,
  `salt` varchar(25) NOT NULL COMMENT 'For PayPal IPN notification',
  `agreed_to_terms` tinyint(1) NOT NULL,
  `saw_upsell` TINYINT( 1 ) NOT NULL,
  `dependencies` TINYINT( 1 ) NOT NULL,
  `dependency_submitted` TEXT NOT NULL,
  `invoice_id` varchar(35) NOT NULL,
  `return_time_out` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `return_code` VARCHAR( 45 ) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `member_id` (`member_id`),
  KEY `invoice_id` (`invoice_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "
CREATE TABLE IF NOT EXISTS `ppSD_cart_session_totals` (
  `tid` int(9) NOT NULL AUTO_INCREMENT,
  `id` varchar(14) NOT NULL,
  `total` decimal(15,2) NOT NULL COMMENT 'This reflects actual income.',
  `gateway_fees` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `subtotal_nosave` decimal(15,2) NOT NULL COMMENT 'Subtotal of item prices after all savings and volume discounts.',
  `shipping` decimal(15,2) NOT NULL,
  `tax` decimal(15,2) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL,
  `savings` decimal(15,2) NOT NULL,
  `refunds` decimal(15,2) NOT NULL,
  `invoice_due` decimal(15,2) NOT NULL,
  `invoice_paid` decimal(15,2) NOT NULL,
  PRIMARY KEY (`tid`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cart_terms` (
  `id` smallint(4) NOT NULL auto_increment,
  `name` varchar(125) NOT NULL,
  `terms` mediumtext NOT NULL,
  `created` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `owner` mediumint(6) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cart_tracking` (
  `id` int(9) NOT NULL auto_increment,
  `cart_session` varchar(14) NOT NULL,
  `page` varchar(100) NOT NULL,
  `query` varchar(255) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_contacts` (
  `id` varchar(20) NOT NULL,
  `type` ENUM(  'Contact',  'Lead',  'Opportunity',  'Customer' ) NOT NULL,
  `created` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `owner` mediumint(5) NOT NULL COMMENT 'ppSD_staff ID, or 2 = system = unassigned',
  `email` varchar(125) NOT NULL,
  `bounce_notice` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `last_updated` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `last_updated_by` mediumint(6) NOT NULL,
  `last_action` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `next_action` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `source` mediumint(5) NOT NULL,
  `account` varchar(10) NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '1 = Active, 2 = Converted, 3 = Dead',
  `public` tinyint(1) NOT NULL COMMENT '1 = All can see, 0 = admin and owner, 2 = permission group only',
  `email_pref` enum('html','text') NOT NULL,
  `converted` tinyint(1) NOT NULL,
  `converted_id` int(9) NOT NULL COMMENT 'Matches ppSD_lead_conversion ID',
  `email_optout` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `sms_optout` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `expected_value` decimal(10,2) NOT NULL,
  `actual_dollars` decimal(10,2) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `owner` (`owner`),
  KEY `account` (`account`),
  KEY `source` (`source`),
  KEY `converted` (`converted_id`),
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_contact_data` (
  `contact_id` varchar(20) NOT NULL,
  `first_name` varchar(40) NOT NULL,
  `last_name` varchar(40) NOT NULL,
  `address_line_1` varchar(80) NOT NULL,
  `address_line_2` varchar(30) NOT NULL,
  `city` varchar(40) NOT NULL,
  `state` varchar(3) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `country` varchar(35) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `cell` varchar(15) NOT NULL,
  `cell_carrier` varchar(20) NOT NULL,
  `office_phone` varchar(20) NOT NULL,
  `alt_phone` varchar(20) NOT NULL,
  `fax` varchar(15) NOT NULL,
  `company_name` varchar(50) NOT NULL,
  `url` varchar(100) NOT NULL,
  `facebook` varchar(100) NOT NULL,
  `twitter` varchar(80) NOT NULL,
  `linkedin` varchar(100) NOT NULL,
  `dob` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `occupation` varchar(40) NOT NULL,
  `sms_optout` tinyint(1) NOT NULL,
  `email_optout` tinyint(1) NOT NULL,
  PRIMARY KEY  (`contact_id`),
  KEY `last_name` (`last_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_content` (
  `id` int(9) NOT NULL auto_increment,
  `permalink` varchar(150) NOT NULL,
  `permalink_clean` varchar(150) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('folder','page','redirect','section','file','newsletter','profile','user_group') NOT NULL,
  `path` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `additional_update_fieldsets` varchar(255) NOT NULL COMMENT 'CSV of fieldset IDs',
  `display_on_usercp` tinyint(1) NOT NULL,
  `owner` mediumint(5) NOT NULL,
  `section` varchar(35) NOT NULL,
  `secure` TINYINT( 1 ) NOT NULL,
  `section_homepage` INT( 9 ) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `permalink` (`permalink`),
  KEY `section_homepage` (`section_homepage`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_content_access` (
  `id` int(9) NOT NULL auto_increment,
  `added` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `expires` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `timeframe` varchar(12) NOT NULL,
  `member_id` varchar(20) NOT NULL,
  `content_id` int(9) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_criteria_cache` (
  `id` int(9) NOT NULL auto_increment,
  `criteria` mediumtext NOT NULL,
  `search_id` varchar(29) NOT NULL,
  `act_id` VARCHAR( 30 ) NOT NULL,
  `email_id` varchar(13) NOT NULL,
  `save` tinyint(1) NOT NULL,
  `name` varchar(85) NOT NULL,
  `type` enum('member','contact','rsvp','account','campaign','transaction','subscription','invoice') NOT NULL,
  `act` enum('email','search','print','campaign','export','other') NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `inclusive` enum('or','and') NOT NULL,
  `public` tinyint(1) NOT NULL,
  `owner` mediumint(5) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `search_id` (`search_id`,`email_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_custom_actions` (
  `id` int(9) NOT NULL auto_increment,
  `name` VARCHAR( 75 ) NOT NULL,
  `trigger` varchar(30) NOT NULL COMMENT 'Can be any task used throughout the program.',
  `trigger_type` TINYINT( 1 ) NOT NULL,
  `specific_trigger` varchar(35) NOT NULL,
  `when` tinyint(1) NOT NULL COMMENT '1 = before, 2 = after',
  `type` tinyint(1) NOT NULL COMMENT '1 = php include, 2 = email, 3 = mysql query, 4 = curl',
  `data` mediumtext NOT NULL COMMENT 'For include, path to file. For email, it is a data array that goes into email class. For mysql, list of queries.',
  `active` tinyint(1) NOT NULL,
  `owner` mediumint(6) NOT NULL,
  `created` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_custom_callers` (
  `id` mediumint(6) NOT NULL auto_increment,
  `caller` varchar(35) NOT NULL,
  `replacement` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_data_eav` (
  `id` int(9) NOT NULL auto_increment,
  `item_id` varchar(35) NOT NULL COMMENT 'Either ppSD_members ID or ppSD_contacts ID. If none, leave blank.',
  `key` varchar(45) NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `item_id` (`item_id`),
  KEY `key` (`key`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_departments` (
  `id` mediumint(5) NOT NULL auto_increment,
  `name` varchar(75) NOT NULL,
  `head_employee` mediumint(5) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `head_employee` (`head_employee`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_email_scheduled` (
  `id` int(9) NOT NULL auto_increment,
  `to` varchar(85) NOT NULL,
  `user_id` varchar(21) NOT NULL,
  `user_type` enum('member','contact','rsvp','account') NOT NULL,
  `email_id` varchar(35) NOT NULL COMMENT 'Matches ppSD_saved_email_content',
  `added` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `type` enum('email','sms') NOT NULL,
  `delete_email_after` tinyint(1) NOT NULL,
  `campaign` VARCHAR( 11 ) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='List of scheduled emails waiting to be sent. Deleted after.'";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_email_trackback` (
  `id` varchar(27) NOT NULL,
  `email_id` varchar(35) NOT NULL default '0',
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00' default '0000-00-00 00:00:00',
  `status` tinyint(1) NOT NULL default '0',
  `viewed` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00' default '0000-00-00 00:00:00',
  `last_viewed` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00' default '0000-00-00 00:00:00',
  `times_opened` smallint(4) NOT NULL default '0',
  `user_id` varchar(20) NOT NULL,
  `user_type` enum('member','contact','rsvp') NOT NULL,
  `campaign_id` varchar(11) NOT NULL,
  `campaign_saved_id` varchar(35) NOT NULL,
  `ip` VARCHAR( 30 ) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `email_id` (`email_id`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_error_codes` (
  `id` mediumint(4) NOT NULL auto_increment,
  `code` varchar(4) NOT NULL,
  `msg` text NOT NULL,
  `lang` VARCHAR( 3 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'en',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_events` (
  `id` varchar(9) NOT NULL,
  `name` varchar(100) NOT NULL,
  `tagline` varchar(150) NOT NULL,
  `calendar_id` mediumint(4) NOT NULL,
  `starts` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `ends` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `start_registrations` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `early_bird_end` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `close_registration` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `max_rsvps` mediumint(6) NOT NULL,
  `members_only_rsvp` tinyint(1) NOT NULL,
  `members_only_view` tinyint(1) NOT NULL,
  `allow_guests` tinyint(1) NOT NULL,
  `max_guests` smallint(2) NOT NULL,
  `description` mediumtext NOT NULL,
  `post_rsvp_message` mediumtext NOT NULL,
  `online` tinyint(1) NOT NULL COMMENT '1 = online event, 2 = offline event',
  `url` varchar(255) NOT NULL,
  `location_name` varchar(50) NOT NULL,
  `address_line_1` varchar(125) NOT NULL,
  `address_line_2` varchar(75) NOT NULL,
  `city` varchar(45) NOT NULL,
  `state` varchar(3) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `country` varchar(45) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `all_day` tinyint(1) NOT NULL,
  `custom_template` varchar(60) NOT NULL,
  `custom_email_template` varchar(35) NOT NULL,
  `custom_email_guest_template` varchar(35) NOT NULL,
  `owner` mediumint(6) NOT NULL,
  `public` tinyint(1) NOT NULL,
  `created` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `calendar_id` (`calendar_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_event_products` (
  `id` int(9) NOT NULL auto_increment,
  `product_id` varchar(35) NOT NULL,
  `event_id` varchar(9) NOT NULL,
  `type` tinyint(1) NOT NULL COMMENT '1 = rsvp, 2 = guest rsvp, 3 = addon, 4 = early bird, 6= early bird member, 5 = member pricing',
  PRIMARY KEY  (`id`),
  KEY `product_id` (`product_id`,`event_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_event_reminders` (
  `id` int(9) NOT NULL auto_increment,
  `event_id` varchar(9) character set latin1 NOT NULL,
  `send_date` date NOT NULL,
  `sent_on` date NOT NULL,
  `timeframe` varchar(12) character set latin1 NOT NULL,
  `when` enum('before','after') NOT NULL,
  `template_id` varchar(35) character set latin1 NOT NULL,
  `sms` tinyint(1) NOT NULL,
  `custom_message` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `event_id` (`event_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_event_reminder_logs` (
  `id` int(10) NOT NULL auto_increment,
  `event_id` varchar(9) NOT NULL,
  `rsvp_id` varchar(21) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `msg_id` int(9) NOT NULL,
  `status` TINYINT( 1 ) NOT NULL,
  `status_msg` VARCHAR( 100 ) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `event_id` (`event_id`,`rsvp_id`),
  KEY `msg_id` (`msg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_event_rsvps` (
  `id` varchar(21) NOT NULL,
  `event_id` varchar(9) NOT NULL,
  `user_id` varchar(20) NOT NULL COMMENT 'The ppSD_member ID.',
  `email` varchar(85) NOT NULL,
  `bounce_notice` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `type` tinyint(1) NOT NULL COMMENT '1 = Primary / 2 = Guest',
  `primary_rsvp` varchar(21) NOT NULL COMMENT 'For guests, this is the main RSVP ID.',
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `order_id` varchar(15) NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '1 = Paid / 2 = Pending Payment',
  `checked_in_by` mediumint(5) NOT NULL,
  `arrived` tinyint(1) NOT NULL,
  `arrived_date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `qrcode_key` varchar(65) NOT NULL,
  `ip` varchar(35) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `event_id` (`event_id`),
  KEY `primary_rsvp` (`primary_rsvp`),
  KEY `user_id` (`user_id`),
  KEY `qrcode_key` (`qrcode_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_event_rsvp_data` (
  `rsvp_id` varchar(21) NOT NULL,
  `first_name` varchar(40) NOT NULL,
  `last_name` varchar(40) NOT NULL,
  `address_line_1` varchar(80) NOT NULL,
  `address_line_2` varchar(30) NOT NULL,
  `city` varchar(40) NOT NULL,
  `state` varchar(3) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `country` varchar(35) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `cell` varchar(20) NOT NULL,
  `cell_carrier` varchar(20) NOT NULL,
  `sms_optout` tinyint(1) NOT NULL,
  PRIMARY KEY  (`rsvp_id`),
  KEY `last_name` (`last_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_event_tags` (
  `id` int(7) NOT NULL auto_increment,
  `tag` smallint(3) NOT NULL COMMENT 'Matches ID in ppSD_event_types',
  `event_id` varchar(9) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `tag` (`tag`,`event_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_event_timeline` (
  `id` int(9) NOT NULL auto_increment,
  `event_id` varchar(9) NOT NULL,
  `starts` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `ends` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `location_name` varchar(50) NOT NULL,
  `address_line_1` varchar(125) NOT NULL,
  `address_line_2` varchar(75) NOT NULL,
  `city` varchar(45) NOT NULL,
  `state` varchar(3) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `country` varchar(45) NOT NULL,
  `phone` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `event_id` (`event_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_event_types` (
  `id` smallint(3) NOT NULL auto_increment,
  `name` varchar(35) NOT NULL,
  `color` varchar(6) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_favorites` (
  `id` int(9) NOT NULL auto_increment,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `user_id` varchar(25) NOT NULL,
  `user_type` enum('member','contact','account') NOT NULL,
  `owner` mediumint(5) NOT NULL,
  `ref_name` varchar(75) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_fields` (
  `id` varchar(25) NOT NULL,
  `display_name` varchar(85) NOT NULL,
  `type` enum('text','textarea','radio','select','checkbox','attachment','section','multiselect','multicheckbox','linkert','date') NOT NULL,
  `special_type` enum('','formatting','date','datetime','url','password','email','random_id','terms','phone','state','country','cell_carriers','cc','cc_expiration') NOT NULL,
  `logic` tinyint(1) NOT NULL,
  `logic_dependent` tinyint(1) NOT NULL COMMENT '1 = Top level logic / 2 = Second level logic',
  `desc` mediumtext NOT NULL,
  `label_position` enum('top','left') NOT NULL,
  `options` mediumtext NOT NULL,
  `styling` mediumtext NOT NULL,
  `default_value` varchar(85) NOT NULL,
  `encrypted` tinyint(1) NOT NULL,
  `sensitive` tinyint(1) NOT NULL COMMENT 'Hides data on previews.',
  `maxlength` smallint(3) NOT NULL,
  `settings` mediumtext NOT NULL,
  `permissions_group` tinyint(3) NOT NULL,
  `primary` tinyint(1) NOT NULL,
  `static` tinyint(1) NOT NULL,
  `data_type` tinyint(1) NOT NULL COMMENT '1 = all, 2 = letters, 3 = numbers, 4 = letters/numbers',
  `min_len` mediumint(5) NOT NULL,
  `scope_member` tinyint(1) NOT NULL,
  `scope_contact` tinyint(1) NOT NULL,
  `scope_rsvp` tinyint(1) NOT NULL,
  `scope_account` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_fieldsets` (
  `id` mediumint(5) NOT NULL auto_increment,
  `name` varchar(85) NOT NULL,
  `desc` mediumtext NOT NULL,
  `order` smallint(3) NOT NULL,
  `columns` tinyint(1) NOT NULL,
  `logic_dependent` tinyint(1) NOT NULL,
  `static` tinyint(1) NOT NULL COMMENT '1 = Default, no delete, 2 = Custom, 0 = Custom but not visible (events, etc.)',
  `owner` mediumint(5) NOT NULL,
  `billing` tinyint(1) NOT NULL COMMENT 'Used to prevent displaying billing data in dropdowns to avoid confusion.',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_fieldsets_fields` (
  `id` int(7) NOT NULL auto_increment,
  `fieldset` mediumint(5) NOT NULL,
  `field` varchar(25) NOT NULL,
  `order` mediumint(4) NOT NULL,
  `req` tinyint(1) NOT NULL,
  `column` tinyint(1) NOT NULL,
  `tabindex` mediumint(4) NOT NULL,
  `autoadd_product` varchar(11) NOT NULL COMMENT 'Mainly used for event registration',
  `autoadd_value` varchar(100) NOT NULL COMMENT 'If \"autoadd_value\" is selected for the input, \"autoadd_product\" will be added to cart.',
  PRIMARY KEY  (`id`),
  KEY `field` (`field`),
  KEY `fieldset` (`fieldset`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_fieldsets_locations` (
  `id` smallint(3) NOT NULL auto_increment,
  `location` varchar(35) NOT NULL COMMENT 'account-ID for account-specific sets',
  `act_id` varchar(35) NOT NULL,
  `order` smallint(3) NOT NULL,
  `col` smallint(2) NOT NULL,
  `fieldset_id` mediumint(5) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `fieldset_id` (`fieldset_id`),
  KEY `act_id` (`act_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_field_logic` (
  `id` int(6) NOT NULL auto_increment,
  `field_id` varchar(8) NOT NULL,
  `field_value` varchar(85) NOT NULL,
  `display_type` enum('field','fieldset','msg_popup','msg_inline','email','text') NOT NULL,
  `display_id` varchar(8) NOT NULL,
  `display_msg` mediumtext NOT NULL,
  `template_id` mediumint(5) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `field_id` (`field_id`,`display_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_forms` (
  `id` varchar(25),
  `type` enum('admin_cp','payment_form','register-free','contact','update_account','event','register-paid','campaign','dependency','update'),
  `criteria` mediumtext NULL DEFAULT '',
  `act_id` varchar(20) NULL DEFAULT '',
  `name` varchar(50),
  `description` text,
  `code_required` tinyint(1),
  `date` datetime DEFAULT '0000-00-00 00:00:00',
  `owner` mediumint(6),
  `public` tinyint(1),
  `reg_status` varchar(1) COMMENT 'Registrations only: A, P (email approve), Y (admin approve)',
  `pages` tinyint(1),
  `member_type` MEDIUMINT( 5 ),
  `preview` tinyint(1),
  `step1_name` varchar(65),
  `step2_name` varchar(65),
  `step3_name` varchar(65),
  `step4_name` varchar(65),
  `step5_name` varchar(65),
  `public_list` tinyint(1),
  `static` tinyint(1),
  `disabled` tinyint(1),
  `account_create` tinyint(1),
  `terms_id` smallint(4),
  `captcha` tinyint(1),
  `redirect` varchar(255),
  `account` varchar(8),
  `source` mediumint(5),
  `email_thankyou` tinyint(1),
  `template` varchar(35),
  `email_forward` text,
  PRIMARY KEY  (`id`),
  KEY `act_id` (`act_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_form_closed_sessions` (
  `code` varchar(29) NOT NULL,
  `used` tinyint(1) NOT NULL,
  `form_id` varchar(25) NOT NULL,
  `date_issued` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `date_used` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `form_session` varchar(40) NOT NULL,
  `sent_to` varchar(100) NOT NULL,
  PRIMARY KEY  (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_form_conditions` (
  `id` int(11) NOT NULL auto_increment,
  `act_id` varchar(35) NOT NULL COMMENT 'Form ID',
  `type` enum('content','product','campaign','kill','coupon','expected_value','assign_contact') NOT NULL,
  `field_name` varchar(25) NOT NULL,
  `field_eq` varchar(4) NOT NULL,
  `field_value` varchar(75) NOT NULL,
  `condition_id` varchar(35) NOT NULL COMMENT 'Product, campaign, or content id',
  `act_qty` varchar(12) NOT NULL COMMENT 'Could be a timeframe or a qty.',
  PRIMARY KEY  (`id`),
  KEY `form_id` (`act_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_form_submit` (
  `id` VARCHAR(30) NOT NULL,
  `form_id` varchar(25) NOT NULL,
  `form_name` VARCHAR( 50 ) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `user_id` varchar(25) NOT NULL,
  `user_type` ENUM(  'member',  'contact',  'rsvp',  'account' ) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_form_products` (
  `id` int(9) NOT NULL auto_increment,
  `form_id` varchar(25) NOT NULL,
  `product_id` varchar(35) NOT NULL,
  `qty_control` tinyint(1) NOT NULL COMMENT '1 = Add 1, 2 = user select',
  `type` tinyint(1) NOT NULL COMMENT '1 = Required / 2 = Optional',
  `order` SMALLINT( 3 ) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `form_id` (`form_id`,`product_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_form_requests` (
  `id` varchar(31) NOT NULL,
  `form_id` varchar(25) NOT NULL,
  `member_id` varchar(20) NOT NULL,
  `member_type` enum('member','contact','rsvp') NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `expires` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `completed` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `form_id` (`form_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_form_sessions` (
  `id` varchar(40) NOT NULL,
  `member_id` varchar(20) NOT NULL,
  `closed_code` varchar(33) NOT NULL,
  `code_approved` tinyint(1) NOT NULL,
  `req_login` tinyint(1) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `last_activity` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `step` smallint(2) NOT NULL,
  `form_id` varchar(25) NOT NULL,
  `act_id` varchar(20) NOT NULL COMMENT 'Reg form ID or Event ID being acted on',
  `type` enum('register','lead','update','dependency','event','forced_update','campaign','contact') NOT NULL,
  `s1` mediumtext NOT NULL,
  `s2` mediumtext NOT NULL,
  `s3` mediumtext NOT NULL,
  `s4` mediumtext NOT NULL,
  `s5` mediumtext NOT NULL,
  `s6` int(11) NOT NULL,
  `s7` int(11) NOT NULL,
  `s8` int(11) NOT NULL,
  `ip` varchar(35) NOT NULL,
  `salt` varchar(6) NOT NULL,
  `cart_id` varchar(14) NOT NULL,
  `products` text NOT NULL,
  `terms` tinyint(1) NOT NULL,
  `final_member_id` varchar(25) NOT NULL,
  `redirect` VARCHAR( 255 ) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `form_id` (`form_id`),
  KEY `act_id` (`act_id`),
  KEY `member_id` (`member_id`),
  KEY `cart_id` (`cart_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_history` (
  `id` int(9) NOT NULL auto_increment,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `method` varchar(25) NOT NULL,
  `owner` mediumint(5) NOT NULL COMMENT 'c7_staff ID',
  `notes` mediumtext NOT NULL,
  `user_id` varchar(35) NOT NULL COMMENT 'ppSD_members ID or ppSD_contacts ID',
  `act_id` varchar(35) NOT NULL COMMENT 'If possible, the ID of the action, such as the email or note.',
  `type` tinyint(1) NOT NULL COMMENT '1 = member, 2 = contact, 3 = rsvp, 4 = other',
  PRIMARY KEY  (`id`),
  KEY `owner` (`owner`),
  KEY `user_id` (`user_id`),
  KEY `act_id` (`act_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='This is basically an activity feed for users.'";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_homepage_widgets` (
  `id` varchar(25) NOT NULL,
  `options` text NOT NULL,
  `title` varchar(50) NOT NULL,
  `perms` enum('admin','all') NOT NULL,
  `static` tinyint(1) NOT NULL,
  `employee` mediumint(5) NOT NULL,
  `add_fields` text NOT NULL,
  `hide` tinyint(1) NOT NULL,
  `custom` TINYINT( 1 ) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_invoices` (
  `id` varchar(35) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `last_reminder` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `total_reminders` smallint(3) NOT NULL,
  `date_due` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `order_id` varchar(14) NOT NULL COMMENT 'Mainly used to associate totals and shipping data',
  `member_id` varchar(20) NOT NULL,
  `member_type` enum('member','contact') NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '0 = Unpaid, 1 = Paid, 2 = Partial Payment, 3 = Overdue, 4 = Dead',
  `salt` varchar(4) NOT NULL,
  `hash` varchar(60) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL,
  `shipping_rule` mediumint(5) NOT NULL,
  `shipping_name` varchar(125) NOT NULL,
  `ip` varchar(35) NOT NULL,
  `owner` mediumint(5) NOT NULL,
  `hourly` decimal(8,2) NOT NULL,
  `rsvp_id` varchar(21) NOT NULL,
  `auto_inform` TINYINT( 1 ) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `rsvp_id` (`rsvp_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_invoice_components` (
  `id` int(9) NOT NULL auto_increment,
  `invoice_id` varchar(35) NOT NULL,
  `type` enum('product','time','credit') NOT NULL,
  `minutes` int(8) NOT NULL,
  `hourly` decimal(8,2) NOT NULL,
  `product_id` varchar(35) NOT NULL,
  `qty` int(7) NOT NULL,
  `unit_price` decimal(8,2) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `option1` varchar(35) NOT NULL,
  `option2` varchar(35) NOT NULL,
  `option3` varchar(35) NOT NULL,
  `option4` varchar(35) NOT NULL,
  `option5` varchar(35) NOT NULL,
  `name` varchar(85) NOT NULL,
  `description` text NOT NULL,
  `owner` mediumint(5) NOT NULL,
  `tax` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `invoice_id` (`invoice_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_invoice_data` (
  `id` varchar(35) NOT NULL,
  `company_name` varchar(80) NOT NULL,
  `contact_name` varchar(80) NOT NULL,
  `address_line_1` varchar(80) NOT NULL,
  `address_line_2` varchar(30) NOT NULL,
  `city` varchar(40) NOT NULL,
  `state` varchar(3) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `country` varchar(35) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `fax` varchar(20) NOT NULL,
  `email` varchar(80) NOT NULL,
  `website` varchar(125) NOT NULL,
  `memo` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_invoice_payments` (
  `id` int(11) NOT NULL auto_increment,
  `order_id` varchar(14) NOT NULL,
  `invoice_id` varchar(35) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `paid` decimal(8,2) NOT NULL,
  `new_balance` decimal(8,2) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `order_id` (`order_id`,`invoice_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_invoice_totals` (
  `id` varchar(35) NOT NULL,
  `paid` decimal(8,2) NOT NULL,
  `due` decimal(8,2) NOT NULL,
  `subtotal` decimal(8,2) NOT NULL,
  `shipping` decimal(8,2) NOT NULL,
  `tax` decimal(8,2) NOT NULL,
  `tax_rate` decimal(5,2) NOT NULL,
  `credits` decimal(8,2) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_lead_conversion` (
  `id` int(9) NOT NULL auto_increment,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `began` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `contact_id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `owner` mediumint(6) NOT NULL,
  `estimated_value` decimal(9,2) NOT NULL,
  `actual_value` decimal(9,2) NOT NULL,
  `percent_change` decimal(5,2) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `username` (`user_id`),
  KEY `owner` (`owner`),
  KEY `contact_id` (`contact_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_link_tracking` (
  `id` varchar(32) NOT NULL,
  `email_id` varchar(35) NOT NULL,
  `clicked` mediumint(4) NOT NULL,
  `first_clicked` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `last_clicked` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `link` varchar(255) NOT NULL,
  `campaign_id` varchar(11) NOT NULL,
  `campaign_email_id` varchar(35) NOT NULL,
  `user_id` VARCHAR( 20 ) NOT NULL,
  `user_type` ENUM(  'contact',  'member',  'rsvp' ) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_logins` (
  `id` int(9) NOT NULL auto_increment,
  `session_id` varchar(35) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `member_id` varchar(20) NOT NULL,
  `ip` varchar(35) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `host` VARCHAR(80) NOT NULL,
  `browser` VARCHAR(150) NOT NULL,
  `browser_short` VARCHAR( 25 ) NOT NULL,
  `attempt_no` smallint(3) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_login_announcements` (
  `id` int(9) NOT NULL auto_increment,
  `starts` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `ends` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `title` varchar(100) NOT NULL,
  `content` mediumtext NOT NULL,
  `show_criteria` int(9) NOT NULL COMMENT 'Matches ppSD_criteria_cache',
  `active` tinyint(1) NOT NULL,
  `created` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00' ,
  `owner` MEDIUMINT( 5 ) NOT NULL ,
  `public` TINYINT( 1 ) NOT NULL ,
  PRIMARY KEY  (`id`),
  KEY `starts` (`starts`),
  KEY `ends` (`ends`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_login_announcement_logs` (
  `id` int(9) NOT NULL auto_increment,
  `announcement_id` int(9) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `member_id` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `announcement_id` (`announcement_id`,`member_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_login_temp` (
  `id` int(9) NOT NULL auto_increment,
  `ip` varchar(35) NOT NULL,
  `attempt` smallint(2) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `ip` (`ip`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_members` (
  `id` varchar(20) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(6) NOT NULL,
  `email` varchar(110) NOT NULL,
  `bounce_notice` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `joined` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00' default '0000-00-00 00:00:00',
  `last_renewal` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `last_action` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `last_login` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00' default '0000-00-00 00:00:00',
  `last_updated` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00' default '0000-00-00 00:00:00',
  `last_date_check` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00' COMMENT 'Used for inactivity checks',
  `next_action` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `last_updated_by` varchar(20) NOT NULL,
  `status` char(1) NOT NULL,
  `status_msg` varchar(255) NOT NULL COMMENT 'If someone is paused, rejected, etc.. this holds the reason.',
  `conversion_id` int(8) NOT NULL default '0',
  `source` mediumint(5) NOT NULL default '0' COMMENT 'Corresponds to ppSD_sources',
  `concurrent_login_notices` tinyint(3) NOT NULL default '0',
  `concurrent_login_date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00' default '0000-00-00 00:00:00',
  `public` tinyint(1) NOT NULL,
  `owner` mediumint(6) NOT NULL,
  `email_pref` enum('html','text') NOT NULL,
  `locked` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `locked_ip` varchar(25) NOT NULL,
  `login_attempts` tinyint(1) NOT NULL,
  `account` varchar(10) NOT NULL,
  `email_optout` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `sms_optout` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `activation_code` varchar(40) NOT NULL,
  `activated` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `start_page` varchar(255) NOT NULL,
  `converted` tinyint(1) NOT NULL,
  `converted_id` int(9) NOT NULL,
  `listing_display` tinyint(1) NOT NULL,
  `member_type` MEDIUMINT( 4 ) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `username` (`username`),
  KEY `member_type` (`member_type`),
  KEY `account` (`account`),
  KEY `source` (`source`),
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_member_types` (
  `id` mediumint(4) NOT NULL auto_increment,
  `name` varchar(125) NOT NULL,
  `order` MEDIUMINT( 5 ) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_member_types_content` (
  `id` int(7) NOT NULL auto_increment,
  `member_type` mediumint(4) NOT NULL,
  `act_id` varchar(30) NOT NULL,
  `act_type` enum('content','other') NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `member_type` (`member_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_member_activations` (
  `id` int(9) NOT NULL auto_increment,
  `member_id` varchar(20) NOT NULL,
  `date` int(11) NOT NULL,
  `owner` mediumint(5) NOT NULL,
  `status` varchar(1) NOT NULL,
  `reason` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_member_data` (
  `member_id` varchar(20) NOT NULL,
  `first_name` varchar(40) default NULL,
  `last_name` varchar(40) default NULL,
  `address_line_1` varchar(80) default NULL,
  `address_line_2` varchar(30) default NULL,
  `city` varchar(40) default NULL,
  `state` varchar(3) default NULL,
  `zip` varchar(10) default NULL,
  `country` varchar(35) default NULL,
  `phone` varchar(20) default NULL,
  `fax` varchar(20) default NULL,
  `sms_optout` tinyint(1) NOT NULL,
  `email_optout` tinyint(1) NOT NULL,
  `dob` date NOT NULL,
  `industry` varchar(30) NOT NULL,
  `facebook` varchar(100) NOT NULL,
  `twitter` varchar(80) NOT NULL,
  `linkedin` varchar(100) NOT NULL,
  `cell` varchar(15) NOT NULL,
  `cell_carrier` varchar(20) NOT NULL,
  `alt_phone` varchar(20) NOT NULL,
  `office_phone` varchar(20) NOT NULL,
  PRIMARY KEY  (`member_id`),
  KEY `last_name` (`last_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
/*
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_modules` (
  `id` varchar(20) NOT NULL,
  `status` tinyint(1) NOT NULL default '0',
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00' default '0000-00-00 00:00:00',
  `path` varchar(255) NOT NULL,
  `db` varchar(100) NOT NULL,
  `db_host` varchar(100) NOT NULL,
  `db_user` varchar(255) NOT NULL,
  `db_pass` varchar(255) NOT NULL,
  `login` tinyint(1) NOT NULL default '0',
  `logout` tinyint(1) NOT NULL default '0',
  `table_prefix` varchar(20) NOT NULL,
  `options_array` mediumtext NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
*/
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_newsletters_subscribers` (
  `id` int(9) NOT NULL auto_increment,
  `user_id` varchar(20) NOT NULL,
  `user_type` enum('member','contact') NOT NULL,
  `newsletter_id` varchar(10) NOT NULL,
  `added` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `expires` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `unsubscribed` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `status` tinyint(1) NOT NULL default '1' COMMENT '1 = subscribed, 0 = unsubscribed',
  `activation_code` varchar(35) NOT NULL,
  `double_optin` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`,`newsletter_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_notes` (
  `id` varchar(35) NOT NULL,
  `user_id` varchar(35) NOT NULL,
  `item_scope` varchar(25) NOT NULL,
  `name` varchar(85) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `note` mediumtext NOT NULL,
  `added_by` mediumint(6) NOT NULL,
  `label` tinyint(2) NOT NULL COMMENT 'Matches ppSD_note_labels',
  `public` tinyint(1) NOT NULL COMMENT '2 = broadcast , 1 = all can see, 0 = creator and admin only',
  `deadline` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `value` decimal(10,2) NOT NULL,
  `for` mediumint(5) NOT NULL,
  `remove_from_cp` tinyint(1) NOT NULL,
  `complete` TINYINT( 1 ) NOT NULL ,
  `completed_by` MEDIUMINT( 5 ) NOT NULL,
  `completed_on` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `priority` TINYINT( 1 ) NOT NULL,
  `encrypt` TINYINT( 1 ) NOT NULL,
  `external_id` VARCHAR( 30 ) NOT NULL COMMENT  'Used for external calendar links.',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `added_by` (`added_by`),
  KEY `name` (`name`),
  KEY `for` (`for`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_note_labels` (
  `id` smallint(3) NOT NULL auto_increment,
  `label` varchar(35) NOT NULL,
  `color` varchar(6) NOT NULL,
  `fontcolor` varchar(6) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_options` (
  `id` varchar(50) NOT NULL,
  `display` varchar(30) NOT NULL,
  `value` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `type` enum('text','select','radio','checkbox','timeframe','special','file_size','textarea') NOT NULL,
  `width` mediumint(3) NOT NULL,
  `options` varchar(100) NOT NULL,
  `section` varchar(20) NOT NULL,
  `maxlength` mediumint(5) NOT NULL,
  `class` varchar(25) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_pages` (
  `id` varchar(14) NOT NULL,
  `title` varchar(35) NOT NULL,
  `meta_title` varchar(69) NOT NULL,
  `meta_desc` varchar(156) NOT NULL,
  `meta_keywords` varchar(85) NOT NULL,
  `members_only` tinyint(1) NOT NULL,
  `section` varchar(35) NOT NULL,
  `template` varchar(65) NOT NULL,
  `content` longtext NOT NULL,
  `live` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `section` (`section`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_payment_gateways` (
  `id` smallint(3) NOT NULL auto_increment,
  `fee_flat` decimal(6,2) NOT NULL,
  `fee_percent` decimal(5,2) NOT NULL,
  `test_mode` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(45) NOT NULL,
  `online` varchar(255) NOT NULL,
  `api` tinyint(1) NOT NULL,
  `local_card_storage` tinyint(1) NOT NULL,
  `credential1` varchar(255) NOT NULL,
  `credential2` varchar(255) NOT NULL,
  `credential3` varchar(255) NOT NULL,
  `credential4` varchar(255) NOT NULL,
  `primary` tinyint(1) NOT NULL,
  `method_cc_visa` tinyint(1) NOT NULL,
  `method_cc_amex` int(11) NOT NULL,
  `method_cc_mc` int(11) NOT NULL,
  `method_cc_discover` int(11) NOT NULL,
  `method_check` tinyint(1) NOT NULL,
  `method_refund` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_permission_groups` (
  `id` smallint(3) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `admin` tinyint(1) NOT NULL,
  `owner` mediumint(6) NOT NULL COMMENT 'c7_staff ID',
  `start_page` varchar(125) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_permission_group_settings` (
  `id` int(9) NOT NULL auto_increment,
  `group_id` tinyint(3) NOT NULL,
  `scope` varchar(25) NOT NULL,
  `action` varchar(25) NOT NULL,
  `allowed` enum('all','owned','none') NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `group` (`group_id`),
  KEY `permission` (`scope`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";

$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_products` (
  `id` varchar(35) NOT NULL,
  `associated_id` varchar(20) NOT NULL COMMENT 'Event, etc.',
  `name` varchar(85) NOT NULL,
  `tagline` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `type` tinyint(1) NOT NULL COMMENT '1 = one time, 2 = subscription, 3 = trial',
  `physical` tinyint(1) NOT NULL COMMENT '1 = physical but no shipping, 2 = physical and needs shipping',
  `tax_exempt` tinyint(1) NOT NULL,
  `cost_in_credits` int(7) NOT NULL,
  `grant_credits` int(7) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `upfront_cost` decimal(15,2) NOT NULL,
  `trial_price` decimal(15,2) NOT NULL,
  `trial_period` varchar(12) NOT NULL,
  `trial_repeat` smallint(3) NOT NULL,
  `renew_max` mediumint(5) NOT NULL,
  `renew_timeframe` varchar(12) NOT NULL,
  `threshold_date` VARCHAR(5) NOT NULL COMMENT 'For subscriptions, if started after a certain date, it will automatically set the next renewal to threshold_date_set',
  `threshold_date_set` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `hide` tinyint(1) NOT NULL,
  `hide_in_admin` tinyint(1) NOT NULL,
  `weight` decimal(8,2) NOT NULL,
  `member_type` MEDIUMINT( 5 ) NOT NULL ,
  `cart_ordering` mediumint(6) NOT NULL,
  `category` mediumint(6) NOT NULL,
  `attribute_to` varchar(11) NOT NULL,
  `max_per_cart` int(7) NOT NULL COMMENT 'Allow quantities to be added or only 1',
  `min_per_cart` INT( 8 ) NOT NULL,
  `limit_attr` tinyint(1) NOT NULL COMMENT '1 = Max 1 selected, 2 = Input number to buy',
  `terms` mediumint(4) NOT NULL COMMENT 'Matches ppSD_terms ID',
  `popularity` smallint(6) NOT NULL,
  `owner` mediumint(6) NOT NULL,
  `public` tinyint(1) NOT NULL,
  `created` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `invoice_id` varchar(35) NOT NULL,
  `featured` tinyint(1) NOT NULL,
  `base_popularity` mediumint(5) NOT NULL,
  `members_only` TINYINT( 1 ) NOT NULL,
  `auto_register` TINYINT(1),
  `sync_id` VARCHAR( 25 ) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `category` (`category`,`attribute_to`),
  KEY `type` (`type`),
  KEY `associated_id` (`associated_id`),
  KEY `invoice_id` (`invoice_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_products_linked` (
  `id` mediumint(5) NOT NULL AUTO_INCREMENT,
  `product_id` varchar(35) NOT NULL,
  `package_id` mediumint(9) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `package_id` (`package_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_packages` (
  `id` mediumint(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `prorate_upgrades` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_products_options` (
  `id` int(9) NOT NULL auto_increment,
  `product_id` varchar(35) NOT NULL,
  `option_no` varchar(35) NOT NULL,
  `option_value` varchar(35) NOT NULL,
  `options` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_product_dependencies` (
  `id` int(9) NOT NULL auto_increment,
  `type` enum('form') NOT NULL,
  `act_id` varchar(25) NOT NULL,
  `options` text NOT NULL,
  `product_id` varchar(35) NOT NULL,
  PRIMARY KEY  (`id`), KEY `act_id` (`act_id`,`product_id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_products_options_qty` (
  `id` int(9) NOT NULL auto_increment,
  `product_id` varchar(35) NOT NULL,
  `option1` varchar(35) NOT NULL COMMENT 'Represents the csv value option in ppSD_product_options',
  `option2` varchar(35) NOT NULL,
  `option3` varchar(35) NOT NULL,
  `option4` varchar(35) NOT NULL,
  `option5` varchar(35) NOT NULL,
  `qty` int(7) NOT NULL,
  `price_adjust` decimal(8,2) NOT NULL,
  `weight_adjust` decimal(6,2) NOT NULL,
  `sync_id` VARCHAR( 35 ) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `product_id` (`product_id`),
  KEY `option1` (`option1`,`option2`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Example: for size options, color options, etc.'";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_products_tiers` (
  `id` int(8) NOT NULL auto_increment,
  `product_id` varchar(35) NOT NULL,
  `low` mediumint(5) NOT NULL,
  `high` mediumint(5) NOT NULL,
  `discount` decimal(4,2) NOT NULL COMMENT 'Percentage',
  PRIMARY KEY  (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_product_upsell` (
  `id` int(9) NOT NULL auto_increment,
  `product` varchar(35) NOT NULL,
  `upsell` varchar(35) NOT NULL,
  `type` enum('popup','checkout') NOT NULL,
  `order` MEDIUMINT( 5 ) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `product` (`product`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
/*
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_product_views` (
  `id` int(9) NOT NULL auto_increment,
  `product_id` varchar(35) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `ip` varchar(35) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
*/
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_qr_devices` (
  `id` mediumint(6) NOT NULL auto_increment,
  `employee_id` mediumint(6) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `ip` varchar(35) NOT NULL,
  `host` varchar(75) NOT NULL,
  `browser` varchar(150) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_reset_passwords` (
  `id` varchar(40) NOT NULL,
  `member_id` varchar(20) NOT NULL,
  `email` varchar(150) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_saved_emails` (
  `id` varchar(35) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00' default '0000-00-00 00:00:00',
  `content` mediumtext NOT NULL,
  `subject` varchar(100) NOT NULL,
  `to` varchar(125) NOT NULL,
  `from` varchar(125) NOT NULL,
  `cc` varchar(125) NOT NULL,
  `bcc` varchar(125) NOT NULL,
  `sent_by` varchar(50) NOT NULL,
  `format` varchar(12) NOT NULL,
  `template` varchar(10) NOT NULL,
  `type` char(1) NOT NULL,
  `newsletter` mediumint(6) NOT NULL default '0',
  `statuses` varchar(100) NOT NULL,
  `areas` varchar(255) NOT NULL,
  `inclusive` char(1) NOT NULL,
  `criteria` char(3) NOT NULL,
  `mass_email_id` varchar(26) NOT NULL default '0',
  `user_id` varchar(35) NOT NULL,
  `user_type` enum('member','contact','rsvp') NOT NULL,
  `fail` TINYINT( 1 ) NOT NULL,
  `fail_reason` VARCHAR( 100 ) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_saved_sms` (
  `id` int(9) NOT NULL auto_increment,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `msg` varchar(160) NOT NULL,
  `user_id` varchar(25) NOT NULL,
  `user_type` enum('member','contact') NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_schedule_social` (
  `id` int(9) NOT NULL auto_increment,
  `type` enum('date','after_join') NOT NULL,
  `post` mediumtext NOT NULL,
  `where` enum('company_feed','post_to_user') NOT NULL,
  `site` enum('facebook','twitter') NOT NULL,
  `post_date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `post_timeframe` varchar(12) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_saved_email_content` (
  `id` varchar(35) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `message` mediumtext NOT NULL,
  `subject` varchar(150) NOT NULL,
  `from` varchar(85) NOT NULL,
  `to` varchar(85) NOT NULL,
  `reply_to` varchar(85) NOT NULL,
  `cc` varchar(255) NOT NULL,
  `bcc` varchar(255) NOT NULL,
  `trackback` tinyint(1) NOT NULL,
  `track_links` tinyint(1) NOT NULL,
  `save` tinyint(1) NOT NULL,
  `criteria_id` int(9) NOT NULL,
  `update_activity` tinyint(1) NOT NULL,
  `owner` mediumint(5) NOT NULL,
  `campaign_id` varchar(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `criteria_id` (`criteria_id`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Saves the content of mass emails and campaign emails.'";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_sections` (
  `name` varchar(35) NOT NULL,
  `display_title` varchar(50) NOT NULL,
  `url` varchar(150) NOT NULL,
  `subsection` varchar(35) NOT NULL,
  `main_nav` tinyint(1) NOT NULL,
  `secure` TINYINT( 1 ) NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_sessions` (
  `id` varchar(35) NOT NULL,
  `member_id` varchar(20) NOT NULL,
  `created` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `ended` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `ended_by` TINYINT(1) NOT NULL,
  `last_activity` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `ip` varchar(35) NOT NULL,
  `browser` varchar(100) NOT NULL,
  `host` varchar(100) NOT NULL,
  `remember` tinyint(1) NOT NULL,
  `salt` varchar(4) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_shipping` (
  `id` int(9) NOT NULL auto_increment,
  `cart_session` varchar(14) NOT NULL,
  `invoice_id` varchar(35) NOT NULL,
  `company_name` varchar(125) NOT NULL,
  `name` varchar(125) NOT NULL COMMENT 'Name of shipping package',
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `address_line_1` varchar(175) NOT NULL,
  `address_line_2` varchar(175) NOT NULL,
  `city` varchar(75) NOT NULL,
  `state` varchar(50) NOT NULL,
  `zip` varchar(15) NOT NULL,
  `country` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `ship_directions` text NOT NULL,
  `shipped` tinyint(1) NOT NULL,
  `ship_date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `trackable` tinyint(1) NOT NULL,
  `shipping_number` varchar(50) NOT NULL,
  `shipping_provider` varchar(50) NOT NULL,
  `remarks` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `cart_session` (`cart_session`,`invoice_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_shipping_rules` (
  `id` int(8) NOT NULL auto_increment,
  `name` varchar(125) NOT NULL,
  `type` enum('weight','region','qty','total','product','flat') NOT NULL,
  `priority` mediumint(5) NOT NULL,
  `details` text NOT NULL,
  `cost` decimal(6,2) NOT NULL,
  `low` varchar(12) NOT NULL,
  `high` varchar(12) NOT NULL,
  `country` varchar(35) NOT NULL,
  `zip` varchar(11) NOT NULL,
  `state` varchar(3) NOT NULL,
  `product` varchar(35) NOT NULL,
  `created` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `sync_id` varchar(30) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_social_media_feed` (
  `id` int(10) NOT NULL auto_increment,
  `post_id` varchar(30) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `user_type` enum('member','contact','rsvp') NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `site` enum('facebook','twitter','linkedin') NOT NULL,
  `post` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `post_id` (`post_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_sources` (
  `id` mediumint(5) NOT NULL auto_increment,
  `source` varchar(85) NOT NULL,
  `type` enum('form','custom') NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_staff` (
  `id` mediumint(6) NOT NULL auto_increment,
  `username` varchar(50) NOT NULL,
  `password` varchar(150) NOT NULL,
  `salt` varchar(4) NOT NULL,
  `permission_group` smallint(3) NOT NULL,
  `signature` mediumtext NOT NULL,
  `email` varchar(150) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `address_line_1` varchar(80) NOT NULL,
  `address_line_2` varchar(30) NOT NULL,
  `city` varchar(40) NOT NULL,
  `state` varchar(3) NOT NULL,
  `zip` varchar(10) NOT NULL,
  `country` varchar(35) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `fax` varchar(20) NOT NULL,
  `alt_phone` varchar(20) NOT NULL,
  `office_phone` varchar(20) NOT NULL,
    `cell` VARCHAR(15)  NULL,
    `cell_carrier` VARCHAR(20)  NULL,
    `sms_optout` TINYINT(1)  NULL,
    `email_optout` TINYINT(1)  NULL,
  `facebook` varchar(100) NOT NULL,
  `twitter` varchar(80) NOT NULL,
  `linkedin` varchar(100) NOT NULL,
  `department` varchar(50) NOT NULL,
  `occupation` varchar(75) NOT NULL,
  `locked` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `locked_ip` varchar(25) NOT NULL,
  `login_attempts` tinyint(1) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `options` mediumtext NOT NULL,
  `created` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `last_updated` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `owner` mediumint(5) NOT NULL,
  `static` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `permission_group` (`permission_group`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_staff_in` (
  `id` varchar(100) NOT NULL,
  `salt` varchar(4) NOT NULL,
  `masterlog` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `username` varchar(100) NOT NULL,
  `expires` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `remember` tinyint(1) NOT NULL,
  `ip` varchar(25) NOT NULL,
  `complete` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_stats` (
  `key` varchar(70) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY  (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_subscriptions` (
  `id` varchar(22) NOT NULL,
  `member_id` varchar(20) NOT NULL,
  `member_type` enum('member','contact') NOT NULL,
  `order_id` varchar(14) NOT NULL,
  `card_id` varchar(13) NOT NULL COMMENT 'ppSD_cart_billing',
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `last_renewed` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `next_renew` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `next_renew_keep` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `price` decimal(15,2) NOT NULL,
  `retry` smallint(3) NOT NULL,
  `product` varchar(35) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `advance_notice_sent` TINYINT( 1 ),
  `in_trial` tinyint(1) NOT NULL,
  `trial_charge_number` smallint(3) NOT NULL COMMENT 'How many times trial period has been charged',
  `paypal` tinyint(1) NOT NULL COMMENT '1 = PayPal Handles It',
  `paypal_id` varchar(20) NOT NULL COMMENT 'Subscription ID',
  `cancel_date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `cancel_reason` varchar(75) NOT NULL,
  `gateway` varchar(35) NOT NULL,
  `salt` varchar(45) NOT NULL COMMENT 'Used for no-login CC updates',
  PRIMARY KEY  (`id`),
  KEY `member_id` (`member_id`),
  KEY `paypal_id` (`paypal_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_subscription_reattempts` (
  `fail_attempt` smallint(2) NOT NULL,
  `timeframe` varchar(12) NOT NULL,
  `penalty_percent` decimal(5,2) NOT NULL,
  `penalty_fixed` decimal(7,2) NOT NULL,
  `cancel` tinyint(1) NOT NULL,
  PRIMARY KEY  (`fail_attempt`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_tags` (
  `id` int(9) NOT NULL auto_increment,
  `tag` varchar(35) NOT NULL,
  `item_id` varchar(40) NOT NULL,
  `item_type` TINYINT( 1 ) NOT NULL COMMENT  '1 = Upload',
  `owner` mediumint(6) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `item_id` (`item_id`),
  KEY `tag` (`tag`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_tax_classes` (
  `id` smallint(3) NOT NULL auto_increment,
  `state` varchar(35) NOT NULL,
  `country` varchar(35) NOT NULL,
  `zips` MEDIUMTEXT NOT NULL,
  `percent_physical` decimal(5,3) NOT NULL,
  `percent_digital` decimal(5,3) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_temp` (
  `id` varchar(40) NOT NULL,
  `data` longtext NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Temporary information for previewing and other short-term ca'";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_templates` (
  `id` varchar(65) NOT NULL,
  `path` varchar(150) NOT NULL,
  `theme` varchar(25) NOT NULL,
  `subtemplate` varchar(65) NOT NULL,
  `title` varchar(100) NOT NULL,
  `desc` varchar(255) NOT NULL,
  `caller_tags` text NOT NULL,
  `order` smallint(3) NOT NULL,
  `custom_header` varchar(65) NOT NULL,
  `custom_footer` varchar(65) NOT NULL,
  `custom_template` varchar(65) NOT NULL,
  `type` tinyint(1) NOT NULL COMMENT '1 = Header, 2 = Footer, 3 = Custom template, 0 = Template, 4 = page',
  `section` varchar(35) NOT NULL,
  `content` mediumtext NOT NULL,
  `secure` tinyint(1) NOT NULL,
  `static` tinyint(1) NOT NULL,
  `owner` mediumint(5) NOT NULL,
  `encrypt` TINYINT( 1 ) NOT NULL,
  `meta_title` VARCHAR( 65 ) NOT NULL,
  `lang` VARCHAR( 3 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'en',
  PRIMARY KEY  (`id`),
  KEY `subtemplate` (`subtemplate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_templates_lang` (
  `up` int(9) NOT NULL auto_increment,
  `id` varchar(65) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `content` mediumtext NOT NULL,
  `lang` varchar(3) NOT NULL,
  `meta_title` VARCHAR( 65 ) NOT NULL,
  PRIMARY KEY  (`up`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_templates_email` (
  `template` varchar(35) NOT NULL,
  `title` varchar(100) NOT NULL,
  `desc` varchar(255) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `to` varchar(125) NOT NULL,
  `from` varchar(80) NOT NULL,
  `cc` varchar(255) NOT NULL,
  `bcc` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `format` tinyint(1) NOT NULL COMMENT '1 = html / 0 = plain text',
  `status` tinyint(1) NOT NULL,
  `save` tinyint(1) NOT NULL,
  `track` tinyint(1) NOT NULL,
  `track_links` tinyint(1) NOT NULL,
  `caller_tags` text NOT NULL,
  `custom` tinyint(1) NOT NULL COMMENT 'If this was custom created by the user.',
  `owner` mediumint(6) NOT NULL,
  `public` tinyint(1) NOT NULL,
  `created` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `header_id` varchar(35) NOT NULL,
  `footer_id` varchar(35) NOT NULL,
  `static` tinyint(1) NOT NULL,
  `default_for` tinyint(1) NOT NULL COMMENT '1 = email, 2 = targeted, 3 = scheduled',
  `theme` varchar(25) NOT NULL,
  `type` enum('template','header','footer') NOT NULL,
  KEY `template` (`template`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_themes` (
  `id` varchar(25) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `author` varchar(45) NOT NULL,
  `author_url` varchar(255) NOT NULL,
  `img_1` varchar(150) NOT NULL,
  `img_2` varchar(150) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `type` enum('html','email','mobile') NOT NULL,
  `style` enum('Clean','Minimalist','Experimental','Corporate','Colorful','Dark','Other') NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_tracking_activity` (
  `id` int(10) NOT NULL auto_increment,
  `track_id` varchar(32) NOT NULL,
  `type` enum('order','member','contact','rsvp','invoice') NOT NULL,
  `act_id` varchar(35) NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `campaign_id` varchar(11) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `track_id` (`track_id`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "
CREATE TABLE IF NOT EXISTS `ppSD_trash_bin` (
  `id` int(9) NOT NULL auto_increment,
  `act_id` varchar(35) NOT NULL,
  `data` mediumtext NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `act_id` (`act_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_uploads` (
  `id` varchar(30) NOT NULL,
  `item_id` varchar(35) NOT NULL COMMENT 'Matches either the ppSD_members id or ppSD_contacts id',
  `type` enum('member','contact','event','product','cart_category','digital_product','employee') NOT NULL,
  `filename` varchar(35) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `downloaded` int(8) NOT NULL,
  `label` varchar(25) NOT NULL COMMENT 'Optional label that can be sent from the hidden form.',
  `cp_only` tinyint(1) NOT NULL COMMENT '1 = only visible on admin CP',
  `note_id` varchar(35) NOT NULL,
  `owner` mediumint(9) NOT NULL,
  `email_id` varchar(35) NOT NULL,
  `byuser` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `username` (`item_id`),
  KEY `label` (`label`),
  KEY `note_id` (`note_id`),
  KEY `owner` (`owner`),
  KEY `email_id` (`email_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_usage_logs` (
  `id` int(10) NOT NULL auto_increment,
  `start_date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `end_date` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `username` varchar(150) NOT NULL,
  `act_id` varchar(35) NOT NULL,
  `type` enum('staff','user') NOT NULL,
  `success` tinyint(1) NOT NULL,
  `msg` varchar(255) NOT NULL,
  `task` varchar(75) NOT NULL,
  `ip` varchar(25) NOT NULL,
  `session` varchar(30) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `username` (`username`,`task`),
  KEY `session` (`session`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_widgets` (
  `id` varchar(75) NOT NULL,
  `name` varchar(45) NOT NULL,
  `type` enum('plugin','menu','html','code','upload_list') NOT NULL,
  `menu_type` enum('horizontal','vertical') NOT NULL,
  `content` longtext NOT NULL,
  `active` tinyint(1) NOT NULL,
  `add_class` varchar(50) NOT NULL,
  `author` VARCHAR( 35 ) NOT NULL ,
  `author_url` VARCHAR( 120 ) NOT NULL ,
  `author_twitter` VARCHAR( 120 ) NOT NULL ,
  `version` VARCHAR( 6 ) NOT NULL ,
  `installed` datetime NOT NULL DEFAULT  '0000-00-00 00:00:00',
  `original_creator` VARCHAR( 40 ) NOT NULL,
  `original_creator_url` VARCHAR( 120 ) NOT NULL ,
  `description` TEXT NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_widgets_menus` (
  `id` mediumint(6) NOT NULL auto_increment,
  `widget_id` varchar(25) NOT NULL,
  `submenu` mediumint(6) NOT NULL,
  `title` varchar(125) NOT NULL,
  `link` varchar(255) NOT NULL,
  `link_type` tinyint(1) NOT NULL COMMENT '1 = cms page, 2 = full url, 3 = onsite build url',
  `link_target` enum('same','new') NOT NULL,
  `position` smallint(3) NOT NULL,
  `content_id` INT( 9 ) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `submenu` (`submenu`),
  KEY `widget_id` (`widget_id`),
  KEY `link` (`link`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
