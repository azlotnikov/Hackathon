DROP DATABASE IF EXISTS `hack`;
CREATE DATABASE `hack` DEFAULT CHARSET utf8;

use `hack`;

GRANT ALL
ON `hack`.*
TO `marik`@localhost IDENTIFIED BY 'marik107';

CREATE TABLE IF NOT EXISTS `users` (
   `id`              INT(11)      NOT NULL AUTO_INCREMENT,
   `name`            VARCHAR(70)  NOT NULL,
   `surname`         VARCHAR(70),
   `phone`           VARCHAR(30),
   `description`     TEXT,
   `password`        VARCHAR(80)  NOT NULL,
   `salt`            VARCHAR(8)   NOT NULL,
   `last_update`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   `photo_id`        INT DEFAULT NULL,
   PRIMARY KEY (`id`)
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
END//

DROP TRIGGER IF EXISTS `insert_users`//
CREATE TRIGGER `insert_users` BEFORE INSERT ON `users`
FOR EACH ROW BEGIN
   SET new.password = create_encrypted_pass(new.password, new.salt);
END//

DELIMITER ;