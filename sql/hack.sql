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
   `id`            INT          NOT NULL AUTO_INCREMENT,
   `header`        VARCHAR(100) NOT NULL,
   `owner_id`      INT          NOT NULL,
   `place_id`      INT          NOT NULL,
   `event_type`    INT          NOT NULL,
   `creation_date` TIMESTAMP    NOT NULL,
   `description`   TEXT,
   `due_date`      DATETIME,
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

DELIMITER //

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
CREATE PROCEDURE `add_user_session`(IN user_id INT, IN sid VARCHAR(40))
BEGIN
   DELETE FROM `sessions` WHERE `user_id` = user_id;
   INSERT INTO `sessions`(`user_id`, `sid`) VALUES(user_id, sid);
END//

DROP PROCEDURE IF EXISTS `process_event` //
CREATE FUNCTION `process_event`(
   sid VARCHAR(40), act_type INT(2), eid INT, header VARCHAR(100), place_id INT, event_type INT, description TEXT, due_date DATETIME
)
RETURNS TINYINT(1)
BEGIN
   DECLARE uuser_id INT;
   DECLARE result TINYINT(1);
   SELECT `user_id` INTO uuser_id FROM `sessions` WHERE `sid` = sid;
   SET uuser_id = IFNULL(uuser_id, 0);
   IF uuser_id > 0  THEN
      SET result = 1;
      IF act_type = 0 THEN
         DELETE FROM `events` WHERE `id` =  eid;
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
         INSERT INTO `events`(`header`, `owner_id`, `place_id`, `event_type`, `description`, `due_date`) VALUES
            (header, uuser_id, place_id, event_type, description, due_date);
      ELSE
         SET result = 0;
      END IF;
   ELSE
      SET result = 0;
   END IF;
   RETURN result;
END//

DELIMITER ;


INSERT INTO `hostels`(`number`) VALUES
   ('8.1');

INSERT INTO `floors`(`number`) VALUES
   ('4');

INSERT INTO `place_types`(`type_name`) VALUES
   ('Комната');

INSERT INTO `places`(`number`, `polygon`, `place_type`, `floor`, `hostel`) VALUES
   (400, '73,192,340,192,340,23,73,23', 1, 1, 1);

INSERT INTO `event_types`(`type_name`) VALUES
   ('Услуги'),
   ('Мероприятия'),
   ('Досуг');