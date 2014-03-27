DROP DATABASE IF EXISTS `camerapeople`;
CREATE DATABASE `camerapeople` DEFAULT CHARSET utf8;

use `camerapeople`;

GRANT ALL
ON `camerapeople`.*
TO `marik`@localhost IDENTIFIED BY 'marik107';

CREATE TABLE IF NOT EXISTS `images` (
   `id`         INT(11)    NOT NULL AUTO_INCREMENT,
   `is_resized` TINYINT(4) NOT NULL DEFAULT 0,
   PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `users` (
   `id`              INT(11)      NOT NULL AUTO_INCREMENT,
   `name`            VARCHAR(70)  NOT NULL,
   `surname`         VARCHAR(70),
   `email`           VARCHAR(120) NOT NULL,
   `site`            VARCHAR(60),
   `skype`           VARCHAR(40),
   `phone`           VARCHAR(30),
   `description`     TEXT,
   `password`        VARCHAR(80)  NOT NULL,
   `salt`            VARCHAR(8)   NOT NULL,
   `is_photographer` TINYINT(4)   NOT NULL DEFAULT 0,
   `is_videographer` TINYINT(4)   NOT NULL DEFAULT 0,
   `verification`    TINYINT(4)   NOT NULL DEFAULT 0,
   `register_date`   DATETIME     NOT NULL,
   `last_update`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   `profile_views`   INT DEFAULT 0,
   `photo_id`        INT DEFAULT NULL,
   PRIMARY KEY (`id`),
   UNIQUE KEY `email` (`email`),
   FOREIGN KEY (`photo_id`) REFERENCES `images` (`id`) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS `users_forgotten_pass` (
   `id`           INT         NOT NULL AUTO_INCREMENT,
   `user_id`      INT         NOT NULL,
   `new_password` VARCHAR(80) NOT NULL,
   `new_salt`     VARCHAR(8)  NOT NULL,
   `change_date`  DATETIME    NOT NULL,
   PRIMARY KEY (`id`),
   UNIQUE KEY  (`user_id`),
   FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `admin` (
   `id`       INT         NOT NULL AUTO_INCREMENT,
   `login`    VARCHAR(50) NOT NULL,
   `pass_md5` VARCHAR(50) NOT NULL,
   PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `ps_categories` (
   `id`   INT         NOT NULL AUTO_INCREMENT,
   `name` VARCHAR(20) NOT NULL,
   PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `user_ps_spec` (
   `id`          INT NOT NULL AUTO_INCREMENT,
   `user_id`     INT NOT NULL,
   `category_id` INT NOT NULL,
   PRIMARY KEY (`id`),
   FOREIGN KEY (`user_id`)     REFERENCES `users`         (`id`) ON DELETE CASCADE,
   FOREIGN KEY (`category_id`) REFERENCES `ps_categories` (`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `vs_categories` (
   `id`   INT         NOT NULL AUTO_INCREMENT,
   `name` VARCHAR(20) NOT NULL,
   PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `user_vs_spec` (
   `id`          INT NOT NULL AUTO_INCREMENT,
   `user_id`     INT NOT NULL,
   `category_id` INT NOT NULL,
   PRIMARY KEY (`id`),
   FOREIGN KEY (`user_id`)     REFERENCES `users`         (`id`) ON DELETE CASCADE,
   FOREIGN KEY (`category_id`) REFERENCES `vs_categories` (`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `photosessions` (
   `id`          INT          NOT NULL AUTO_INCREMENT,
   `name`        VARCHAR(100) NOT NULL,
   `site`        VARCHAR(60),
   `description` TEXT,
   `user_id`     INT          NOT NULL,
   `photo_id`    INT          DEFAULT NULL,
   `category_id` INT          NOT NULL,
   `create_date` DATETIME     NOT NULL,
   PRIMARY KEY (`id`),
   FOREIGN KEY (`photo_id`)    REFERENCES `images`        (`id`) ON DELETE SET NULL,
   FOREIGN KEY (`user_id`)     REFERENCES `users`         (`id`) ON DELETE CASCADE,
   FOREIGN KEY (`category_id`) REFERENCES `ps_categories` (`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `ps_imgs` (
   `id`       INT NOT NULL AUTO_INCREMENT,
   `sess_id`  INT NOT NULL,
   `photo_id` INT NOT NULL,
   PRIMARY KEY (`id`),
   FOREIGN KEY (`photo_id`) REFERENCES `images`        (`id`) ON DELETE CASCADE,
   FOREIGN KEY (`sess_id`)  REFERENCES `photosessions` (`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `videosessions` (
   `id`          INT          NOT NULL AUTO_INCREMENT,
   `name`        VARCHAR(100) NOT NULL,
   `site`        VARCHAR(60),
   `description` TEXT,
   `user_id`     INT          NOT NULL,
   `photo_id`    INT          DEFAULT NULL,
   `category_id` INT          NOT NULL,
   `create_date` DATETIME     NOT NULL,
   PRIMARY KEY (`id`),
   FOREIGN KEY (`photo_id`)    REFERENCES `images`        (`id`) ON DELETE SET NULL,
   FOREIGN KEY (`user_id`)     REFERENCES `users`         (`id`) ON DELETE CASCADE,
   FOREIGN KEY (`category_id`) REFERENCES `vs_categories` (`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `texts` (
   `id`         INT          NOT NULL AUTO_INCREMENT,
   `name`       VARCHAR(150) NOT NULL,
   `text_body`  TEXT         NOT NULL,
   PRIMARY KEY (`id`)
);

DELIMITER //

DROP FUNCTION IF EXISTS `create_encrypted_pass` //
CREATE FUNCTION `create_encrypted_pass`(pass VARCHAR(80), salt VARCHAR(8))
RETURNS VARCHAR(80)
BEGIN
   RETURN MD5(CONCAT(salt, MD5(CONCAT(pass, salt)), SUBSTR(salt, 2)));
END//

DROP FUNCTION IF EXISTS `get_user_photo_amount_by_id` //
CREATE FUNCTION `get_user_photo_amount_by_id`(user_id INT)
RETURNS INT
BEGIN
   DECLARE amount1, amount2 INT;
   SELECT COUNT(psi.id) INTO amount1 FROM ps_imgs psi
   INNER JOIN photosessions ps ON ps.id = psi.sess_id AND ps.user_id = user_id
   INNER JOIN images i ON i.id = psi.photo_id AND i.is_resized = 1;
   SELECT COUNT(vs.id)  INTO amount2 FROM videosessions vs WHERE vs.user_id = user_id;
   SET amount1 = IFNULL(amount1, 0);
   SET amount2 = IFNULL(amount2, 0);
   SET amount1 = amount1 + amount2;
   RETURN amount1;
END//

DROP FUNCTION IF EXISTS `get_user_photo_amount_by_email` //
CREATE FUNCTION `get_user_photo_amount_by_email`(user_email VARCHAR(120))
RETURNS INT
BEGIN
   DECLARE user_id INT;
   SELECT `id` INTO user_id FROM `users` WHERE `email` = user_email;
   RETURN get_user_photo_amount_by_id(user_id);
END//

DROP PROCEDURE IF EXISTS `remove_old_users_info` //
CREATE PROCEDURE `remove_old_users_info`()
BEGIN
   DELETE FROM `users` WHERE `verification` = 0 AND `register_date` < NOW() - INTERVAL 3 DAY;
   DELETE FROM `users_forgotten_pass` WHERE `change_date` < NOW() - INTERVAL 3 day;
END//

DROP TRIGGER IF EXISTS `update_admin`//
CREATE TRIGGER `update_admin` BEFORE UPDATE ON `admin`
FOR EACH ROW BEGIN
   IF new.pass_md5 != old.pass_md5 THEN
      SET new.pass_md5 = MD5(new.pass_md5);
   END IF;
END//

DROP TRIGGER IF EXISTS `update_users`//
CREATE TRIGGER `update_users` BEFORE UPDATE ON `users`
FOR EACH ROW BEGIN
   IF new.is_photographer = 0 AND new.is_videographer = 0 THEN
      SET new.is_photographer = 1;
   END IF;
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

DROP TRIGGER IF EXISTS `insert_photosessions`//
CREATE TRIGGER `insert_photosessions` BEFORE INSERT ON `photosessions`
FOR EACH ROW BEGIN
   DECLARE amount INT;
   SELECT COUNT(user_ps_spec.id) INTO amount FROM user_ps_spec WHERE user_ps_spec.user_id = new.user_id
      AND user_ps_spec.category_id = new.category_id;
   SET amount = IFNULL(amount, 0);
   IF amount = 0 THEN
      INSERT INTO user_ps_spec(user_id, category_id) VALUES(new.user_id, new.category_id);
   END IF;
   SET new.create_date = CURRENT_TIMESTAMP;
   UPDATE `users` SET last_update = CURRENT_TIMESTAMP;
END//

DROP TRIGGER IF EXISTS `update_photosessions`//
CREATE TRIGGER `update_photosessions` BEFORE UPDATE ON `photosessions`
FOR EACH ROW BEGIN
   UPDATE `users` SET last_update = CURRENT_TIMESTAMP;
END//

DROP TRIGGER IF EXISTS `delete_photosessions`//
CREATE TRIGGER `delete_photosessions` AFTER DELETE ON `photosessions`
FOR EACH ROW BEGIN
   DECLARE amount INT;
   SELECT COUNT(photosessions.id) INTO amount FROM photosessions
      WHERE photosessions.user_id = old.user_id AND photosessions.category_id = old.category_id;
   SET amount = IFNULL(amount, 0);
   IF amount = 0 THEN
      DELETE FROM user_ps_spec WHERE user_id = old.user_id AND category_id = old.category_id;
   END IF;
   UPDATE `users` SET last_update = CURRENT_TIMESTAMP;
   DELETE FROM `images` WHERE `id` IN (SELECT `photo_id` FROM `ps_imgs` WHERE `sess_id` = old.id);
END//

DROP TRIGGER IF EXISTS `insert_videosessions`//
CREATE TRIGGER `insert_videosessions` BEFORE INSERT ON `videosessions`
FOR EACH ROW BEGIN
   DECLARE amount INT;
   SELECT COUNT(user_vs_spec.id) INTO amount FROM user_vs_spec WHERE user_vs_spec.user_id = new.user_id
      AND user_vs_spec.category_id = new.category_id;
   SET amount = IFNULL(amount, 0);
   IF amount = 0 THEN
      INSERT INTO user_vs_spec(user_id, category_id) VALUES(new.user_id, new.category_id);
   END IF;
   SET new.create_date = CURRENT_TIMESTAMP;
   UPDATE `users` SET last_update = CURRENT_TIMESTAMP;
END//

DROP TRIGGER IF EXISTS `update_videosessions`//
CREATE TRIGGER `update_videosessions` BEFORE UPDATE ON `videosessions`
FOR EACH ROW BEGIN
   UPDATE `users` SET last_update = CURRENT_TIMESTAMP;
END//

DROP TRIGGER IF EXISTS `delete_videosessions`//
CREATE TRIGGER `delete_videosessions` AFTER DELETE ON `videosessions`
FOR EACH ROW BEGIN
   DECLARE amount INT;
   SELECT COUNT(videosessions.id) INTO amount FROM videosessions
      WHERE videosessions.user_id = old.user_id AND videosessions.category_id = old.category_id;
   SET amount = IFNULL(amount, 0);
   IF amount = 0 THEN
      DELETE FROM user_vs_spec WHERE user_id = old.user_id AND category_id = old.category_id;
   END IF;
   UPDATE `users` SET last_update = CURRENT_TIMESTAMP;
   DELETE FROM `images` WHERE `id` = old.photo_id;
END//

DELIMITER ;

INSERT INTO `admin`(`login`, `pass_md5`) VALUES('admin', '21232f297a57a5a743894a0e4a801fc3');

INSERT INTO `ps_categories`(`name`) VALUES
   ('Свадьба и праздники'),
   ('Дети и семья'),
   ('Натюрморт'),
   ('Студия и портфолио'),
   ('Репортаж'),
   ('Другое');

INSERT INTO `vs_categories`(`name`) VALUES
   ('Свадьба и праздники'),
   ('Натюрморт'),
   ('Студия и портфолио'),
   ('Репортаж'),
   ('Другое');

INSERT INTO `texts`(`name`, `text_body`) VALUES
   ('Страница о проекте 1', 'Текст проекта 1'),
   ('Страница о проекте 2', 'Текст проекта 2'),
   ('Верхняя реклама на главной странице', ''),
   ('Нижняя реклама на главной странице', '');