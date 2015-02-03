-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 30, 2015 at 07:43 PM
-- Server version: 5.5.24-log
-- PHP Version: 5.3.13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `mycanvas`
--

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE IF NOT EXISTS `company` (
  `no` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_no` bigint(20) NOT NULL,
  `company_logo` text COLLATE utf8_unicode_ci NOT NULL,
  `company_name` text COLLATE utf8_unicode_ci NOT NULL,
  `reg_date` date NOT NULL,
  PRIMARY KEY (`no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`no`, `user_no`, `company_logo`, `company_name`, `reg_date`) VALUES
(1, 1, '54c8ab404f1b1.png', 'Sold Company', '2015-01-28'),
(2, 1, '54c8b09f64b3b.png', 'Construction Company', '2015-01-28'),
(3, 1, '54c8b0d222b28.png', 'Construction company', '2015-01-28'),
(4, 1, '54c8b7902b16c.jpg', 'Adidas', '2015-01-28'),
(5, 1, '54c8b9eccbd50.jpg', 'Leasing Company', '2015-01-28'),
(6, 1, '54c8e22bde818.png', 'Database Company', '2015-01-28');

-- --------------------------------------------------------

--
-- Table structure for table `pin`
--

CREATE TABLE IF NOT EXISTS `pin` (
  `no` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_no` bigint(20) NOT NULL,
  `pin_type` text COLLATE utf8_unicode_ci NOT NULL,
  `lat` text COLLATE utf8_unicode_ci NOT NULL,
  `lng` text COLLATE utf8_unicode_ci NOT NULL,
  `address` text COLLATE utf8_unicode_ci NOT NULL,
  `title` text COLLATE utf8_unicode_ci NOT NULL,
  `memo` text COLLATE utf8_unicode_ci NOT NULL,
  `favorite` tinyint(4) NOT NULL,
  `company_no` bigint(20) NOT NULL,
  `custom_field` text COLLATE utf8_unicode_ci NOT NULL,
  `pin_date` date NOT NULL,
  PRIMARY KEY (`no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=79 ;

--
-- Dumping data for table `pin`
--

INSERT INTO `pin` (`no`, `user_no`, `pin_type`, `lat`, `lng`, `address`, `title`, `memo`, `favorite`, `company_no`, `custom_field`, `pin_date`) VALUES
(1, 1, 'mark-0-1', '40.726835976477936', '-74.08321380615229', '2212 Summit Avenue, Union City, NJ 07087, USA', 'Summit Avenue', 'This is test of Summit Avenue\r\nAwesome!This is test of Summit Avenue\r\nAwesome!This is test of Summit Avenue\r\nAwesome!This is test of Summit Avenue\r\nAwesome!This is test of Summit Avenue\r\nAwesome!This is test of Summit Avenue\r\nAwesome!This is test of Summit Avenue\r\nAwesome!This is test of Summit Avenue\r\nAwesome!', 0, 1, '[{"name":"Job","content":"Freelancer"},{"name":"Duty","content":"gmap"}]', '2015-01-18'),
(2, 1, 'mark-0-4', '40.75194266223655', '-73.95872712135315', 'East Road, New York, NY 10044, USA', 'test2', 'this is test 2 memo', 0, 0, '', '2015-01-19'),
(4, 1, 'mark-0-7', '40.75020639104453', '-73.96089769899845', 'East Road, New York, NY 10044, USA', '', '', 0, 0, '', '0000-00-00'),
(5, 1, 'mark-0-6', '40.75019877126413', '-73.9608708769083', 'East Road, New York, NY 10044, USA', '', '', 0, 0, '', '0000-00-00'),
(6, 1, 'mark-0-3', '40.75729647225288', '-73.99472236633301', '413 West 39th Street, New York, NY 10018, USA', '413 West 39th', 'The office of Alica', 1, 6, '', '2015-01-01'),
(18, 1, 'mark-0-1', '40.9218144123785', '-73.94451141357428', '128-210 Pershing Avenue, Englewood Cliffs, NJ 07632, USA', '', '', 0, 0, '', '2015-01-27'),
(19, 1, 'mark-0-6', '40.76702162667874', '-74.2157363891601', '139-191 William Street, Newark, NJ 07102, USA', 'Newark', 'Newark', 1, 0, '', '0000-00-00'),
(27, 1, 'mark-0-0', '39.7386433566274', '-104.99007225036621', '3270 Moore Court, Wheat Ridge, CO 80033, USA', '', '', 0, 0, '', '0000-00-00'),
(28, 1, 'mark-0-7', '39.70665840507515', '-105.0893783569336', '8200-8334 West Virginia Avenue, Lakewood, CO 80226, USA', '', '', 0, 0, '', '0000-00-00'),
(29, 1, 'mark-0-2', '39.75708819537115', '-105.2219009399414', 'Clear Creek Trail, Golden, CO 80401, USA', '', '', 0, 0, '', '0000-00-00'),
(30, 1, 'mark-0-1', '40.75128026336245', '-73.95971953868866', 'East Road, New York, NY 10044, USA', 'test for marker cluster', 'ok\r\ngreat.\r\nThanks.', 0, 4, '', '2015-01-01'),
(31, 1, 'mark-0-7', '40.75080479507166', '-73.96034181118011', '1 FDR Drive, New York, NY 10044, USA', 'test', 'All test Right?', 0, 0, '', '0000-00-00'),
(33, 1, 'mark-0-0', '40.78808012819681', '-74.06845092773438', '31 Enterprise Avenue North, Secaucus, NJ 07094, USA', 'Retail Condo', 'https://www.w3schools.com/\r\nhttp://therealdeal.com/blog/2015/01/14/top-10-biggest-real-estate-projects-coming-to-nyc-3/#sthash.x22eEA29.dpuf\r\nAriel Property Advisors presents 123 Baxter Street, a portfolio of four (4) retail condo units situated in Little\r\nItaly between Hester Street and Canal Street. Together, the properties enjoy approximately 140 feet of\r\nprime retail frontage, with 70 feet on Baxter Street and 70 feet on Hester Street.\r\nUnits 1 and 3 are currently rented to Scottrade, an investment company with over 500 branch offices nationwide\r\nthat offers brokerage and banking services. Scottrade currently pays a below market rent of $95\r\nper square foot. This lease expires in January 2016 and includes a Tenant’s option for two (2) additional\r\nterms of three (3) years with rent being adjusted to full fair market value.\r\nUnit 2 is currently rented to Kumon North America, an on-site after-school math and reading program.\r\nKumon currently pays a below market rent of $93 per square foot, per a lease that expires in 2019, with\r\nno Tenant options.\r\nUnit 4 is currently vacant, presenting an ideal opportunity for either an owner-user or investor to capitalize\r\non a current market rents.\r\nThe properties are located just two blocks from the 6, J, Z, N and Q trains. Due to an increasing demand\r\nfor retail properties in well situated locations and the below market rents currently being paid by the\r\ntenants, this is an opportunity for a long term investor to own a portfolio of retail properties that offer\r\ntremendous upside potential.', 0, 0, '', '2015-01-19'),
(64, 1, 'mark-0-2', '40.81952545442639', '-73.92253875732422', '2950 Park Avenue, Bronx, NY 10451, USA', 'This is test', 'This is test for public InfoBubble', 0, 0, '', '2015-01-27'),
(65, 1, 'mark-0-5', '40.841865966890786', '-74.14020538330078', '437 Rutherford Boulevard, Clifton, NJ 07014, USA', 'Test of title', 'This is WuAk', 0, 1, '', '2015-01-28'),
(67, 1, 'mark-0-6', '40.87536262100473', '-74.0423583984375', '112 Hudson Street, Hackensack, NJ 07601, USA', '', '', 0, 3, '', '2015-01-28'),
(70, 1, 'mark-0-8', '40.86030420568381', '-73.9548110961914', 'Henry Hudson Drive, Fort Lee, NJ 07024, USA', '', '', 0, 4, '', '2015-01-28'),
(71, 1, 'mark-0-6', '40.846021510805194', '-74.23908233642578', '14 Laurel Court, Verona, NJ 07044, USA', '', '', 0, 5, '', '2015-01-28'),
(72, 1, 'mark-0-6', '40.7560997756636', '-73.85318756103516', 'Grand Central Parkway, Corona, NY 11368, USA', '', '', 0, 1, '', '2015-01-28'),
(73, 1, 'mark-0-5', '40.78574062435701', '-73.8339614868164', '14-14 135th Street, Flushing, NY 11356, USA', '', '', 0, 4, '', '2015-01-28'),
(78, 1, 'mark-0-1', '40.724884598773755', '-74.18415069580078', 'Test Address', 'Test for add custom field feature', 'This is test for add custom field feature.', 0, 1, '[{"name":"1","content":"1"},{"name":"2","content":"2"}]', '2015-01-30');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `no` bigint(20) NOT NULL AUTO_INCREMENT,
  `username` text COLLATE utf8_unicode_ci NOT NULL,
  `passwd` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`no`, `username`, `passwd`) VALUES
(1, 'test', 'test');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
