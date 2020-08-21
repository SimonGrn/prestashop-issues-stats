-- --------------------------------------------------------

--
-- Table structure for table `issue`
--

CREATE TABLE `issue` (
  `id` int(11) NOT NULL,
  `issue_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `state` varchar(20) NOT NULL,
  `milestone` varchar(50) NOT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `closed` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `issue_label`
--

CREATE TABLE `issue_label` (
  `issue_id` int(11) NOT NULL,
  `label_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `label`
--

CREATE TABLE `label` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(100) NOT NULL,
  `type_id` int(11) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `label`
--

INSERT INTO `label` (`id`, `name`, `description`, `type_id`) VALUES
(1, '1.7.7.x', '', 3),
(2, 'BO', 'Category: Back Office', 8),
(3, 'Bug', 'Type: Bug', 12),
(4, 'Fixed', 'resolution: Fixed', 10),
(5, 'Major', 'Severity: Major', 4),
(6, 'Must-have', '', 1),
(7, 'Order', 'Component: Which BO section is concerned', 6),
(8, 'QA âœ”ï¸', 'Status: QA-Approved', 9),
(9, 'Regression', 'Type: regression', 12),
(10, 'migration', 'symfony migration project', 14),
(11, '1.7.3.0', 'Affects versions', 2),
(12, '1.7.4.0', 'Affects versions', 2),
(13, '1.7.4.1', 'Affects versions', 2),
(14, 'Carriers', 'Label: Which BO under menu is concerned', 5),
(15, 'FO', 'Category: Front Office', 8),
(16, 'Front-end', 'Category: Front end', 8),
(17, 'Trivial', 'Severity: Trivial', 4),
(18, '1.7.6.0', 'Affects versions', 2),
(19, 'CLDR', '', 8),
(20, 'Currencies', 'Label: Which BO under menu is concerned', 5),
(21, 'International', 'Component: Which BO section is concerned', 6),
(22, 'Minor', 'Severity: Minor', 4),
(23, 'develop', 'Branch', 3),
(24, '1.7.7.0', '', 2),
(25, 'CO', 'Category: Core', 8),
(26, 'PR available', 'A PR has been done', 1),
(27, 'Delivery slip', 'Label: Which BO under menu is concerned', 5),
(28, '1.7.2.0', 'Affects versions', 2),
(29, '1.7.5.2', 'Affects versions', 2),
(30, 'Combinations', 'Type of product: With combinations', 13),
(31, 'Products', 'Label: Which BO under menu is concerned', 5),
(32, 'Addresses', 'Label: Which BO under menu is concerned', 5),
(33, 'Critical', 'Severity: Critical', 4),
(34, 'Detected by TE', 'Issue detected by TE', 1),
(35, 'Link widget', 'Label: Which BO under menu is concerned', 5),
(36, 'Pack', 'Type of product: Pack', 13),
(37, 'Employees', 'Label: Which BO under menu is concerned', 5),
(38, '1.7.6.3', 'Affects versions', 2),
(39, 'Customer settings', 'Label: Which BO under menu is concerned', 5),
(40, 'Shopping carts FO', 'All the issues related to the Front Office Shopping Cart', 6),
(41, 'Key feature', 'Notable feature to be highlighted', 1),
(42, 'Cart rules', 'Label: Which BO under menu is concerned', 5),
(43, '1.7.6.4', 'Affects versions', 2),
(44, 'Customized', 'Type of product: Customized', 13),
(45, 'Credit notes', 'Label: Which BO under menu is concerned', 5),
(46, 'Discounts', 'Label: Which BO under menu is concerned', 5),
(47, '1.7.6.1', 'Affects versions', 2),
(48, 'Order statuses', 'Label: Which BO under menu is concerned', 5),
(49, 'Workaround available', 'A viable workaround has been provided in the comments', 1),
(50, 'good first issue', 'Beginner-friendly issues', 1),
(51, '1.7.6.x', 'Branch', 3),
(52, 'Catalog price rules', 'Label: Which BO under menu is concerned', 5),
(53, 'Invoices', 'Label: Which BO under menu is concerned', 5),
(54, '1.7.6.2', 'Affects versions', 2),
(55, 'Faceted search', 'Module: ps_facetedsearch', 7),
(56, 'ps_shoppingcart', 'Module: ps_shoppingcart', 7),
(57, '1.7.6.7', 'Affects versions', 2),
(58, 'Email', 'Label: Which BO under menu is concerned', 5),
(59, 'Catalog', 'Component: Which BO section is concerned', 6),
(60, 'Categories', 'Label: Which BO under menu is concerned', 5),
(61, 'Multistore', 'Label: Which BO under menu is concerned', 5),
(62, '1.7.6.5', 'Affects versions', 2),
(63, 'Image', 'Label: Which BO under menu is concerned', 5),
(64, 'Autoupgrade', 'Module: autoupgrade', 7),
(65, '1.7.5.1', 'Affects versions', 2),
(66, 'Positions', 'Label: Which BO under menu is concerned', 5),
(67, 'Welcome', 'Module: welcome', 7),
(68, '1.7.2.2', 'Affects versions', 2),
(69, 'Checkout', '', 1),
(70, 'Countries', 'Label: Which BO under menu is concerned', 5),
(71, 'Suppliers', 'Label: Which BO under menu is concerned', 5),
(72, 'Taxes and Prices', 'Component: Which BO section is concerned', 6),
(73, 'WIP', 'Status: Work In Progress', 9),
(74, 'Monitoring', 'Label: Which BO under menu is concerned', 5),
(75, 'PM âœ”ï¸', 'Status: PM-approved', 9),
(76, 'Wording âœ”ï¸', 'Status: Wording-Approved', 9),
(77, '1.7.4.4', 'Affects versions', 2),
(78, '1.7.5.0', 'Affects versions', 2),
(79, '1.7.4.2', 'Affects versions', 2),
(80, '1.7.6.6', '', 2),
(81, 'Attributes', 'Label: Which BO under menu is concerned', 5),
(82, 'Features', 'Label: Which BO under menu is concerned', 5),
(83, 'Translations', 'Label: Which BO under menu is concerned', 5),
(84, 'Epic', '', 1),
(85, 'Modules & Themes', 'Component: Which BO section is concerned', 6),
(86, 'Gamification', 'Module: gamification', 7),
(87, 'Modules catalog', 'Label: Which BO under menu is concerned', 5),
(88, 'Customer', 'Component: Which BO section is concerned', 6),
(89, 'Email theme', 'Label: Which BO under menu is concerned', 5),
(90, 'IN', 'Category: Install', 8),
(91, 'MBO', 'Module: MBO', 7),
(92, 'Theme & logo', 'Label: Which BO under menu is concerned', 5),
(93, 'Topwatchers', 'When the issue is reported by more than 6 people', 1),
(94, 'Import / Export', 'Label: Which BO under menu is concerned', 5),
(95, 'Stats', 'Component: Which BO section is concerned', 6),
(96, 'Stocks', 'Label: Which BO under menu is concerned', 5),
(97, 'Search', 'Label: Which BO under menu is concerned', 5),
(98, 'Productcomments', 'Module: productcomments', 7),
(99, 'Modules', 'Component: Which BO section is concerned', 6),
(100, 'WS', 'Category: Web Service', 8),
(101, 'Module manager', 'Label: Which BO under menu is concerned', 5),
(102, 'Duplicate', 'Resolution: Duplicate', 10),
(103, 'waiting for dev', 'Status: Waiting for dev feedback', 9),
(104, 'Brands', 'Label: Which BO under menu is concerned', 5),
(105, 'statslive', '', 7),
(106, '1.7.4.3', 'Affects versions', 2),
(107, 'pscleaner', 'Module: pscleaner', 7),
(108, '1.6.1.0', 'Affects versions', 2),
(109, 'UX âœ”ï¸', 'Status: UX Approved', 9),
(110, 'Localization', 'Label: Which BO under menu is concerned', 5),
(111, 'Design', 'Component: Which BO section is concerned', 6),
(112, 'Theme custo', 'Module: ps_themecusto', 7),
(113, '1.7.x', 'Branch', 3),
(114, 'Theme catalog', 'Label: Which BO under menu is concerned', 5),
(115, 'Merchandise returns', 'Label: Which BO under menu is concerned', 5),
(116, 'Contactinfo', 'Module: contactinfo', 7),
(117, 'Administration', 'Label: Which BO under menu is concerned', 5),
(118, 'Advanced parameters', 'Component: Which BO section is concerned', 6),
(119, '1.7.2.4', 'Affects versions', 2),
(120, 'No change required', 'Resolution: No change required', 10),
(121, 'waiting for author', 'Status: Waiting for Author Feedback', 9),
(122, 'Maintenance', 'Label: Which BO under menu is concerned', 5),
(123, 'Shop parameters', 'Component: Which BO section is concerned', 6),
(124, 'Logs', 'Label: Which BO under menu is concerned', 5),
(125, 'Customer service', 'Component: Which BO section is concerned', 6),
(126, 'Can\'t Reproduce', 'Resolution: Can\'t Reproduce', 10),
(127, 'Permissions', 'Label: Which BO under menu is concerned', 5),
(128, 'Languages', 'Label: Which BO under menu is concerned', 5),
(129, 'High', 'Priority: High', 1),
(130, 'Team', 'Label: Which BO under menu is concerned', 5),
(131, 'Google sitemap', 'module: gsitemap', 7),
(132, 'SEO & URLs', 'Label: Which BO under menu is concerned', 5),
(133, 'Profiles', 'Label: Which BO under menu is concerned', 5),
(134, 'Brand list', 'Module: ps_brandlist', 7),
(135, 'Dashboard', 'Component: Which BO section is concerned', 6),
(136, 'Dashproducts', 'Module: dashproducts', 7),
(137, 'Files', 'Label: Which BO under menu is concerned', 5),
(138, 'GDPR', 'Module: psgdpr', 7),
(139, 'Virtual', 'Type of product: Virtual', 13),
(140, '1.7.2.1', 'Affects versions', 2),
(141, '1.7.0.0', 'Affects versions', 2),
(142, 'ps_customeraccountlinks', '', 7),
(143, 'Shipping', 'Component: Which BO section is concerned', 6),
(144, '1.7.2.3', 'Affects versions', 2),
(145, 'Emailsubscription', 'Module: ps_emailsubscription', 7),
(146, '1.7.1.2', 'Affects versions', 2),
(147, '1.7.3.1', 'Affects versions', 2),
(148, '1.7.5.x', 'Branch', 3),
(149, 'Performance', 'Label: Which BO under menu is concerned', 5),
(150, 'Pages', 'Label: Which BO under menu is concerned', 5),
(151, 'Contact', 'Label: Which BO under menu is concerned', 5),
(152, 'Nice to have', '', 4),
(153, 'Geolocation', 'Label: Which BO under menu is concerned', 5),
(154, 'Product settings', 'Label: Which BO under menu is concerned', 5),
(155, 'Blockreassurance', 'Module: blockreassurance', 7),
(156, 'Blockcart', 'Module: blockcart', 7),
(157, 'Bankwire', 'Module: bankwire', 7),
(158, '1.6.1.x', 'Branch', 3),
(159, '1.7.3.3', 'Affects versions', 2),
(160, 'Traffic & SEO', 'Label: Which BO under menu is concerned', 5),
(161, 'newproducts', 'Module: ps_newproducts', 7),
(162, 'Dashactivity', 'Module: Dashactivity', 7),
(163, 'Supplier list', 'Module: ps_supplierlist', 7),
(164, 'BC break', 'Type: Introduces a backwards-incompatible break', 12),
(165, 'Webservice', 'Label: Which BO under menu is concerned', 5),
(166, 'Payment methods', 'Label: Which BO under menu is concerned', 5),
(167, 'Category tree', 'Module: ps_categorytree', 7),
(168, 'ps_currencyselector', 'Module: ps_currencyselector', 7),
(169, 'Customtext', 'Module: ps_customtext', 7),
(170, 'Tax rules', 'Label: Which BO under menu is concerned', 5),
(171, 'Shipping preferences', 'Label: Which BO under menu is concerned', 5),
(172, 'Contactform', 'Module: contactform', 7),
(173, 'S', 'Difficulty: S', 11),
(174, 'Payment preferences', 'Label: Which BO under menu is concerned', 5),
(175, 'L', 'Difficulty: L', 11),
(176, 'Shopping carts', 'Label: Which BO under menu is concerned', 5),
(177, 'Automated', 'Automation: is automated', 1),
(178, 'VAT', 'Label: Which BO under menu is concerned', 5),
(179, 'Mainmenu', 'Module: ps_mainmenu', 7),
(180, 'psaddonsconnect', '', 7),
(181, 'Database', 'Label: Which BO under menu is concerned', 5),
(182, '1.7.3.4', 'Affects versions', 2),
(183, 'Blocknewsletter', 'Module: blocknewsletter', 7),
(184, 'Newsletter', 'Module: newsletter', 7),
(185, 'Customer groups', 'Label: Which BO under menu is concerned', 5),
(186, 'Legalcompliance', 'Module: ps_legalcompliance', 7),
(187, '1.7.4.x', 'Branch', 3),
(188, '1.7.3.2', 'Affects versions', 2),
(189, 'M', 'Difficulty: M', 11),
(190, 'Circuit Breaker', '', 1),
(191, 'Mondialrelay', 'Module: mondialrelay', 7),
(192, 'Dashtrends', 'Module: dashtrends', 7),
(193, '1.7.0.6', 'Affects versions', 2),
(194, '1.7.1.0', 'Affects versions', 2),
(195, 'SQL Manager', 'Label: Which BO under menu is concerned', 5),
(196, '1.6.1.22', 'Affects versions', 2),
(197, '1.7.2.5', 'Affects versions', 2),
(198, 'Developer Feature', 'Developer-oriented feature', 1),
(199, 'waiting for PM', 'Status: Waiting for PM feedback', 9),
(200, 'Bug fix', 'Type: Bug fix', 12),
(201, 'LO', 'Category: Localization', 8),
(202, '1.7.0.4', 'Affects versions', 2),
(203, '1.7.0.5', 'Affects versions', 2),
(204, 'Viewed products', 'Module: ps_viewedproduct', 7),
(205, 'Vatnumber', 'Module: vatnumber', 7),
(206, 'Feature', 'Type: New Feature', 12),
(207, '1.6.1.23', 'Affects versions', 2),
(208, 'TBS', 'Status: To Be Specified', 9),
(209, 'To Do', 'Status: To do', 9),
(210, 'States', 'Label: Which BO under menu is concerned', 5),
(211, 'Won\'t Fix', 'Resolution: Won\'t Fix', 10),
(212, '1.7.1.1', 'Affects versions', 2),
(213, 'Mailalerts', 'Module: mailalert', 7),
(214, '1.6.1.24', 'Affects versions', 2),
(215, 'Payment', 'Component: Which BO section is concerned', 6),
(216, 'Customer reminder', 'Module: ps_reminder', 7),
(217, 'TBR', 'Status: To Be Reproduced', 9),
(218, 'waiting for wording', 'Status: Waiting for wording', 9),
(219, 'Dashgoals', 'Module: dashgoals', 7),
(220, 'Follow up', 'Module: followup', 7),
(221, 'Cross selling', 'Module: ps_crossselling', 7),
(222, 'Imageslider', 'Module: ps_imageslider', 7),
(223, 'Referrers', 'Label: Which BO under menu is concerned', 5),
(224, 'B2B', '', 1),
(225, '1.6.1.1', 'Affects versions', 2),
(226, '1.6.1.21', 'Affects versions', 2),
(227, 'gapi', 'Module: Google analytic API', 7),
(228, 'Documentation', '', 1),
(229, 'Category products', 'Module: ps_categoryproducts', 7),
(230, 'waiting for UX', 'Status: Waiting for UX feedback', 9),
(231, 'Dataprivacy', 'Module: ps_dataprivacy', 7),
(232, '1.6.1.20', 'Affects versions', 2),
(233, 'Quickwin', 'Type: Quickwin', 12),
(234, 'Socialsharing', 'Module: socialsharing', 7),
(235, 'Stores', 'Label: Which BO under menu is concerned', 5),
(236, 'Google Analytics', 'Module: ps_googleanalytics', 7),
(237, 'Wire payment', 'Module: ps_wirepayment', 7),
(238, 'Carriercompare', 'Module: carriercompare', 7),
(239, '1.6.1.19', 'Affects versions', 2),
(240, 'General', 'Label: Which BO under menu is concerned', 5),
(241, 'Improvement', 'Type: Improvement', 12),
(242, '1.6.1.18', 'Affects versions', 2),
(243, 'waiting for QA', 'Status: Waiting for QA feedback', 9),
(244, '1.7.0.3', 'Affects versions', 2),
(245, 'Advancedeucompliance', 'Module: advancedeucompliance', 7),
(246, 'cronjobs', 'Module: cronjobs', 7),
(247, 'statsproduct', '', 7),
(248, 'TE', 'Category: Tests', 8),
(249, '1.6.1.8', 'Affects versions', 2),
(250, 'Socialfollow', 'Module: ps_socialfollow', 7),
(251, 'Back-end', '', 1),
(252, '1.6.1.6', 'Affects versions', 2),
(253, 'Order settings', 'Label: Which BO under menu is concerned', 5),
(254, 'Favicon notification BO', 'Module: ps_faviconnotificationbo', 7),
(255, 'Missing basic requested informations', 'Resolution: basic informations requested to reproduce the issue are missing', 10),
(256, 'Customer titles', 'Label: Which BO under menu is concerned', 5),
(257, '1.6.1.13', 'Affects versions', 2),
(258, 'Order messages', 'Label: Which BO under menu is concerned', 5),
(259, 'Manual Upgrade', '', 1),
(260, 'DB Backup', 'Label: Which BO under menu is concerned', 5),
(261, 'Watermark', 'Module: Watermark', 7),
(262, 'Advertising block', 'Module: ps_advertising', 7),
(263, 'Zones', 'Label: Which BO under menu is concerned', 5),
(264, 'Broader topic', '', 1),
(265, 'Featured products', 'Module: ps_featuredproducts', 7),
(266, 'Best vouchers', 'Module: statsbestvouchers', 7),
(267, '< 1.6', 'Affects versions', 2),
(268, 'Best Sellers', 'Module: ps_bestsellers', 7),
(269, 'Responsive', '', 1),
(270, 'NMI', 'Status: Need More Info', 9),
(271, 'Hook', '', 1),
(272, 'ps_customersignin', '', 7),
(273, 'ps_checkpayment', 'Module: ps_checkpayment', 7);

-- --------------------------------------------------------

--
-- Table structure for table `type`
--

CREATE TABLE `type` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `type`
--

INSERT INTO `type` (`id`, `name`) VALUES
(1, 'None'),
(2, 'Version'),
(3, 'Branch'),
(4, 'Severity'),
(5, 'Page'),
(6, 'Section'),
(7, 'Module'),
(8, 'Category'),
(9, 'Status'),
(10, 'Resolution'),
(11, 'Difficulty'),
(12, 'Type'),
(13, 'Type of product'),
(14, 'Migration');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `issue`
--
ALTER TABLE `issue`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `label`
--
ALTER TABLE `label`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `type`
--
ALTER TABLE `type`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `label`
--
ALTER TABLE `label`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=274;
--
-- AUTO_INCREMENT for table `type`
--
ALTER TABLE `type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
