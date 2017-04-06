<?php

/**
 * Installation: MySQL Table Creation
 *
 * A successful installation should have 133 tables.
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
  `id` int(9) auto_increment,
  `ip` varchar(30),
  `time` varchar(15),
  PRIMARY KEY  (`id`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_access_granters` (
  `id` int(9) auto_increment,
  `item_id` varchar(35) COMMENT 'ppSD_products ID or ppSD_',
  `type` enum('content','newsletter'),
  `grants_to` varchar(25) COMMENT 'ppSD_content ID ppSD_event_timeline ID or ppSD_events ID',
  `timeframe` varchar(12) COMMENT 'For subscription product, always matches that timeframe.',
  PRIMARY KEY  (`id`),
  KEY `item_id` (`item_id`,`grants_to`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_accounts` (
  `id` varchar(10),
  `name` varchar(65),
  `contact_frequency` varchar(12),
  `created` datetime DEFAULT '1920-01-01 00:01:01',
  `default` tinyint(1) DEFAULT '0',
  `master_user` varchar(20) COMMENT 'This is a ppSD_member ID.',
  `owner` mediumint(6),
  `public` tinyint(1) DEFAULT '0',
  `last_updated` datetime DEFAULT '1920-01-01 00:01:01',
  `last_updated_by` mediumint(6),
  `last_action` datetime DEFAULT '1920-01-01 00:01:01',
  `source` mediumint(5),
  `status` tinyint(1) DEFAULT '0',
  `start_page` VARCHAR( 255 ),
  PRIMARY KEY  (`id`),
  KEY `master_user` (`master_user`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_account_data` (
  `account_id` varchar(10),
  `address_line_1` varchar(125),
  `address_line_2` varchar(75),
  `city` varchar(45),
  `state` varchar(50),
  `zip` varchar(10),
  `country` varchar(45),
  `phone` varchar(20),
  `office_phone` varchar(25),
  `alt_phone` varchar(25),
  `fax` varchar(25),
  `company_name` varchar(50),
  `url` varchar(125),
  `industry` varchar(45),
  `account_type` varchar(25),
  `email_optout` tinyint(1) DEFAULT '0',
  `facebook` varchar(125),
  `twitter` varchar(125),
  `linkedin` varchar(125),
  PRIMARY KEY  (`account_id`),
  KEY `company_name` (`company_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_account_types` (
  `id` mediumint(4) auto_increment,
  `type` varchar(35),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_activity_methods` (
  `id` varchar(35),
  `icon` varchar(35),
  `link` varchar(35),
  `link_type` enum('popup','popup_large','link','slider'),
  `custom` tinyint(1) DEFAULT '1',
  `text` VARCHAR( 75 ),
  `in_feed` TINYINT( 1 ) DEFAULT '1',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_bounced_emails` (
  `id` int(10) auto_increment,
  `email_id` varchar(35),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `user_id` varchar(25),
  `user_type` enum('member','contact','rsvp'),
  PRIMARY KEY  (`id`),
  KEY `email_id` (`email_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cache` (
  `act_id` varchar(45),
  `data` longtext,
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `expires` datetime DEFAULT '1920-01-01 00:01:01',
  PRIMARY KEY  (`act_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_calendars` (
  `id` mediumint(4) auto_increment,
  `name` varchar(65),
  `template` mediumint(5),
  `members_only` tinyint(1) DEFAULT '1',
  `owner` mediumint(6),
  `public` tinyint(1) DEFAULT '1',
  `created` datetime DEFAULT '1920-01-01 00:01:01',
  `style` tinyint(1) DEFAULT '1' COMMENT '1 = Calendar, 2 = Long List, 3 = Cloud',
  PRIMARY KEY  (`id`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_campaigns` (
  `id` varchar(11),
  `when_type` enum('after_join','exact_date'),
  `criteria_id` int(9),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `type` enum('email','sms','facebook','twitter'),
  `user_type` enum('member','contact','rsvp','account'),
  `name` varchar(85),
  `kill_condition` enum('on_open','unsubscribe','purchase','register','form_submit','rsvp'),
  `owner` mediumint(5),
  `public` tinyint(1) DEFAULT '1',
  `status` tinyint(1) DEFAULT '1' COMMENT '1 = Active, 2 = Paused',
  `update_activity` tinyint(1) DEFAULT '1',
  `last_sent` datetime DEFAULT '1920-01-01 00:01:01',
  `optin_type` enum('criteria','single_optin','double_optin'),
  PRIMARY KEY  (`id`),
  KEY `criteria_id` (`criteria_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_campaign_items` (
  `id` int(9) auto_increment,
  `title` varchar(85),
  `campaign_id` varchar(11),
  `msg_id` varchar(35),
  `when_timeframe` varchar(12),
  `when_date` datetime DEFAULT '1920-01-01 00:01:01',
  `template_id` varchar(10),
  PRIMARY KEY  (`id`),
  KEY `msg_id` (`msg_id`),
  KEY `template_id` (`template_id`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_campaign_logs` (
  `id` int(9) auto_increment,
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `user_id` varchar(35),
  `user_type` enum('member','contact','rsvp'),
  `trackback_id` varchar(27),
  `campaign_id` varchar(11),
  `saved_id` varchar(35) COMMENT 'Matches ppSD_saved_emails',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_campaign_subscriptions` (
  `id` int(11) auto_increment,
  `campaign_id` varchar(11),
  `user_id` varchar(25),
  `user_type` enum('member','contact'),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `subscribed_by` enum('user',  'employee',  'condition',  'criteria'),
  `subscribed_by_id` varchar(15),
  `active` tinyint(1) DEFAULT '1',
  `optin_id` varchar(20),
  `optin_date` datetime DEFAULT '1920-01-01 00:01:01',
  PRIMARY KEY  (`id`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Campaigns can be criteria based or subscription-based.'";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_campaign_unsubscribe` (
  `id` int(9) auto_increment,
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `user_id` varchar(20),
  `user_type` enum('member','contact','rsvp'),
  `campaign_id` varchar(11),
  `by` enum('user','staff','kill_condition','bounce'),
  `reason` varchar(255),
  `staff` mediumint(5),
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`,`campaign_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_captcha` (
  `id` int(10) auto_increment,
  `captcha` varchar(25),
  `redirect` mediumtext,
  `type` enum('staff','user'),
  `username` varchar(150) COMMENT 'Can me ip, staff username, or member id',
  `form_session` varchar(40),
  PRIMARY KEY  (`id`),
  KEY `form_session` (`form_session`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cart_billing` (
  `id` varchar(20),
  `email` varchar(125),
  `method` enum('Credit Card','Check','PayPal','Invoice','Other'),
  `card_type` enum('','Visa','Mastercard','Amex','Diners','Discover','JCB'),
  `gateway` varchar(35) COMMENT 'ppSD_payment_gateways',
  `gateway_id_1` varchar(45),
  `gateway_id_2` varchar(45),
  `company_name` varchar(125),
  `first_name` varchar(255),
  `last_name` varchar(255),
  `cc_number` varchar(255),
  `card_exp_yy` varchar(255),
  `card_exp_mm` varchar(255),
  `address_line_1` varchar(255),
  `address_line_2` varchar(255),
  `city` varchar(255),
  `state` varchar(255),
  `zip` varchar(255),
  `country` varchar(255),
  `phone` varchar(25),
  `member_id` varchar(20),
  `salt` varchar(25),
  PRIMARY KEY  (`id`),
  KEY `gateway_id` (`gateway`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cart_categories` (
  `id` mediumint(5) auto_increment,
  `name` varchar(40),
  `description` text,
  `meta_title` varchar(69),
  `meta_desc` varchar(156),
  `meta_keywords` varchar(150),
  `subcategory` mediumint(5),
  `template_id` varchar(65),
  `cols` tinyint(1) DEFAULT '0',
  `search_index` tinyint(1) DEFAULT '0',
  `public` tinyint(1) DEFAULT '0',
  `hide` TINYINT( 1 ) DEFAULT '0',
  `members_only` TINYINT( 1 ) DEFAULT '0',
  `owner` mediumint(6),
  `created` datetime DEFAULT '1920-01-01 00:01:01',
  PRIMARY KEY  (`id`),
  KEY `subcategory` (`subcategory`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cart_coupon_codes` (
  `id` varchar(15),
  `description` varchar(150),
  `dollars_off` decimal(8,2),
  `percent_off` smallint(3),
  `products` text,
  `max_use_overall` int(7),
  `date_start` datetime DEFAULT '1920-01-01 00:01:01',
  `date_end` datetime DEFAULT '1920-01-01 00:01:01',
  `current_customers_only` tinyint(1) DEFAULT '0',
  `max_use_per_user` int(6),
  `cart_minimum` decimal(8,2),
  `type` enum('dollars_off','percent_off','shipping'),
  `flat_shipping` decimal(6,2),
  `created` datetime DEFAULT '1920-01-01 00:01:01',
  `owner` mediumint(6),
  `public` tinyint(1) DEFAULT '1',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cart_coupon_codes_used` (
  `order_id` varchar(14),
  `member_id` varchar(20),
  `code` varchar(15),
  `savings` decimal(8,2),
  `tax` decimal(8,2),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  PRIMARY KEY  (`order_id`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cart_items` (
  `id` int(9) auto_increment,
  `cart_session` varchar(14),
  `product_id` varchar(35),
  `qty` int(7),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `option1` varchar(35),
  `option2` varchar(35),
  `option3` varchar(35),
  `option4` varchar(35),
  `option5` varchar(35),
  PRIMARY KEY  (`id`),
  KEY `cart_session` (`cart_session`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cart_items_complete` (
  `id` int(9) auto_increment,
  `cart_session` varchar(14),
  `product_id` varchar(35),
  `qty` int(7),
  `unit_price` decimal(8,2),
  `subscription_id` varchar(22),
  `status` tinyint(1) default '0',
  `tax` decimal(8,2),
  `tax_rate` decimal(5,2),
  `savings` decimal(8,2),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `option1` varchar(35),
  `option2` varchar(35),
  `option3` varchar(35),
  `option4` varchar(35),
  `option5` varchar(35),
  PRIMARY KEY  (`id`),
  KEY `cart_session` (`cart_session`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cart_refunds` (
  `id` int(9) auto_increment,
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `total` decimal(8,2),
  `reason` text,
  `order_id` varchar(14),
  `type` TINYINT( 1 ) DEFAULT '1' COMMENT  '1 = Refund / 2 = Chargeback',
  `chargeback_fee` DECIMAL( 10, 2 ),
  PRIMARY KEY  (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cart_sessions` (
  `id` varchar(20),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `last_activity` datetime DEFAULT '1920-01-01 00:01:01',
  `date_completed` datetime DEFAULT '1920-01-01 00:01:01',
  `status` tinyint(1) DEFAULT '1' COMMENT '0 = unpaid, 1 = paid, 2 = pending payment, 3 = Partial refund, 4= full refung, 9 = rejected',
  `member_id` varchar(20),
  `member_type` enum('member','contact','rsvp','other'),
  `code` varchar(15),
  `ip` varchar(35),
  `payment_gateway` varchar(35) COMMENT 'Matches the \"code\" in ppSD_payments_gateways',
  `gateway_order_id` varchar(50),
  `gateway_resp_code` varchar(8),
  `state` varchar(3),
  `country` varchar(80),
  `zip` VARCHAR(8),
  `return_path` varchar(255),
  `reg_session` varchar(40),
  `gateway_msg` varchar(125),
  `shipping_rule` mediumint(5),
  `shipping_name` varchar(125),
  `card_id` varchar(13),
  `salt` varchar(25) COMMENT 'For PayPal IPN notification',
  `agreed_to_terms` tinyint(1) DEFAULT '0',
  `saw_upsell` TINYINT( 1 ) DEFAULT '0',
  `dependencies` TINYINT( 1 ) DEFAULT '0',
  `dependency_submitted` TEXT,
  `invoice_id` varchar(35),
  `return_time_out` DATETIME DEFAULT '1920-01-01 00:01:01',
  `return_code` VARCHAR( 45 ),
  `donation` TINYINT(1) DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `member_id` (`member_id`),
  KEY `invoice_id` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "
CREATE TABLE IF NOT EXISTS `ppSD_cart_session_totals` (
  `tid` int(9) AUTO_INCREMENT,
  `id` varchar(14),
  `total` decimal(15,2) COMMENT 'This reflects actual income.',
  `gateway_fees` decimal(15,2),
  `subtotal` decimal(15,2),
  `subtotal_nosave` decimal(15,2) COMMENT 'Subtotal of item prices after all savings and volume discounts.',
  `shipping` decimal(15,2),
  `tax` decimal(15,2),
  `tax_rate` decimal(5,2),
  `savings` decimal(15,2),
  `refunds` decimal(15,2),
  `invoice_due` decimal(15,2),
  `invoice_paid` decimal(15,2),
  PRIMARY KEY (`tid`),
  KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cart_terms` (
  `id` smallint(4) auto_increment,
  `name` varchar(125),
  `terms` mediumtext,
  `created` datetime DEFAULT '1920-01-01 00:01:01',
  `owner` mediumint(6),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_cart_tracking` (
  `id` int(9) auto_increment,
  `cart_session` varchar(14),
  `page` varchar(100),
  `query` varchar(255),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
//`type` ENUM(  'Contact',  'Lead',  'Opportunity',  'Customer' ),
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_contacts` (
  `id` varchar(20),
  `type` INT(5) DEFAULT '1',
  `created` datetime DEFAULT '1920-01-01 00:01:01',
  `bounce_notice` DATETIME DEFAULT '1920-01-01 00:01:01',
  `last_updated` datetime DEFAULT '1920-01-01 00:01:01',
  `last_action` datetime DEFAULT '1920-01-01 00:01:01',
  `next_action` datetime DEFAULT '1920-01-01 00:01:01',
  `email_optout` datetime DEFAULT '1920-01-01 00:01:01',
  `sms_optout` datetime DEFAULT '1920-01-01 00:01:01',
  `owner` mediumint(5) COMMENT 'ppSD_staff ID, or 2 = system = unassigned',
  `email` varchar(125),
  `last_updated_by` mediumint(6),
  `source` mediumint(5),
  `account` varchar(10),
  `status` tinyint(1) DEFAULT '1' COMMENT '1 = Active, 2 = Converted, 3 = Dead',
  `public` tinyint(1) DEFAULT '1' COMMENT '1 = All can see, 0 = admin and owner, 2 = permission group only',
  `email_pref` enum('html','text'),
  `converted` tinyint(1) DEFAULT '0',
  `converted_id` int(9) COMMENT 'Matches ppSD_lead_conversion ID',
  `expected_value` decimal(10,2),
  `actual_dollars` decimal(10,2),
  PRIMARY KEY  (`id`),
  KEY `owner` (`owner`),
  KEY `account` (`account`),
  KEY `source` (`source`),
  KEY `converted` (`converted_id`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_contact_data` (
  `contact_id` varchar(20),
  `first_name` varchar(40),
  `last_name` varchar(40),
  `address_line_1` varchar(80),
  `address_line_2` varchar(30),
  `city` varchar(40),
  `state` varchar(50),
  `zip` varchar(10),
  `country` varchar(35),
  `phone` varchar(20),
  `cell` varchar(15),
  `cell_carrier` varchar(20),
  `office_phone` varchar(20),
  `alt_phone` varchar(20),
  `fax` varchar(15),
  `company_name` varchar(50),
  `url` varchar(100),
  `facebook` varchar(100),
  `twitter` varchar(80),
  `linkedin` varchar(100),
  `dob` date DEFAULT '1920-01-01',
  `occupation` varchar(40),
  `sms_optout` tinyint(1) DEFAULT '0',
  `email_optout` tinyint(1) DEFAULT '0',
  PRIMARY KEY  (`contact_id`),
  KEY `last_name` (`last_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_content` (
  `id` int(9) auto_increment,
  `permalink` varchar(150),
  `permalink_clean` varchar(150),
  `name` varchar(100),
  `type` enum('folder','page','redirect','section','file','newsletter','profile','user_group'),
  `path` varchar(255),
  `url` varchar(255),
  `additional_update_fieldsets` varchar(255) COMMENT 'CSV of fieldset IDs',
  `display_on_usercp` tinyint(1) DEFAULT '0',
  `owner` mediumint(5),
  `section` varchar(35),
  `secure` TINYINT( 1 ) DEFAULT '0',
  `section_homepage` INT( 9 ),
  PRIMARY KEY  (`id`),
  KEY `permalink` (`permalink`),
  KEY `section_homepage` (`section_homepage`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_content_access` (
  `id` int(9) auto_increment,
  `added` datetime DEFAULT '1920-01-01 00:01:01',
  `expires` datetime DEFAULT '1920-01-01 00:01:01',
  `timeframe` varchar(12),
  `member_id` varchar(20),
  `content_id` int(9),
  PRIMARY KEY  (`id`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_criteria_cache` (
  `id` int(9) auto_increment,
  `criteria` mediumtext,
  `search_id` varchar(29),
  `act_id` VARCHAR( 30 ),
  `email_id` varchar(13),
  `save` tinyint(1) DEFAULT '0',
  `name` varchar(85),
  `type` enum('member','contact','rsvp','account','campaign','transaction','subscription','invoice'),
  `act` enum('email','search','print','campaign','export','other'),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `inclusive` enum('or','and'),
  `public` tinyint(1) DEFAULT '0',
  `owner` mediumint(5),
  PRIMARY KEY  (`id`),
  KEY `search_id` (`search_id`,`email_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_custom_actions` (
  `id` int(9) auto_increment,
  `name` VARCHAR( 75 ),
  `trigger` varchar(30) COMMENT 'Can be any task used throughout the program.',
  `trigger_type` TINYINT( 1 ) DEFAULT '0',
  `specific_trigger` varchar(35),
  `when` tinyint(1) DEFAULT '1' COMMENT '1 = before, 2 = after',
  `type` tinyint(1) DEFAULT '1' COMMENT '1 = php include, 2 = email, 3 = mysql query, 4 = curl',
  `data` mediumtext COMMENT 'For include, path to file. For email, it is a data array that goes into email class. For mysql, list of queries.',
  `active` tinyint(1) DEFAULT '1',
  `owner` mediumint(6),
  `created` datetime DEFAULT '1920-01-01 00:01:01',
  `plugin` VARCHAR(50) NULL,
  `order` INT(6) NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_custom_callers` (
  `id` mediumint(6) auto_increment,
  `caller` varchar(35),
  `replacement` varchar(255),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_data_eav` (
  `id` int(9) auto_increment,
  `item_id` varchar(35) COMMENT 'Either ppSD_members ID or ppSD_contacts ID. If none, leave blank.',
  `key` varchar(45),
  `value` longtext,
  PRIMARY KEY  (`id`),
  KEY `item_id` (`item_id`),
  KEY `key` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_departments` (
  `id` mediumint(5) auto_increment,
  `name` varchar(75),
  `head_employee` mediumint(5),
  PRIMARY KEY  (`id`),
  KEY `head_employee` (`head_employee`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE `ppSD_donations` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `member_id` varchar(20) DEFAULT NULL,
  `member_type` enum('member','contact') DEFAULT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `public` tinyint(1) DEFAULT '1',
  `comments` mediumtext,
  PRIMARY KEY (`id`),
  KEY `campaign_id` (`campaign_id`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE `ppSD_donation_campaigns` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `starts` datetime DEFAULT '1920-01-01 00:01:01',
  `ends` datetime DEFAULT '1920-01-01 00:01:01',
  `goal` decimal(10,2) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE `ppSD_donation_amounts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) DEFAULT NULL,
  `name` varchar(60) DEFAULT NULL,
  `description` mediumtext,
  `amount` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_email_scheduled` (
  `id` int(9) auto_increment,
  `to` varchar(85),
  `user_id` varchar(21),
  `user_type` enum('member','contact','rsvp','account'),
  `email_id` varchar(35) COMMENT 'Matches ppSD_saved_email_content',
  `added` datetime DEFAULT '1920-01-01 00:01:01',
  `type` enum('email','sms'),
  `delete_email_after` tinyint(1) DEFAULT '0',
  `campaign` VARCHAR( 11 ),
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='List of scheduled emails waiting to be sent. Deleted after.'";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_email_trackback` (
  `id` varchar(27),
  `email_id` varchar(35) default '0',
  `date` datetime default '1920-01-01 00:01:01',
  `viewed` datetime default '1920-01-01 00:01:01',
  `last_viewed` datetime default '1920-01-01 00:01:01',
  `status` tinyint(1) default '0',
  `times_opened` smallint(4) default '0',
  `user_id` varchar(20),
  `user_type` enum('member','contact','rsvp'),
  `campaign_id` varchar(11),
  `campaign_saved_id` varchar(35),
  `ip` VARCHAR( 30 ),
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `email_id` (`email_id`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_error_codes` (
  `id` mediumint(4) auto_increment,
  `code` varchar(4),
  `msg` text,
  `lang` VARCHAR( 3 ) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'en',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_events` (
  `id` varchar(9),
  `name` varchar(100),
  `tagline` varchar(150),
  `calendar_id` mediumint(4),
  `starts` datetime DEFAULT '1920-01-01 00:01:01',
  `ends` datetime DEFAULT '1920-01-01 00:01:01',
  `start_registrations` datetime DEFAULT '1920-01-01 00:01:01',
  `early_bird_end` datetime DEFAULT '1920-01-01 00:01:01',
  `close_registration` datetime DEFAULT '1920-01-01 00:01:01',
  `created` datetime DEFAULT '1920-01-01 00:01:01',
  `max_rsvps` mediumint(6),
  `members_only_rsvp` tinyint(1) DEFAULT '0',
  `members_only_view` tinyint(1) DEFAULT '0',
  `allow_guests` tinyint(1) DEFAULT '0',
  `max_guests` smallint(2),
  `description` mediumtext,
  `post_rsvp_message` mediumtext,
  `online` tinyint(1) DEFAULT '0' COMMENT '1 = online event, 2 = offline event',
  `url` varchar(255),
  `location_name` varchar(50),
  `address_line_1` varchar(125),
  `address_line_2` varchar(75),
  `city` varchar(45),
  `state` varchar(50),
  `zip` varchar(10),
  `country` varchar(45),
  `phone` varchar(20),
  `all_day` tinyint(1) DEFAULT '0',
  `custom_template` varchar(60),
  `custom_email_template` varchar(35),
  `custom_email_guest_template` varchar(35),
  `owner` mediumint(6),
  `public` tinyint(1) DEFAULT '0',
  `status` tinyint(1) DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `calendar_id` (`calendar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_event_products` (
  `id` int(9) auto_increment,
  `product_id` varchar(35),
  `event_id` varchar(9),
  `type` tinyint(1) DEFAULT '1' COMMENT '1 = rsvp, 2 = guest rsvp, 3 = addon, 4 = early bird, 6= early bird member, 5 = member pricing',
  PRIMARY KEY  (`id`),
  KEY `product_id` (`product_id`,`event_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_event_reminders` (
  `id` int(9) auto_increment,
  `event_id` varchar(9) character set latin1,
  `send_date` date,
  `sent_on` date,
  `timeframe` varchar(12) character set latin1,
  `when` enum('before','after'),
  `template_id` varchar(35) character set latin1,
  `sms` tinyint(1) DEFAULT '0',
  `custom_message` text,
  PRIMARY KEY  (`id`),
  KEY `event_id` (`event_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_event_reminder_logs` (
  `id` int(10) auto_increment,
  `event_id` varchar(9),
  `rsvp_id` varchar(21),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `msg_id` int(9),
  `status` TINYINT( 1 ) DEFAULT '1',
  `status_msg` VARCHAR( 100 ),
  PRIMARY KEY  (`id`),
  KEY `event_id` (`event_id`,`rsvp_id`),
  KEY `msg_id` (`msg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_event_rsvps` (
  `id` varchar(21),
  `event_id` varchar(9),
  `user_id` varchar(20) COMMENT 'The ppSD_member ID.',
  `email` varchar(85),
  `bounce_notice` DATETIME DEFAULT '1920-01-01 00:01:01',
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `arrived_date` datetime DEFAULT '1920-01-01 00:01:01',
  `type` tinyint(1) DEFAULT '1' COMMENT '1 = Primary / 2 = Guest',
  `primary_rsvp` varchar(21) COMMENT 'For guests, this is the main RSVP ID.',
  `order_id` varchar(15),
  `status` tinyint(1) DEFAULT '0' COMMENT '1 = Paid / 2 = Pending Payment',
  `checked_in_by` mediumint(5),
  `arrived` tinyint(1) DEFAULT '0',
  `qrcode_key` varchar(65),
  `ip` varchar(35),
  PRIMARY KEY  (`id`),
  KEY `event_id` (`event_id`),
  KEY `primary_rsvp` (`primary_rsvp`),
  KEY `user_id` (`user_id`),
  KEY `qrcode_key` (`qrcode_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_event_rsvp_data` (
  `rsvp_id` varchar(21),
  `first_name` varchar(40),
  `last_name` varchar(40),
  `address_line_1` varchar(80),
  `address_line_2` varchar(30),
  `city` varchar(40),
  `state` varchar(50),
  `zip` varchar(10),
  `country` varchar(35),
  `phone` varchar(20),
  `cell` varchar(20),
  `cell_carrier` varchar(20),
  `sms_optout` tinyint(1) DEFAULT '0',
  PRIMARY KEY  (`rsvp_id`),
  KEY `last_name` (`last_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_event_tags` (
  `id` int(7) auto_increment,
  `tag` smallint(3) COMMENT 'Matches ID in ppSD_event_types',
  `event_id` varchar(9),
  PRIMARY KEY  (`id`),
  KEY `tag` (`tag`,`event_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_event_timeline` (
  `id` int(9) auto_increment,
  `event_id` varchar(9),
  `starts` datetime DEFAULT '1920-01-01 00:01:01',
  `ends` datetime DEFAULT '1920-01-01 00:01:01',
  `title` varchar(100),
  `description` text,
  `location_name` varchar(50),
  `address_line_1` varchar(125),
  `address_line_2` varchar(75),
  `city` varchar(45),
  `state` varchar(3),
  `zip` varchar(10),
  `country` varchar(45),
  `phone` varchar(20),
  PRIMARY KEY  (`id`),
  KEY `event_id` (`event_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_event_types` (
  `id` smallint(3) auto_increment,
  `name` varchar(35),
  `color` varchar(6),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";

$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_favorites` (
  `id` int(9) auto_increment,
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `user_id` varchar(25),
  `user_type` enum('member','contact','account'),
  `owner` mediumint(5),
  `ref_name` varchar(75),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_fields` (
  `id` varchar(60),
  `display_name` varchar(85),
  `type` enum('text','textarea','radio','select','checkbox','attachment','section','multiselect','multicheckbox','linkert','date'),
  `special_type` enum('','formatting','date','datetime','url','password','email','random_id','terms','phone','state','country','cell_carriers','cc','cc_expiration'),
  `logic` tinyint(1) DEFAULT '0',
  `logic_dependent` tinyint(1) DEFAULT '0' COMMENT '1 = Top level logic / 2 = Second level logic',
  `desc` mediumtext,
  `label_position` enum('top','left'),
  `options` mediumtext,
  `styling` mediumtext,
  `default_value` varchar(85),
  `encrypted` tinyint(1) DEFAULT '0',
  `sensitive` tinyint(1) DEFAULT '0' COMMENT 'Hides data on previews.',
  `maxlength` smallint(3),
  `settings` mediumtext,
  `permissions_group` tinyint(3) DEFAULT '0',
  `primary` tinyint(1) DEFAULT '0',
  `static` tinyint(1) DEFAULT '0',
  `data_type` tinyint(1) DEFAULT '0' COMMENT '1 = all, 2 = letters, 3 = numbers, 4 = letters/numbers',
  `min_len` mediumint(5),
  `scope_member` tinyint(1) DEFAULT '0',
  `scope_contact` tinyint(1) DEFAULT '0',
  `scope_rsvp` tinyint(1) DEFAULT '0',
  `scope_account` tinyint(1) DEFAULT '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_fieldsets` (
  `id` mediumint(5) auto_increment,
  `name` varchar(85),
  `desc` mediumtext,
  `order` smallint(3),
  `columns` tinyint(1) DEFAULT '1',
  `logic_dependent` tinyint(1) DEFAULT '0',
  `static` tinyint(1) DEFAULT '0' COMMENT '1 = Default, no delete, 2 = Custom, 0 = Custom but not visible (events, etc.)',
  `owner` mediumint(5),
  `billing` tinyint(1) DEFAULT '0' COMMENT 'Used to prevent displaying billing data in dropdowns to avoid confusion.',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_fieldsets_fields` (
  `id` int(7) auto_increment,
  `fieldset` mediumint(5),
  `field` varchar(25),
  `order` mediumint(4),
  `req` tinyint(1) DEFAULT '0',
  `column` tinyint(1) DEFAULT '1',
  `tabindex` mediumint(4),
  `autoadd_product` varchar(11) COMMENT 'Mainly used for event registration',
  `autoadd_value` varchar(100) COMMENT 'If \"autoadd_value\" is selected for the input, \"autoadd_product\" will be added to cart.',
  PRIMARY KEY  (`id`),
  KEY `field` (`field`),
  KEY `fieldset` (`fieldset`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_fieldsets_locations` (
  `id` smallint(3) auto_increment,
  `location` varchar(35) COMMENT 'account-ID for account-specific sets',
  `act_id` varchar(35),
  `order` smallint(3),
  `col` smallint(2),
  `fieldset_id` mediumint(5),
  PRIMARY KEY  (`id`),
  KEY `fieldset_id` (`fieldset_id`),
  KEY `act_id` (`act_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_field_logic` (
  `id` int(6) auto_increment,
  `field_id` varchar(8),
  `field_value` varchar(85),
  `display_type` enum('field','fieldset','msg_popup','msg_inline','email','text'),
  `display_id` varchar(8),
  `display_msg` mediumtext,
  `template_id` mediumint(5),
  PRIMARY KEY  (`id`),
  KEY `field_id` (`field_id`,`display_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_forms` (
  `id` varchar(25),
  `type` enum('admin_cp','payment_form','register-free','contact','update_account','event','register-paid','campaign','dependency','update'),
  `criteria` mediumtext  NULL DEFAULT '',
  `act_id` varchar(20) NULL DEFAULT '',
  `name` varchar(50),
  `description` text,
  `code_required` tinyint(1) DEFAULT '0',
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `owner` mediumint(6),
  `public` tinyint(1) DEFAULT '0',
  `reg_status` varchar(1) COMMENT 'Registrations only: A, P (email approve), Y (admin approve)',
  `pages` tinyint(1) DEFAULT '1',
  `member_type` MEDIUMINT( 5 ),
  `preview` tinyint(1) DEFAULT '0',
  `step1_name` varchar(65),
  `step2_name` varchar(65),
  `step3_name` varchar(65),
  `step4_name` varchar(65),
  `step5_name` varchar(65),
  `public_list` tinyint(1) DEFAULT '1',
  `static` tinyint(1) DEFAULT '0',
  `disabled` tinyint(1) DEFAULT '0',
  `account_create` tinyint(1) DEFAULT '0',
  `terms_id` smallint(4),
  `captcha` tinyint(1) DEFAULT '0',
  `redirect` varchar(255),
  `account` varchar(8),
  `source` mediumint(5),
  `email_thankyou` tinyint(1) DEFAULT '0',
  `template` varchar(35),
  `email_forward` text,
  PRIMARY KEY  (`id`),
  KEY `act_id` (`act_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_form_closed_sessions` (
  `code` varchar(29),
  `used` tinyint(1) DEFAULT '0',
  `form_id` varchar(25),
  `date_issued` datetime DEFAULT '1920-01-01 00:01:01',
  `date_used` datetime DEFAULT '1920-01-01 00:01:01',
  `form_session` varchar(40),
  `sent_to` varchar(100),
  PRIMARY KEY  (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_form_conditions` (
  `id` int(11) auto_increment,
  `act_id` varchar(35) COMMENT 'Form ID',
  `type` enum('content','product','campaign','kill','coupon','expected_value','assign_contact'),
  `field_name` varchar(25),
  `field_eq` varchar(4),
  `field_value` varchar(75),
  `condition_id` varchar(35) COMMENT 'Product, campaign, or content id',
  `act_qty` varchar(12) COMMENT 'Could be a timeframe or a qty.',
  PRIMARY KEY  (`id`),
  KEY `form_id` (`act_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_form_submit` (
  `id` VARCHAR(30),
  `form_id` varchar(25),
  `form_name` VARCHAR( 50 ),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `user_id` varchar(25),
  `user_type` ENUM(  'member',  'contact',  'rsvp',  'account' ),
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_form_products` (
  `id` int(9) auto_increment,
  `form_id` varchar(25),
  `product_id` varchar(35),
  `qty_control` tinyint(1) DEFAULT '1' COMMENT '1 = Add 1, 2 = user select',
  `type` tinyint(1) DEFAULT '1' COMMENT '1 = Required / 2 = Optional',
  `order` SMALLINT( 3 ),
  PRIMARY KEY  (`id`),
  KEY `form_id` (`form_id`,`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_form_requests` (
  `id` varchar(31),
  `form_id` varchar(25),
  `member_id` varchar(20),
  `member_type` enum('member','contact','rsvp'),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `expires` datetime DEFAULT '1920-01-01 00:01:01',
  `completed` datetime DEFAULT '1920-01-01 00:01:01',
  PRIMARY KEY  (`id`),
  KEY `form_id` (`form_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_form_sessions` (
  `id` varchar(40),
  `member_id` varchar(20),
  `closed_code` varchar(33),
  `code_approved` tinyint(1) DEFAULT '0',
  `req_login` tinyint(1) DEFAULT '0',
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `last_activity` datetime DEFAULT '1920-01-01 00:01:01',
  `step` smallint(2),
  `form_id` varchar(25),
  `act_id` varchar(20) COMMENT 'Reg form ID or Event ID being acted on',
  `type` enum('register','lead','update','dependency','event','forced_update','campaign','contact'),
  `s1` mediumtext,
  `s2` mediumtext,
  `s3` mediumtext,
  `s4` mediumtext,
  `s5` mediumtext,
  `s6` int(11),
  `s7` int(11),
  `s8` int(11),
  `ip` varchar(35),
  `salt` varchar(6),
  `cart_id` varchar(14),
  `products` text,
  `terms` tinyint(1) DEFAULT '0',
  `final_member_id` varchar(25),
  `redirect` VARCHAR( 255 ),
  PRIMARY KEY  (`id`),
  KEY `form_id` (`form_id`),
  KEY `act_id` (`act_id`),
  KEY `member_id` (`member_id`),
  KEY `cart_id` (`cart_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_history` (
  `id` int(9) auto_increment,
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `method` varchar(75),
  `owner` mediumint(5) COMMENT 'c7_staff ID',
  `notes` mediumtext,
  `plugin` VARCHAR(30) NULL,
  `user_id` varchar(35) COMMENT 'ppSD_members ID or ppSD_contacts ID',
  `act_id` varchar(35) COMMENT 'If possible, the ID of the action, such as the email or note.',
  `type` tinyint(1) DEFAULT '1' COMMENT '1 = member, 2 = contact, 3 = rsvp, 4 = other',
  PRIMARY KEY  (`id`),
  KEY `owner` (`owner`),
  KEY `user_id` (`user_id`),
  KEY `act_id` (`act_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='This is basically an activity feed for users.'";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_homepage_widgets` (
  `id` varchar(25),
  `options` text,
  `title` varchar(50),
  `perms` enum('admin','all'),
  `static` tinyint(1) DEFAULT '0',
  `employee` mediumint(5),
  `add_fields` text,
  `hide` tinyint(1) DEFAULT '0',
  `custom` TINYINT( 1 ) DEFAULT '1',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_invoices` (
  `id` varchar(35),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `last_reminder` datetime DEFAULT '1920-01-01 00:01:01',
  `date_due` datetime DEFAULT '1920-01-01 00:01:01',
  `total_reminders` smallint(3),
  `order_id` varchar(14) COMMENT 'Mainly used to associate totals and shipping data',
  `member_id` varchar(20),
  `member_type` enum('member','contact'),
  `status` tinyint(1) DEFAULT '0' COMMENT '0 = Unpaid, 1 = Paid, 2 = Partial Payment, 3 = Overdue, 4 = Dead',
  `salt` varchar(4),
  `hash` varchar(60),
  `tax_rate` decimal(5,2),
  `shipping_rule` mediumint(5),
  `shipping_name` varchar(125),
  `ip` varchar(35),
  `owner` mediumint(5),
  `hourly` decimal(8,2),
  `rsvp_id` varchar(21),
  `rolling_invoice` TINYINT(1) DEFAULT '0',
  `auto_inform` TINYINT( 1 ) DEFAULT '0',
  `sub_id` VARCHAR(30) NULL,
  PRIMARY KEY  (`id`),
  KEY `rsvp_id` (`rsvp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_invoice_components` (
  `id` int(9) auto_increment,
  `invoice_id` varchar(35),
  `type` enum('product','time','credit'),
  `minutes` int(8),
  `hourly` decimal(8,2),
  `product_id` varchar(35),
  `qty` int(7),
  `unit_price` decimal(8,2),
  `status` tinyint(1) DEFAULT '1',
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `option1` varchar(35),
  `option2` varchar(35),
  `option3` varchar(35),
  `option4` varchar(35),
  `option5` varchar(35),
  `name` varchar(85),
  `description` text,
  `owner` mediumint(5),
  `tax` tinyint(1) DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `invoice_id` (`invoice_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_invoice_data` (
  `id` varchar(35),
  `company_name` varchar(80),
  `contact_name` varchar(80),
  `address_line_1` varchar(80),
  `address_line_2` varchar(30),
  `city` varchar(40),
  `state` varchar(3),
  `zip` varchar(10),
  `country` varchar(35),
  `phone` varchar(20),
  `fax` varchar(20),
  `email` varchar(80),
  `website` varchar(125),
  `memo` text,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_invoice_payments` (
  `id` int(11) auto_increment,
  `order_id` varchar(14),
  `invoice_id` varchar(35),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `paid` decimal(8,2),
  `new_balance` decimal(8,2),
  PRIMARY KEY  (`id`),
  KEY `order_id` (`order_id`,`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_invoice_totals` (
  `id` varchar(35),
  `paid` decimal(8,2),
  `due` decimal(8,2),
  `subtotal` decimal(8,2),
  `shipping` decimal(8,2),
  `tax` decimal(8,2),
  `tax_rate` decimal(5,2),
  `credits` decimal(8,2),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_lead_conversion` (
  `id` int(9) auto_increment,
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `began` datetime DEFAULT '1920-01-01 00:01:01',
  `contact_id` varchar(20),
  `user_id` varchar(20),
  `owner` mediumint(6),
  `estimated_value` decimal(9,2),
  `actual_value` decimal(9,2),
  `percent_change` decimal(5,2),
  PRIMARY KEY  (`id`),
  KEY `username` (`user_id`),
  KEY `owner` (`owner`),
  KEY `contact_id` (`contact_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_link_tracking` (
  `id` varchar(32),
  `email_id` varchar(35),
  `clicked` mediumint(4),
  `first_clicked` datetime DEFAULT '1920-01-01 00:01:01',
  `last_clicked` datetime DEFAULT '1920-01-01 00:01:01',
  `link` varchar(255),
  `campaign_id` varchar(11),
  `campaign_email_id` varchar(35),
  `user_id` VARCHAR( 20 ),
  `user_type` ENUM(  'contact',  'member',  'rsvp' ),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_logins` (
  `id` int(9) auto_increment,
  `session_id` varchar(35),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `member_id` varchar(20),
  `ip` varchar(35),
  `status` tinyint(1) DEFAULT '1',
  `host` VARCHAR(80),
  `browser` VARCHAR(150),
  `browser_short` VARCHAR( 25 ),
  `attempt_no` smallint(3),
  `type` TINYINT(1) DEFAULT '1',
  PRIMARY KEY  (`id`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_login_announcements` (
  `id` int(9) auto_increment,
  `starts` datetime DEFAULT '1920-01-01 00:01:01',
  `ends` datetime DEFAULT '1920-01-01 00:01:01',
  `title` varchar(100),
  `content` mediumtext,
  `show_criteria` int(9) COMMENT 'Matches ppSD_criteria_cache',
  `active` tinyint(1) default '1',
  `created` DATETIME DEFAULT '1920-01-01 00:01:01',
  `owner` MEDIUMINT( 5 ) ,
  `public` TINYINT( 1 ) default '0',
  PRIMARY KEY  (`id`),
  KEY `starts` (`starts`),
  KEY `ends` (`ends`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_login_announcement_logs` (
  `id` int(9) auto_increment,
  `announcement_id` int(9),
  `date` DATETIME DEFAULT '1920-01-01 00:01:01',
  `member_id` varchar(20),
  PRIMARY KEY  (`id`),
  KEY `announcement_id` (`announcement_id`,`member_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_login_temp` (
  `id` int(9) auto_increment,
  `ip` varchar(35),
  `attempt` smallint(2),
  PRIMARY KEY  (`id`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_members` (
  `id` varchar(20),
  `username` varchar(100),
  `password` varchar(255),
  `salt` varchar(6),
  `email` varchar(110),
  `bounce_notice` DATETIME DEFAULT '1920-01-01 00:01:01',
  `joined` datetime default '1920-01-01 00:01:01',
  `last_renewal` DATETIME DEFAULT '1920-01-01 00:01:01',
  `last_action` datetime DEFAULT '1920-01-01 00:01:01',
  `last_login` datetime default '1920-01-01 00:01:01',
  `last_updated` datetime default '1920-01-01 00:01:01',
  `last_date_check` DATETIME COMMENT 'Used for inactivity checks',
  `next_action` datetime DEFAULT '1920-01-01 00:01:01',
  `concurrent_login_date` datetime default '1920-01-01 00:01:01',
  `locked` datetime DEFAULT '1920-01-01 00:01:01',
  `email_optout` datetime DEFAULT '1920-01-01 00:01:01',
  `sms_optout` datetime DEFAULT '1920-01-01 00:01:01',
  `activated` datetime DEFAULT '1920-01-01 00:01:01',
  `last_updated_by` varchar(20),
  `status` char(1),
  `status_msg` varchar(255) COMMENT 'If someone is paused, rejected, etc.. this holds the reason.',
  `conversion_id` int(8) default '0',
  `source` mediumint(5) default '0' COMMENT 'Corresponds to ppSD_sources',
  `concurrent_login_notices` tinyint(3) default '0',
  `public` tinyint(1) DEFAULT '0',
  `owner` mediumint(6),
  `email_pref` enum('html','text'),
  `locked_ip` varchar(25),
  `login_attempts` tinyint(1) DEFAULT '0',
  `account` varchar(10),
  `activation_code` varchar(40),
  `start_page` varchar(255),
  `converted` tinyint(1) DEFAULT '0',
  `converted_id` int(9),
  `listing_display` tinyint(1) DEFAULT '0',
  `member_type` MEDIUMINT( 4 ),
  PRIMARY KEY  (`id`),
  KEY `status` (`status`),
  KEY `username` (`username`),
  KEY `member_type` (`member_type`),
  KEY `account` (`account`),
  KEY `source` (`source`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_member_types` (
  `id` mediumint(4) auto_increment,
  `name` varchar(125),
  `order` MEDIUMINT( 5 ),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_member_types_content` (
  `id` int(7) auto_increment,
  `member_type` mediumint(4),
  `act_id` varchar(30),
  `act_type` enum('content','other'),
  PRIMARY KEY  (`id`),
  KEY `member_type` (`member_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_member_activations` (
  `id` int(9) auto_increment,
  `member_id` varchar(20),
  `date` int(11),
  `owner` mediumint(5),
  `status` varchar(1),
  `reason` text,
  PRIMARY KEY  (`id`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_member_data` (
  `member_id` varchar(20),
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
  `sms_optout` tinyint(1) DEFAULT '0',
  `email_optout` tinyint(1) DEFAULT '0',
  `dob` date DEFAULT '1920-01-01',
  `industry` varchar(30),
  `facebook` varchar(100),
  `twitter` varchar(80),
  `linkedin` varchar(100),
  `cell` varchar(15),
  `cell_carrier` varchar(20),
  `alt_phone` varchar(20),
  `office_phone` varchar(20),
  PRIMARY KEY  (`member_id`),
  KEY `last_name` (`last_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_newsletters_subscribers` (
  `id` int(9) auto_increment,
  `user_id` varchar(20),
  `user_type` enum('member','contact'),
  `newsletter_id` varchar(10),
  `added` datetime DEFAULT '1920-01-01 00:01:01',
  `expires` datetime DEFAULT '1920-01-01 00:01:01',
  `unsubscribed` datetime DEFAULT '1920-01-01 00:01:01',
  `double_optin` datetime DEFAULT '1920-01-01 00:01:01',
  `status` tinyint(1) default '1' COMMENT '1 = subscribed, 0 = unsubscribed',
  `activation_code` varchar(35),
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`,`newsletter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_notes` (
  `id` varchar(35),
  `user_id` varchar(35),
  `item_scope` varchar(25),
  `name` varchar(85),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `deadline` datetime DEFAULT '1920-01-01 00:01:01',
  `completed_on` DATETIME DEFAULT '1920-01-01 00:01:01',
  `seen_date` DATETIME DEFAULT '1920-01-01 00:01:01',
  `note` mediumtext,
  `added_by` mediumint(6),
  `label` tinyint(2) COMMENT 'Matches ppSD_note_labels',
  `public` tinyint(1) DEFAULT '0' COMMENT '2 = broadcast , 1 = all can see, 0 = creator and admin only',
  `value` decimal(10,2),
  `for` mediumint(5),
  `remove_from_cp` tinyint(1) DEFAULT '0',
  `complete` TINYINT( 1 ) DEFAULT '0',
  `completed_by` MEDIUMINT( 5 ) DEFAULT '0',
  `priority` TINYINT( 1 ) DEFAULT '0',
  `encrypt` TINYINT( 1 ) DEFAULT '0',
  `pin` TINYINT(1) DEFAULT '0',
  `seen` TINYINT(1) DEFAULT '0',
  `external_id` VARCHAR( 30 ) COMMENT  'Used for external calendar links.',
  `advance_pipeline` TINYINT(1) DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  KEY `added_by` (`added_by`),
  KEY `name` (`name`),
  KEY `for` (`for`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_note_labels` (
  `id` smallint(3) auto_increment,
  `label` varchar(35),
  `color` varchar(6),
  `fontcolor` varchar(6),
  `static_lookup` VARCHAR(10) NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_options` (
  `id` varchar(50),
  `display` varchar(75),
  `value` varchar(255),
  `description` varchar(255),
  `type` enum('text','select','radio','checkbox','timeframe','special','file_size','textarea'),
  `width` mediumint(3),
  `options` varchar(100),
  `section` varchar(20),
  `maxlength` mediumint(5),
  `class` varchar(25),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE `ppSD_reminders` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created` datetime DEFAULT '1920-01-01 00:01:00',
  `seen_on` datetime DEFAULT '1920-01-01 00:01:00',
  `remind_on` date DEFAULT NULL,
  `user_id` varchar(30) DEFAULT NULL,
  `user_type` enum('member','contact','account','invoice','transaction','event','other') DEFAULT 'other',
  `title` varchar(100) DEFAULT NULL,
  `message` mediumtext,
  `for` int(11) DEFAULT NULL,
  `seen` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `for` (`for`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_pages` (
  `id` varchar(14),
  `title` varchar(35),
  `meta_title` varchar(69),
  `meta_desc` varchar(156),
  `meta_keywords` varchar(85),
  `members_only` tinyint(1) DEFAULT '0',
  `section` varchar(35),
  `template` varchar(65),
  `content` longtext,
  `live` tinyint(1) DEFAULT '1',
  PRIMARY KEY  (`id`),
  KEY `section` (`section`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_payment_gateways` (
  `id` smallint(3) auto_increment,
  `fee_flat` decimal(6,2),
  `fee_percent` decimal(5,2),
  `test_mode` tinyint(1),
  `active` tinyint(1),
  `code` varchar(20),
  `name` varchar(45),
  `online` varchar(255),
  `api` tinyint(1),
  `local_card_storage` tinyint(1),
  `credential1` varchar(255),
  `credential2` varchar(255),
  `credential3` varchar(255),
  `credential4` varchar(255),
  `primary` tinyint(1),
  `method_cc_visa` tinyint(1),
  `method_cc_amex` int(11),
  `method_cc_mc` int(11),
  `method_cc_discover` int(11),
  `method_check` tinyint(1),
  `method_refund` int(11),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";

$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_permission_groups` (
  `id` smallint(3) auto_increment,
  `name` varchar(50),
  `admin` tinyint(1) DEFAULT '0',
  `owner` mediumint(6) COMMENT 'c7_staff ID',
  `start_page` varchar(125),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";

$create[] = "CREATE TABLE `ppSD_pipeline` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `position` int(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_permission_group_settings` (
  `id` int(9) auto_increment,
  `group_id` tinyint(3),
  `scope` varchar(25),
  `action` varchar(25),
  `allowed` enum('all','owned','none'),
  PRIMARY KEY  (`id`),
  KEY `group` (`group_id`),
  KEY `permission` (`scope`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";

$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_products` (
  `id` varchar(35),
  `associated_id` varchar(20) DEFAULT '' COMMENT 'Event, etc.',
  `name` varchar(85),
  `tagline` varchar(255),
  `description` text,
  `type` tinyint(1) COMMENT '1 = one time, 2 = subscription, 3 = trial, 4 = donation',
  `physical` tinyint(1) COMMENT '1 = physical but no shipping, 2 = physical and needs shipping',
  `tax_exempt` tinyint(1) DEFAULT '0',
  `cost_in_credits` int(7),
  `grant_credits` int(7),
  `price` decimal(15,2),
  `upfront_cost` decimal(15,2),
  `trial_price` decimal(15,2),
  `trial_period` varchar(12),
  `trial_repeat` smallint(3),
  `renew_max` mediumint(5),
  `renew_timeframe` varchar(12),
  `threshold_date` VARCHAR(5) COMMENT 'For subscriptions, if started after a certain date, it will automatically set the next renewal to threshold_date_set',
  `threshold_date_set` datetime DEFAULT '1920-01-01 00:01:01',
  `hide` tinyint(1),
  `hide_in_admin` tinyint(1) DEFAULT '0',
  `weight` decimal(8,2),
  `member_type` MEDIUMINT( 5 ) ,
  `cart_ordering` mediumint(6),
  `category` mediumint(6),
  `attribute_to` varchar(11),
  `max_per_cart` int(7) COMMENT 'Allow quantities to be added or only 1',
  `min_per_cart` INT( 8 ),
  `limit_attr` tinyint(1) COMMENT '1 = Max 1 selected, 2 = Input number to buy',
  `terms` mediumint(4) COMMENT 'Matches ppSD_terms ID',
  `popularity` smallint(6),
  `owner` mediumint(6),
  `public` tinyint(1) DEFAULT '1',
  `created` datetime DEFAULT '1920-01-01 00:01:01',
  `invoice_id` varchar(35) DEFAULT '',
  `featured` tinyint(1) DEFAULT '0',
  `base_popularity` mediumint(5),
  `members_only` TINYINT( 1 ) DEFAULT '0',
  `auto_register` TINYINT(1) DEFAULT '0',
  `sync_id` VARCHAR( 25 ),
  PRIMARY KEY  (`id`),
  KEY `category` (`category`,`attribute_to`),
  KEY `type` (`type`),
  KEY `associated_id` (`associated_id`),
  KEY `invoice_id` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_products_linked` (
  `id` mediumint(5) AUTO_INCREMENT,
  `product_id` varchar(35),
  `package_id` mediumint(9),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `package_id` (`package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_packages` (
  `id` mediumint(5) AUTO_INCREMENT,
  `name` varchar(80),
  `prorate_upgrades` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_products_options` (
  `id` int(9) auto_increment,
  `product_id` varchar(35),
  `option_no` varchar(35),
  `option_value` varchar(35),
  `options` varchar(255),
  PRIMARY KEY  (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_product_dependencies` (
  `id` int(9) auto_increment,
  `type` enum('form'),
  `act_id` varchar(25),
  `options` text,
  `product_id` varchar(35),
  PRIMARY KEY  (`id`), KEY `act_id` (`act_id`,`product_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_products_options_qty` (
  `id` int(9) auto_increment,
  `product_id` varchar(35),
  `option1` varchar(35) COMMENT 'Represents the csv value option in ppSD_product_options',
  `option2` varchar(35),
  `option3` varchar(35),
  `option4` varchar(35),
  `option5` varchar(35),
  `qty` int(7),
  `price_adjust` decimal(8,2),
  `weight_adjust` decimal(6,2),
  `sync_id` VARCHAR( 35 ),
  PRIMARY KEY  (`id`),
  KEY `product_id` (`product_id`),
  KEY `option1` (`option1`,`option2`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Example: for size options, color options, etc.'";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_products_tiers` (
  `id` int(8) auto_increment,
  `product_id` varchar(35),
  `low` mediumint(5),
  `high` mediumint(5),
  `discount` decimal(4,2) COMMENT 'Percentage',
  PRIMARY KEY  (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_product_upsell` (
  `id` int(9) auto_increment,
  `product` varchar(35),
  `upsell` varchar(35),
  `type` enum('popup','checkout'),
  `order` MEDIUMINT( 5 ),
  PRIMARY KEY  (`id`),
  KEY `product` (`product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
/*
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_product_views` (
  `id` int(9) auto_increment,
  `product_id` varchar(35),
  `date` datetime,
  `ip` varchar(35),
  PRIMARY KEY  (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
*/
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_qr_devices` (
  `id` mediumint(6) auto_increment,
  `employee_id` mediumint(6),
  `status` tinyint(1) DEFAULT '1',
  `ip` varchar(35),
  `host` varchar(75),
  `browser` varchar(150),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_routes` (
  `id` int(9) auto_increment,
  `path` varchar(100),
  `resolve` varchar(75),
  `plugin` varchar(75),
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_reset_passwords` (
  `id` varchar(40),
  `member_id` varchar(20),
  `email` varchar(150),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  PRIMARY KEY  (`id`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_saved_emails` (
  `id` varchar(35),
  `date` datetime default '1920-01-01 00:01:01',
  `content` mediumtext,
  `subject` varchar(100),
  `to` varchar(125),
  `from` varchar(125),
  `cc` varchar(125),
  `bcc` varchar(125),
  `sent_by` varchar(50),
  `format` varchar(12),
  `template` varchar(10),
  `type` char(1),
  `newsletter` mediumint(6) default '0',
  `statuses` varchar(100),
  `areas` varchar(255),
  `inclusive` char(1),
  `criteria` char(3),
  `mass_email_id` varchar(26) default '0',
  `user_id` varchar(35),
  `user_type` enum('member','contact','rsvp'),
  `fail` TINYINT( 1 ) DEFAULT '0',
  `fail_reason` VARCHAR( 100 ),
  `sentvia` VARCHAR(30) NULL,
  `vendor_id` VARCHAR(100) NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_saved_sms` (
  `id` int(9) auto_increment,
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `msg` varchar(160),
  `user_id` varchar(25),
  `user_type` enum('member','contact', 'rsvp'),
  `cell` VARCHAR(30) NULL,
  `carrier` VARCHAR(30) NULL,
  `sentvia` VARCHAR(30) NULL,
  `media` VARCHAR(255) NULL,
  `provider_id` VARCHAR(50) NULL,
  `success` TINYINT(1) NULL,
  `code` VARCHAR(10) NULL,
  `message` VARCHAR(50) NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_schedule_social` (
  `id` int(9) auto_increment,
  `type` enum('date','after_join'),
  `post` mediumtext,
  `where` enum('company_feed','post_to_user'),
  `site` enum('facebook','twitter'),
  `post_date` datetime DEFAULT '1920-01-01 00:01:01',
  `post_timeframe` varchar(12),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_saved_email_content` (
  `id` varchar(35),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `message` mediumtext,
  `subject` varchar(150),
  `from` varchar(85),
  `to` varchar(85),
  `reply_to` varchar(85),
  `cc` varchar(255),
  `bcc` varchar(255),
  `trackback` tinyint(1) DEFAULT '0',
  `track_links` tinyint(1) DEFAULT '0',
  `save` tinyint(1) DEFAULT '0',
  `criteria_id` int(9),
  `update_activity` tinyint(1) DEFAULT '0',
  `owner` mediumint(5),
  `campaign_id` varchar(11),
  PRIMARY KEY  (`id`),
  KEY `criteria_id` (`criteria_id`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Saves the content of mass emails and campaign emails.'";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_sections` (
  `name` varchar(35),
  `display_title` varchar(50),
  `url` varchar(150),
  `subsection` varchar(35),
  `main_nav` tinyint(1) DEFAULT '0',
  `secure` TINYINT( 1 ) DEFAULT '0',
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_sessions` (
  `id` varchar(35),
  `member_id` varchar(20),
  `created` datetime DEFAULT '1920-01-01 00:01:01',
  `ended` datetime DEFAULT '1920-01-01 00:01:01',
  `last_activity` datetime DEFAULT '1920-01-01 00:01:01',
  `ended_by` TINYINT(1) DEFAULT '0',
  `ip` varchar(35),
  `browser` varchar(100),
  `host` varchar(100),
  `remember` tinyint(1) DEFAULT '0',
  `salt` varchar(4),
  PRIMARY KEY  (`id`),
  KEY `member_id` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_shipping` (
  `id` int(9) auto_increment,
  `cart_session` varchar(14),
  `invoice_id` varchar(35),
  `company_name` varchar(125),
  `name` varchar(125) COMMENT 'Name of shipping package',
  `first_name` varchar(80),
  `last_name` varchar(80),
  `address_line_1` varchar(175),
  `address_line_2` varchar(175),
  `city` varchar(75),
  `state` varchar(50),
  `zip` varchar(15),
  `country` varchar(50),
  `phone` varchar(20),
  `email` varchar(100),
  `ship_directions` text,
  `shipped` tinyint(1) DEFAULT '0',
  `ship_date` datetime DEFAULT '1920-01-01 00:01:01',
  `trackable` tinyint(1) DEFAULT '0',
  `shipping_number` varchar(50),
  `shipping_provider` varchar(50),
  `remarks` text,
  PRIMARY KEY  (`id`),
  KEY `cart_session` (`cart_session`,`invoice_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_shipping_rules` (
  `id` int(8) auto_increment,
  `name` varchar(125),
  `type` enum('weight','region','qty','total','product','flat'),
  `priority` mediumint(5),
  `details` text,
  `cost` decimal(6,2),
  `low` varchar(12),
  `high` varchar(12),
  `country` varchar(35),
  `zip` varchar(11),
  `state` varchar(3),
  `product` varchar(35),
  `created` datetime DEFAULT '1920-01-01 00:01:01',
  `sync_id` varchar(30),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_social_media_feed` (
  `id` int(10) auto_increment,
  `post_id` varchar(30),
  `user_id` varchar(20),
  `user_type` enum('member','contact','rsvp'),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `site` enum('facebook','twitter','linkedin'),
  `post` text,
  PRIMARY KEY  (`id`),
  KEY `post_id` (`post_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_sources` (
  `id` mediumint(5) auto_increment,
  `source` varchar(85),
  `type` enum('form','custom'),
  `trigger` VARCHAR(50) NULL,
  `redirect` VARCHAR(150) NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE `ppSD_source_tracking` (
  `id` int(11) unsigned AUTO_INCREMENT,
  `source_id` int(11) DEFAULT NULL,
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `converted_date` datetime DEFAULT '1920-01-01 00:01:01',
  `referrer` varchar(255) DEFAULT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `converted` tinyint(1) DEFAULT '0',
  `user_id` varchar(50) DEFAULT NULL,
  `user_type` enum('member','contact','rsvp') DEFAULT NULL,
  `link_variation` enum('-','A','B') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `source_id` (`source_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_staff` (
  `id` mediumint(6) auto_increment,
  `username` varchar(50),
  `password` varchar(150),
  `salt` varchar(4),
  `permission_group` smallint(3),
  `signature` mediumtext,
  `email` varchar(150),
  `first_name` varchar(50),
  `last_name` varchar(50),
  `address_line_1` varchar(80),
  `address_line_2` varchar(30),
  `city` varchar(40),
  `state` varchar(3),
  `zip` varchar(10),
  `country` varchar(35),
  `phone` varchar(20),
  `fax` varchar(20),
  `alt_phone` varchar(20),
  `office_phone` varchar(20),
    `cell` VARCHAR(15)  NULL,
    `cell_carrier` VARCHAR(20)  NULL,
    `sms_optout` TINYINT(1)  NULL,
    `email_optout` TINYINT(1)  NULL,
  `facebook` varchar(100),
  `twitter` varchar(80),
  `linkedin` varchar(100),
  `department` varchar(50),
  `occupation` varchar(75),
  `locked` datetime DEFAULT '1920-01-01 00:01:01',
  `locked_ip` varchar(25),
  `login_attempts` tinyint(1) DEFAULT '0',
  `status` tinyint(1) DEFAULT '1',
  `options` mediumtext,
  `created` datetime DEFAULT '1920-01-01 00:01:01',
  `last_updated` datetime DEFAULT '1920-01-01 00:01:01',
  `owner` mediumint(5),
  `static` tinyint(1) DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `permission_group` (`permission_group`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_staff_in` (
  `id` varchar(100),
  `salt` varchar(4),
  `masterlog` datetime DEFAULT '1920-01-01 00:01:01',
  `expires` datetime DEFAULT '1920-01-01 00:01:01',
  `complete` datetime DEFAULT '1920-01-01 00:01:01',
  `username` varchar(100),
  `remember` tinyint(1) DEFAULT '0',
  `ip` varchar(25),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_stats` (
  `key` varchar(70),
  `value` varchar(255),
  PRIMARY KEY  (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_subscriptions` (
  `id` varchar(22),
  `member_id` varchar(20),
  `member_type` enum('member','contact'),
  `order_id` varchar(14),
  `card_id` varchar(13) COMMENT 'ppSD_cart_billing',
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `last_renewed` DATETIME DEFAULT '1920-01-01 00:01:01',
  `next_renew` datetime DEFAULT '1920-01-01 00:01:01',
  `next_renew_keep` datetime DEFAULT '1920-01-01 00:01:01',
  `cancel_date` datetime DEFAULT '1920-01-01 00:01:01',
  `price` decimal(15,2),
  `retry` smallint(3),
  `product` varchar(35),
  `status` tinyint(1) DEFAULT '1',
  `advance_notice_sent` TINYINT( 1 ) DEFAULT '0',
  `in_trial` tinyint(1) DEFAULT '0',
  `trial_charge_number` smallint(3) COMMENT 'How many times trial period has been charged',
  `paypal` tinyint(1) DEFAULT '0' COMMENT '1 = PayPal Handles It',
  `paypal_id` varchar(20) COMMENT 'Subscription ID',
  `cancel_reason` varchar(75),
  `gateway` varchar(35),
  `notice_sent` TINYINT(1) NULL,
  `salt` varchar(45) COMMENT 'Used for no-login CC updates',
  `spawned_invoice` VARCHAR(35) NULL,
  PRIMARY KEY  (`id`),
  KEY `member_id` (`member_id`),
  KEY `paypal_id` (`paypal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_subscription_reattempts` (
  `fail_attempt` smallint(2),
  `timeframe` varchar(12),
  `penalty_percent` decimal(5,2),
  `penalty_fixed` decimal(7,2),
  `cancel` tinyint(1) DEFAULT '0',
  PRIMARY KEY  (`fail_attempt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_tags` (
  `id` int(9) auto_increment,
  `tag` varchar(35),
  `item_id` varchar(40),
  `item_type` TINYINT( 1 ) DEFAULT '0' COMMENT  '1 = Upload',
  `owner` mediumint(6),
  PRIMARY KEY  (`id`),
  KEY `item_id` (`item_id`),
  KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_tax_classes` (
  `id` smallint(3) auto_increment,
  `state` varchar(35),
  `country` varchar(35),
  `zips` MEDIUMTEXT,
  `percent_physical` decimal(5,3),
  `percent_digital` decimal(5,3),
  `name` varchar(50),
  `created` datetime DEFAULT '1920-01-01 00:01:01',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_temp` (
  `id` varchar(40),
  `data` longtext,
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Temporary information for previewing and other short-term ca'";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_templates` (
  `id` varchar(65),
  `path` varchar(150),
  `theme` varchar(25),
  `subtemplate` varchar(65),
  `title` varchar(100),
  `desc` varchar(255),
  `caller_tags` text,
  `order` smallint(3),
  `custom_header` varchar(65),
  `custom_footer` varchar(65),
  `custom_template` varchar(65),
  `type` tinyint(1) DEFAULT '3' COMMENT '1 = Header, 2 = Footer, 3 = Custom template, 0 = Template, 4 = page',
  `section` varchar(35),
  `content` mediumtext,
  `secure` tinyint(1) DEFAULT '0',
  `static` tinyint(1) DEFAULT '0',
  `owner` mediumint(5),
  `encrypt` TINYINT( 1 ) DEFAULT '0',
  `meta_title` VARCHAR( 65 ),
  `lang` VARCHAR( 3 ) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'en',
  PRIMARY KEY  (`id`),
  KEY `subtemplate` (`subtemplate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_templates_lang` (
  `up` int(9) auto_increment,
  `id` varchar(65),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `content` mediumtext,
  `lang` varchar(3),
  `meta_title` VARCHAR( 65 ),
  PRIMARY KEY  (`up`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_templates_email` (
  `template` varchar(35),
  `title` varchar(100),
  `desc` varchar(255),
  `subject` varchar(100),
  `to` varchar(125),
  `from` varchar(80),
  `cc` varchar(255),
  `bcc` varchar(255),
  `content` mediumtext,
  `format` tinyint(1) DEFAULT '1' COMMENT '1 = html / 0 = plain text',
  `status` tinyint(1) DEFAULT '1',
  `save` tinyint(1) DEFAULT '1',
  `track` tinyint(1) DEFAULT '1',
  `track_links` tinyint(1) DEFAULT '1',
  `caller_tags` text,
  `custom` tinyint(1) DEFAULT '1' COMMENT 'If this was custom created by the user.',
  `owner` mediumint(6),
  `public` tinyint(1) DEFAULT '1',
  `created` datetime DEFAULT '1920-01-01 00:01:01',
  `header_id` varchar(35),
  `footer_id` varchar(35),
  `static` tinyint(1) DEFAULT '0',
  `default_for` tinyint(1) DEFAULT '0' COMMENT '1 = email, 2 = targeted, 3 = scheduled',
  `theme` varchar(25),
  `type` enum('template','header','footer'),
  KEY `template` (`template`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_themes` (
  `id` varchar(25),
  `name` varchar(50),
  `description` text,
  `author` varchar(45),
  `author_url` varchar(255),
  `img_1` varchar(150),
  `img_2` varchar(150),
  `active` tinyint(1) DEFAULT '0',
  `type` enum('html','email','mobile'),
  `style` enum('Clean','Minimalist','Experimental','Corporate','Colorful','Dark','Other'),
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_tracking_activity` (
  `id` int(10) auto_increment,
  `track_id` varchar(32),
  `type` enum('order','member','contact','rsvp','invoice'),
  `act_id` varchar(35),
  `value` decimal(10,2),
  `campaign_id` varchar(11),
  `date` DATETIME DEFAULT '1920-01-01 00:01:01',
  PRIMARY KEY  (`id`),
  KEY `track_id` (`track_id`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_trash_bin` (
  `id` int(9) auto_increment,
  `act_id` varchar(35),
  `data` mediumtext,
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  PRIMARY KEY  (`id`),
  KEY `act_id` (`act_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_uploads` (
  `id` varchar(30),
  `item_id` varchar(35) COMMENT 'Matches either the ppSD_members id or ppSD_contacts id',
  `type` enum('member','contact','event','product','cart_category','digital_product','employee'),
  `filename` varchar(150),
  `name` varchar(50),
  `description` varchar(255),
  `date` datetime DEFAULT '1920-01-01 00:01:01',
  `downloaded` int(8),
  `label` varchar(25) COMMENT 'Optional label that can be sent from the hidden form.',
  `cp_only` tinyint(1) DEFAULT '0' COMMENT '1 = only visible on admin CP',
  `note_id` varchar(35),
  `owner` mediumint(9),
  `email_id` varchar(35),
  `byuser` tinyint(1) DEFAULT '0',
  PRIMARY KEY  (`id`),
  KEY `username` (`item_id`),
  KEY `label` (`label`),
  KEY `note_id` (`note_id`),
  KEY `owner` (`owner`),
  KEY `email_id` (`email_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_usage_logs` (
  `id` int(10) auto_increment,
  `start_date` datetime DEFAULT '1920-01-01 00:01:01',
  `end_date` datetime DEFAULT '1920-01-01 00:01:01',
  `username` varchar(150),
  `act_id` varchar(35),
  `type` enum('staff','user'),
  `success` tinyint(1) NOT NULL DEFAULT '1',
  `msg` varchar(255),
  `task` varchar(75),
  `ip` varchar(25),
  `session` varchar(30),
  PRIMARY KEY  (`id`),
  KEY `username` (`username`,`task`),
  KEY `session` (`session`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_widgets` (
  `id` varchar(75),
  `name` varchar(45),
  `type` enum('plugin','menu','html','code','upload_list'),
  `menu_type` enum('horizontal','vertical'),
  `content` longtext,
  `active` tinyint(1) DEFAULT '1',
  `add_class` varchar(50),
  `author` VARCHAR( 35 ) ,
  `author_url` VARCHAR( 120 ) ,
  `author_twitter` VARCHAR( 120 ) ,
  `version` VARCHAR( 6 ) ,
  `installed` DATETIME DEFAULT '1920-01-01 00:01:01',
  `original_creator` VARCHAR( 40 ),
  `original_creator_url` VARCHAR( 120 ) ,
  `description` TEXT,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$create[] = "CREATE TABLE IF NOT EXISTS `ppSD_widgets_menus` (
  `id` mediumint(6) auto_increment,
  `widget_id` varchar(25),
  `submenu` mediumint(6),
  `title` varchar(125),
  `link` varchar(255),
  `link_type` tinyint(1) DEFAULT '1' COMMENT '1 = cms page, 2 = full url, 3 = onsite build url',
  `link_target` enum('same','new'),
  `position` smallint(3),
  `content_id` INT( 9 ),
  PRIMARY KEY  (`id`),
  KEY `submenu` (`submenu`),
  KEY `widget_id` (`widget_id`),
  KEY `link` (`link`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";