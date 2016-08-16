-- phpMyAdmin SQL Dump
-- version 4.3.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 15, 2016 at 09:29 PM
-- Server version: 5.5.42-37.1-log
-- PHP Version: 5.4.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `devninni_af_sale`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE IF NOT EXISTS `category` (
  `cate_id` int(11) NOT NULL,
  `topcate_id` int(11) NOT NULL,
  `cate_name` varchar(128) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=148 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `image`
--

CREATE TABLE IF NOT EXISTS `image` (
  `img_id` int(11) NOT NULL,
  `img_url` varchar(256) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE IF NOT EXISTS `product` (
  `prod_id` int(11) NOT NULL,
  `cate_id` int(11) NOT NULL,
  `last_ss_id` int(11) NOT NULL,
  `ss_count` int(11) NOT NULL,
  `lowest` int(11) NOT NULL,
  `highest` int(11) NOT NULL,
  `prod_name` varchar(128) NOT NULL,
  `prod_url` varchar(256) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `search_index`
--

CREATE TABLE IF NOT EXISTS `search_index` (
  `skw_id` int(11) NOT NULL,
  `prod_id` int(11) NOT NULL,
  `weight` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `search_keyword`
--

CREATE TABLE IF NOT EXISTS `search_keyword` (
  `skw_id` int(11) NOT NULL,
  `skw_word` varchar(32) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=2198 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `snapshot`
--

CREATE TABLE IF NOT EXISTS `snapshot` (
  `ss_id` int(10) NOT NULL,
  `ts` int(10) unsigned NOT NULL,
  `data` blob NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=106000 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `snapshot_detail`
--

CREATE TABLE IF NOT EXISTS `snapshot_detail` (
  `ssd_id` int(10) NOT NULL,
  `prod_id` int(10) NOT NULL,
  `cate_id` int(11) NOT NULL,
  `img_id` int(11) NOT NULL,
  `ss_id` int(11) NOT NULL,
  `start_ts` int(10) unsigned NOT NULL,
  `end_ts` int(10) unsigned NOT NULL,
  `sale_price` int(11) NOT NULL,
  `regular_price` int(11) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=3335653 DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`cate_id`);

--
-- Indexes for table `image`
--
ALTER TABLE `image`
  ADD PRIMARY KEY (`img_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`prod_id`);

--
-- Indexes for table `search_index`
--
ALTER TABLE `search_index`
  ADD KEY `skw_id` (`skw_id`);

--
-- Indexes for table `search_keyword`
--
ALTER TABLE `search_keyword`
  ADD PRIMARY KEY (`skw_id`), ADD UNIQUE KEY `skw_word` (`skw_word`);

--
-- Indexes for table `snapshot`
--
ALTER TABLE `snapshot`
  ADD PRIMARY KEY (`ss_id`);

--
-- Indexes for table `snapshot_detail`
--
ALTER TABLE `snapshot_detail`
  ADD PRIMARY KEY (`ssd_id`), ADD KEY `prod_id` (`prod_id`), ADD KEY `start_ts` (`start_ts`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `cate_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=148;
--
-- AUTO_INCREMENT for table `search_keyword`
--
ALTER TABLE `search_keyword`
  MODIFY `skw_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2198;
--
-- AUTO_INCREMENT for table `snapshot`
--
ALTER TABLE `snapshot`
  MODIFY `ss_id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=106000;
--
-- AUTO_INCREMENT for table `snapshot_detail`
--
ALTER TABLE `snapshot_detail`
  MODIFY `ssd_id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3335653;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
