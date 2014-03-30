DROP DATABASE IF EXISTS `hack`;
CREATE DATABASE `hack` DEFAULT CHARSET utf8;

use `hack`;

GRANT ALL
ON `hack`.*
TO `marik`@localhost IDENTIFIED BY 'marik107';

CREATE TABLE IF NOT EXISTS `images` (
   `id`         INT(11)    NOT NULL AUTO_INCREMENT,
   `is_resized` TINYINT(4) NOT NULL DEFAULT 0,
   PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `floors` (
  `id`         INT(11)    NOT NULL AUTO_INCREMENT,
  `number`     INT(11)    NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `place_types` (
  `id`            INT(11)         NOT NULL AUTO_INCREMENT,
  `type_name`     VARCHAR(100)    NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `hostels` (
  `id`            INT(11)         NOT NULL AUTO_INCREMENT,
  `number`        VARCHAR(100)    NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `places` (
   `id`            INT(11)       NOT NULL AUTO_INCREMENT,
   `number`        VARCHAR(100)  NOT NULL,
   `polygon`       VARCHAR(500)  NOT NULL,
   `place_type`    INT DEFAULT   NULL,
   `floor`         INT DEFAULT   NULL,
   `hostel`        INT DEFAULT   NULL,
   `last_update`   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`),
   FOREIGN KEY (`place_type`) REFERENCES `place_types` (`id`) ON DELETE CASCADE,
   FOREIGN KEY (`floor`)      REFERENCES `floors` (`id`)      ON DELETE CASCADE,
   FOREIGN KEY (`hostel`)     REFERENCES `hostels` (`id`)     ON DELETE CASCADE,
   UNIQUE KEY(`number`)
);

CREATE TABLE IF NOT EXISTS `users` (
   `id`            INT(11)      NOT NULL AUTO_INCREMENT,
   `login`         VARCHAR(70)  NOT NULL,
   `room`          INT          NOT NULL,
   `name`          VARCHAR(70)  NOT NULL,
   `surname`       VARCHAR(70),
   `phone`         VARCHAR(30),
   `description`   TEXT,
   `password`      VARCHAR(80)  NOT NULL,
   `salt`          VARCHAR(8)   NOT NULL,
   `photo_id`      INT DEFAULT  NULL,
   `register_date` DATETIME     NOT NULL,
   `last_update`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   `profile_views` INT DEFAULT 0,
   PRIMARY KEY (`id`),
   FOREIGN KEY (`photo_id`) REFERENCES `images` (`id`) ON DELETE SET NULL,
   UNIQUE KEY(`login`)
);

CREATE TABLE IF NOT EXISTS `event_types` (
  `id`        INT(11)      NOT NULL AUTO_INCREMENT,
  `type_name` VARCHAR(200) NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `events` (
   `id`               INT          NOT NULL AUTO_INCREMENT,
   `header`           VARCHAR(100) NOT NULL,
   `owner_id`         INT          NOT NULL,
   `place_id`         INT          NOT NULL,
   `event_type`       INT          NOT NULL,
   `creation_date`    DATETIME     NOT NULL,
   `description`      TEXT,
   `due_date`         DATETIME,
   `updated_date`     TIMESTAMP,
   `deletion_date`    DATETIME DEFAULT NULL,
   PRIMARY KEY (`id`),
   FOREIGN KEY (`owner_id`)   REFERENCES `users` (`id`)       ON DELETE CASCADE,
   FOREIGN KEY (`place_id`)   REFERENCES `places` (`id`)      ON DELETE CASCADE,
   FOREIGN KEY (`event_type`) REFERENCES `event_types` (`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `sessions` (
   `id`      INT         NOT NULL AUTO_INCREMENT,
   `user_id` INT         NOT NULL,
   `sid`     VARCHAR(40) NOT NULL,
   PRIMARY KEY(`id`),
   UNIQUE KEY(`sid`),
   FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `admin` (
   `id`       INT         NOT NULL AUTO_INCREMENT,
   `login`    VARCHAR(50) NOT NULL,
   `pass_md5` VARCHAR(50) NOT NULL,
   PRIMARY KEY (`id`)
);

DELIMITER //

DROP TRIGGER IF EXISTS `update_admin`//
CREATE TRIGGER `update_admin` BEFORE UPDATE ON `admin`
FOR EACH ROW BEGIN
   IF new.pass_md5 != old.pass_md5 THEN
      SET new.pass_md5 = MD5(new.pass_md5);
   END IF;
END//

DROP FUNCTION IF EXISTS `create_encrypted_pass` //
CREATE FUNCTION `create_encrypted_pass`(pass VARCHAR(80), salt VARCHAR(8))
RETURNS VARCHAR(80)
BEGIN
   RETURN MD5(CONCAT(salt, MD5(CONCAT(pass, salt)), SUBSTR(salt, 2)));
END//

DROP TRIGGER IF EXISTS `update_users`//
CREATE TRIGGER `update_users` BEFORE UPDATE ON `users`
FOR EACH ROW BEGIN
   IF old.password <> new.password THEN
      SET new.password = create_encrypted_pass(new.password, new.salt);
   END IF;
   IF old.profile_views = new.profile_views THEN
      SET new.last_update = CURRENT_TIMESTAMP;
   END IF;
END//

DROP TRIGGER IF EXISTS `insert_users`//
CREATE TRIGGER `insert_users` BEFORE INSERT ON `users`
FOR EACH ROW BEGIN
   SET new.password = create_encrypted_pass(new.password, new.salt);
END//

DROP PROCEDURE IF EXISTS `add_user_session` //
CREATE PROCEDURE `add_user_session`(IN uuser_id INT, IN sid VARCHAR(40))
BEGIN
   DELETE FROM `sessions` WHERE `user_id` = uuser_id;
   INSERT INTO `sessions`(`user_id`, `sid`) VALUES(uuser_id, sid);
END//

DROP FUNCTION IF EXISTS `get_user_ad_amount_by_id` //
CREATE FUNCTION `get_user_ad_amount_by_id`(user_id INT)
RETURNS INT
BEGIN
   DECLARE amount INT;
   SELECT COUNT(`id`) INTO amount FROM `events` WHERE `owner_id` = user_id;
   RETURN IFNULL(amount, 0);
END//

DROP FUNCTION IF EXISTS `process_event` //
CREATE FUNCTION `process_event`(
   ssid VARCHAR(40), act_type INT(2), eid INT, header VARCHAR(100), place_id INT, event_type INT, description TEXT, due_date DATETIME
)
RETURNS TINYINT(1)
BEGIN
   DECLARE uuser_id INT;
   DECLARE result INT;
   SELECT `user_id` INTO uuser_id FROM `sessions` WHERE `sid` = ssid;
   SET uuser_id = IFNULL(uuser_id, 0);
   IF uuser_id > 0  THEN
      SET result = 1;
      IF act_type = 0 THEN
         UPDATE `events` SET `deletion_date` = CURRENT_TIMESTAMP WHERE `id` = eid;
      ELSEIF act_type = 1 THEN
         UPDATE `events` SET
            `header` = header,
            `owner_id` = uuser_id,
            `place_id` = place_id,
            `event_type` = event_type,
            `description` = description,
            `due_date` = due_date
         WHERE `id` = eid;
      ELSEIF act_type = 2 THEN
         IF event_type = 2 THEN
            SET due_date = IFNULL(CURRENT_TIMESTAMP(), due_date);
         END IF;
         INSERT INTO `events`(`header`, `owner_id`, `place_id`, `event_type`, `description`, `due_date`, `creation_date`) VALUES
            (header, uuser_id, place_id, event_type, description, due_date, CURRENT_TIMESTAMP);
         SELECT LAST_INSERT_ID() FROM `events` LIMIT 1 into result;
      ELSE
         SET result = 0;
      END IF;
   ELSE
      SET result = 0;
   END IF;
   RETURN result;
END//

DROP PROCEDURE IF EXISTS `update_user_views` //
CREATE PROCEDURE `update_user_views`(IN uuser_id INT)
BEGIN
   UPDATE `users` SET `profile_views` = `profile_views` + 1 WHERE `id` = uuser_id;
END//

DELIMITER ;

INSERT INTO `admin`(`login`, `pass_md5`) VALUES('admin', '21232f297a57a5a743894a0e4a801fc3');

INSERT INTO `hostels`(`number`) VALUES
   ('8.1');

INSERT INTO `floors`(`number`) VALUES
   ('1'),
   ('2'),
   ('3'),
   ('4'),
   ('5'),
   ('6'),
   ('7'),
  ('8');

INSERT INTO `place_types`(`type_name`) VALUES
   ('Комната'),
   ('Тех. Помещение'),
   ('Холл');

INSERT INTO `event_types`(`type_name`) VALUES
   ('Услуги'),
   ('Мероприятия'),
   ('Досуг');

INSERT INTO `places`(`number`, `polygon`, `place_type`, `floor`, `hostel`) VALUES
   (501, '636,2032,636,1907,1010,1908,1008,2027', 1, 5, 1),
   (503, '636,1904,1009,1905,1010,1795,631,1800', 1, 5, 1),
   (505, '636,1797,1010,1793,1009,1678,636,1677', 1, 5, 1),
   (507, '634,1674,1008,1676,1008,1557,638,1555', 1, 5, 1),
   (509, '634,1552,1013,1554,1009,1438,636,1438', 1, 5, 1),
   (511, '634,1434,1011,1434,1010,1321,633,1323', 1, 5, 1),
   (515, '632,1318,1011,1318,1009,1198,633,1197', 1, 5, 1),
   (502, '179,2031,559,2032,553,1900,182,1907', 1, 5, 1),
   (504, '184,1899,555,1895,557,1796,178,1797', 1, 5, 1),
   (506, '177,1792,557,1792,556,1676,178,1672', 1, 5, 1),
   (508, '179,1556,558,1557,554,1438,180,1440', 1, 5, 1),
   (510, '180,1435,557,1433,555,1319,179,1318', 1, 5, 1),
   (512, '180,1314,559,1317,553,1196,182,1201', 1, 5, 1),
   (514, '177,1197,558,1195,553,1076,183,1077', 1, 5, 1),
   (516, '182,1071,555,1073,553,957,182,957', 1, 5, 1),
   (518, '180,952,556,955,555,831,180,835', 1, 5, 1),
   (519, '177,829,556,830,554,711,174,713', 1, 5, 1),
   (520, '816,445,942,446,938,71,819,73', 1, 5, 1),
   (521, '944,449,1055,449,1046,68,943,68', 1, 5, 1),
   (522, '1057,447,1177,445,1175,69,1056,70', 1, 5, 1),
   (523, '1179,446,1289,444,1283,70,1181,68', 1, 5, 1),
   (524, '1292,445,1406,442,1406,73,1289,69', 1, 5, 1),
   (526, '1409,447,1523,443,1522,71,1410,72', 1, 5, 1),
   (528, '1529,442,1647,445,1643,68,1530,67', 1, 5, 1),
   (530, '1652,445,1766,446,1766,71,1646,71', 1, 5, 1),
   (532, '1768,446,1884,448,1886,71,1769,69', 1, 5, 1),
   (534, '1890,444,2008,443,2006,66,1891,70', 1, 5, 1),
   (525, '1287,904,1412,904,1412,529,1290,529', 1, 5, 1),
   (527, '1416,906,1525,908,1526,529,1414,525', 1, 5, 1),
   (529, '1530,906,1646,907,1646,528,1530,528', 1, 5, 1),
   (531, '1652,904,1768,904,1770,530,1654,528', 1, 5, 1),
   (533, '1772,907,1888,908,1891,530,1772,523', 1, 5, 1),
   (535, '1893,527,2010,524,2010,903,1893,901', 1, 5, 1),
   (537, '2130,903,2248,903,2250,528,2133,529', 1, 5, 1),
   (539, '2250,902,2373,910,2374,533,2253,526', 1, 5, 1),
   (541, '2377,904,2493,902,2493,527,2378,529', 1, 5, 1),
   (543, '2496,905,2611,906,2612,526,2496,530', 1, 5, 1),
   (545, '2614,904,2733,905,2733,530,2616,528', 1, 5, 1),
   (547, '2736,905,2855,903,2855,528,2738,527', 1, 5, 1),
   (536, '2130,448,2259,445,2250,66,2129,70', 1, 5, 1),
   (538, '2260,446,2375,445,2374,70,2255,74', 1, 5, 1),
   (540, '2377,445,2496,447,2491,70,2379,71', 1, 5, 1),
   (542, '2499,446,2615,444,2616,67,2496,70', 1, 5, 1),
   (544, '2617,448,2733,446,2732,72,2620,68', 1, 5, 1),
   (546, '2737,450,2856,448,2853,71,2739,73', 1, 5, 1),
   (548, '2859,447,2968,448,2963,70,2855,68', 1, 5, 1),
   (550, '2972,450,3092,448,3090,67,2970,69', 1, 5, 1),
   (551, '3095,447,3200,445,3201,73,3093,72', 1, 5, 1),
   (552, '3205,451,3325,447,3327,73,3206,69', 1, 5, 1),
   (553, '3583,709,3963,714,3960,833,3584,838', 1, 5, 1),
   (554, '3581,840,3964,836,3963,955,3584,955', 1, 5, 1),
   (555, '3579,960,3962,961,3961,1072,3586,1075', 1, 5, 1),
   (556, '3584,1079,3965,1078,3961,1198,3587,1198', 1, 5, 1),
   (558, '3586,1203,3965,1201,3965,1320,3585,1319', 1, 5, 1),
   (560, '3585,1323,3960,1324,3956,1432,3588,1438', 1, 5, 1),
   (562, '3586,1444,3960,1440,3960,1553,3586,1553', 1, 5, 1),
   (566, '3586,1677,3964,1679,3967,1795,3583,1796', 1, 5, 1),
   (568, '3581,1801,3968,1800,3964,1907,3586,1906', 1, 5, 1),
   (570, '3582,1913,3966,1911,3964,2036,3581,2031', 1, 5, 1),
   (569, '3127,2032,3504,2029,3506,1906,3127,1905', 1, 5, 1),
   (567, '3125,1903,3512,1902,3509,1798,3128,1797', 1, 5, 1),
   (565, '3128,1793,3509,1792,3508,1678,3125,1672', 1, 5, 1),
   (564, '3122,1667,3508,1673,3505,1554,3126,1553', 1, 5, 1),
   (563, '3128,1549,3507,1550,3504,1434,3132,1439', 1, 5, 1),
   (561, '3131,1437,3506,1431,3504,1320,3130,1320', 1, 5, 1),
   (559, '3128,1314,3510,1317,3506,1190,3128,1193', 1, 5, 1);