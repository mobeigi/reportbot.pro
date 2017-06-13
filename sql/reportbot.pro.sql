-- ReportBot.pro Setup Script

DROP DATABASE IF EXISTS reportbotpro;
CREATE DATABASE reportbotpro CHARACTER SET utf8 COLLATE utf8_general_ci;

USE reportbotpro;

DROP TABLE IF EXISTS reportlog;
CREATE TABLE reportlog
(
  id INT NOT NULL AUTO_INCREMENT,
  steamid64 BIGINT(17) NOT NULL, -- steamid/community id in steamid64 format (17 integers)
  timereported INT(11) NOT NULL, -- epoch timestamp
  ipaddress VARCHAR(40), -- ip address of person who did report (ipv4 or ipv6)
  password VARCHAR(64), -- password used by person who did report
  PRIMARY KEY (id)
);