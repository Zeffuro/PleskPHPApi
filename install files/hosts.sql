-- phpMyAdmin SQL Dump
-- version 3.1.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 02, 2011 at 02:43 PM
-- Server version: 5.1.37
-- PHP Version: 5.2.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `plesk`
--

-- --------------------------------------------------------

--
-- Table structure for table `hosts`
--

CREATE TABLE IF NOT EXISTS `hosts` (
  `host_id` int(8) NOT NULL AUTO_INCREMENT,
  `host_address` varchar(255) NOT NULL,
  `host_ipaddress` varchar(255) NOT NULL,
  `host_port` int(5) NOT NULL,
  `host_path` varchar(255) NOT NULL,
  `host_user` varchar(128) DEFAULT NULL,
  `host_pass` varchar(255) DEFAULT NULL,
  `host_key` varchar(128) DEFAULT NULL,
  `host_authmethod` int(11) NOT NULL,
  `host_version` varchar(16) NOT NULL,
  PRIMARY KEY (`host_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=29 ;
