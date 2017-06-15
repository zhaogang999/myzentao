-- phpMyAdmin SQL Dump
-- version 4.0.8
-- http://www.phpmyadmin.net
--
-- 主机: localhost:3306
-- 生成日期: 2017-05-03 16:27:07
-- 服务器版本: 5.5.32
-- PHP 版本: 5.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `zentao`
--

-- --------------------------------------------------------

--
-- 表的结构 `zt_qaaudit`
--

CREATE TABLE IF NOT EXISTS `zt_qaaudit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `auditID` varchar(20) NOT NULL COMMENT '编号',
  `noDec` varchar(255) NOT NULL COMMENT '不符合项描述',
  `task` int(10) unsigned NOT NULL COMMENT '任务id',
  `noType` varchar(10) NOT NULL COMMENT '不符合类型',
  `serious` varchar(20) NOT NULL COMMENT '严重度',
  `cause` varchar(255) NOT NULL COMMENT '原因分析',
  `measures` varchar(255) NOT NULL COMMENT '任务描述',
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=91 ;

-- --------------------------------------------------------

--
-- 表的结构 `zt_review`
--

CREATE TABLE IF NOT EXISTS `zt_review` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '评审id',
  `fileNO` varchar(60) NOT NULL COMMENT '文件编号',
  `recorder` varchar(20) NOT NULL COMMENT '记录人员',
  `reviewName` varchar(30) NOT NULL COMMENT '评审类型',
  `task` int(10) unsigned NOT NULL,
  `doc` varchar(45) NOT NULL,
  `referenceDoc` varchar(45) NOT NULL COMMENT '参考文档',
  `reference` varchar(60) NOT NULL COMMENT '文件版本',
  `pages` tinyint(4) unsigned NOT NULL COMMENT '文件页数',
  `reviewers` varchar(200) NOT NULL COMMENT '参评人员',
  `reviewDate` date NOT NULL COMMENT '评审日期',
  `reviewScope` varchar(255) NOT NULL COMMENT '评审范围',
  `reviewPlace` varchar(60) NOT NULL COMMENT '评审地点',
  `effort` smallint(5) unsigned NOT NULL COMMENT '评审所用时间',
  `conclusion` varchar(15) NOT NULL COMMENT '评审结论',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=37 ;

-- --------------------------------------------------------

--
-- 表的结构 `zt_reviewdetail`
--

CREATE TABLE IF NOT EXISTS `zt_reviewdetail` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reviewID` int(10) unsigned NOT NULL,
  `number` varchar(3) NOT NULL COMMENT '序号',
  `reviewer` varchar(45) NOT NULL COMMENT '评审人员',
  `item` varchar(10) NOT NULL COMMENT '页码或章节',
  `line` varchar(10) NOT NULL COMMENT '行号或单元格',
  `severity` varchar(3) NOT NULL COMMENT '严重性',
  `description` varchar(255) NOT NULL COMMENT '评审描述',
  `proposal` varchar(255) NOT NULL COMMENT '评审建议',
  `changed` varchar(3) NOT NULL COMMENT '评审结果（是否变更）',
  `action` varchar(255) NOT NULL COMMENT '不变更原因',
  `chkd` varchar(3) NOT NULL COMMENT '评审结果确认',
  `deleted` enum('1','0') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
