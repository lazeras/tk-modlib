
-- --------------------------------------------------------
--
-- Table structure for table `mailLog`
--
DROP TABLE IF EXISTS `mailLog`;
CREATE TABLE IF NOT EXISTS `mailLog` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sent` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0 = failed, 1 = sent',

  `to`   TEXT,
  `from` TEXT,
  `cc`   TEXT,
  `bcc`  TEXT,

  `subject` TEXT,
  `body`    TEXT,

  `notes` TEXT,
  `del` TINYINT(1) NOT NULL DEFAULT 0,
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY (`created`)
) ENGINE=InnoDB;


-- --------------------------------------------------------
--
-- Table structure for table `mailTemplate`
--
DROP TABLE IF EXISTS `mailTemplate`;
CREATE TABLE IF NOT EXISTS `mailTemplate` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `oid` INT UNSIGNED NOT NULL DEFAULT 0,
  `own` VARCHAR(128) NOT NULL DEFAULT '',

  `name` VARCHAR(128) NOT NULL DEFAULT '',
  `description` TEXT NOT NULL,
  `params` TEXT NOT NULL,

  `template` TEXT NOT NULL,
  `default` TEXT NOT NULL,

  `del` TINYINT(1) NOT NULL DEFAULT 0,
  `modified` DATETIME NOT NULL,
  `created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY (`oid`),
  KEY (`own`)
) ENGINE = InnoDB;

 --
-- Dumping data for table `mailTemplate`
--

-- INSERT INTO `mailTemplate` (`id`, `oid`, `own`, `name`, `description`, `params`, `template`, `default`, `del`, `modified`, `created`) VALUES
-- (1, 'body', 'Main Template', 'This template will be the outer template for all emails. Be sure to use the param tag {content} in the place where you want the content of the email to go.', '', '<html>\n<head>\n  <title>Email</title>\n<style type="text/css">\nbody {\n  font-family: arial,sans-serif;\n  font-size: 80%;\n  padding: 5px;\n  background-color: #FFF;\n}\n.content {\n  padding: 20px 0px 20px 20px;\n}\np {\n  margin: 0px 0px 10px 0px;\n  padding: 0px;\n}\n</style>\n</head>\n<body>\n  <h2>{subject}</h2>\n  \n  <div class="content">\n    \n    {content}\n    \n    <p>Kind Regards,</p>\n    <blockquote>\n      {publicName}\n    </blockquote>\n  \n  </div>\n  \n  <!--  Email Footer  -->\n  <p>\n    <strong>Faculty of Veterinary Science</strong><br/>\n    University of Melbourne<br/>\n    250 Princes Hwy<br/>\n    Werribee  Vic.  3030\n  </p>\n  <p>\n    T: 03 9731 2390  l  F: 03 9731 2366 l  M: 0418 107 778\n  </p>\n  <p><a href="http://www.vet.unimelb.edu.au/">vet.unimelb.edu.au</a></p>\n  <p>\n    <img alt="logo" src="{themeUrl}/images/mailFooter.jpg" />\n  </p>\n  \n</body>\n</html>', '<html>\n<head>\n  <title>Email</title>\n<style type="text/css">\nbody {\n  font-family: arial,sans-serif;\n  font-size: 80%;\n  padding: 5px;\n  background-color: #FFF;\n}\n.content {\n  padding: 0px 0px 0px 20px;\n}\np {\n  margin: 0px 0px 10px 0px;\n  padding: 0px;\n}\n</style>\n</head>\n<body>\n  <h2>{subject}</h2>\n  \n  <div class="content">{content}</div>\n  <p>&#160;</p>\n  \n  <hr />\n  <div class="footer">\n    <p>\n	  <i>Page:</i> {requestUri}<br/>\n	  <i>Referer:</i> <span>{refererUri}</span><br/>\n	  <i>IP Address:</i> <span>{remoteIp}</span><br/>\n	  <i>User Agent:</i> <span>{userAgent}</span>\n    </p>\n  </div>\n</body>\n</html>', 0, NOW(), NOW());




