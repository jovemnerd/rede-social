SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

DROP SCHEMA IF EXISTS `mydb` ;
CREATE SCHEMA IF NOT EXISTS `mydb` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci ;
DROP SCHEMA IF EXISTS `skynerd` ;
CREATE SCHEMA IF NOT EXISTS `skynerd` DEFAULT CHARACTER SET latin1 ;
USE `mydb` ;

-- -----------------------------------------------------
-- Table `skynerd`.`user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`user` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`user` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(105) NULL DEFAULT NULL,
  `email` VARCHAR(255) NULL DEFAULT NULL,
  `password` VARCHAR(255) NULL DEFAULT NULL,
  `login` VARCHAR(255) NULL DEFAULT NULL,
  `active` TINYINT(1) NULL DEFAULT NULL,
  `validation_token` CHAR(32) NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT NULL,
  `banned` TINYINT(3) UNSIGNED NULL DEFAULT NULL,
  `ban_date` DATETIME NULL DEFAULT NULL,
  `last_login` DATETIME NULL DEFAULT NULL,
  `account_cancel_date` DATE NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 147551
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`posts`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`posts` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`posts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `title` VARCHAR(140) NOT NULL,
  `content` TEXT NULL DEFAULT NULL,
  `wp_posts_ID` BIGINT(20) UNSIGNED NULL DEFAULT NULL,
  `date` DATETIME NULL DEFAULT NULL,
  `promoted` TINYINT(1) NULL DEFAULT NULL,
  `promoted_on` DATETIME NULL DEFAULT NULL,
  `public` TINYINT(1) NULL DEFAULT NULL COMMENT 'P: Publicado\nPH: Publicado na home\n',
  `ip` VARCHAR(15) NULL DEFAULT NULL,
  `like_count` BIGINT(20) NOT NULL DEFAULT '0',
  `dislike_count` BIGINT(20) NOT NULL DEFAULT '0',
  `comment_count` BIGINT(20) NOT NULL DEFAULT '0',
  `reply_count` BIGINT(20) NOT NULL DEFAULT '0',
  `reblog_count` INT(11) NULL DEFAULT '0',
  `status` TINYINT(1) NOT NULL DEFAULT '1',
  `deleted_by_uid` INT(11) NOT NULL DEFAULT '1',
  `delete_date` DATETIME NULL DEFAULT NULL,
  `original_posts_id` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_posts_user1_idx` (`user_id` ASC),
  INDEX `idx_uid` (`user_id` ASC),
  INDEX `idx_wppost_id` (`wp_posts_ID` ASC),
  INDEX `idx_post_status` (`status` ASC),
  INDEX `original_posts_id` (`original_posts_id` ASC),
  CONSTRAINT `fk_posts_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `skynerd`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `posts_ibfk_1`
    FOREIGN KEY (`original_posts_id`)
    REFERENCES `skynerd`.`posts` (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 588483
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `mydb`.`nerdtrack_types`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`nerdtrack_types` ;

CREATE TABLE IF NOT EXISTS `mydb`.`nerdtrack_types` (
  `id` INT NOT NULL,
  `type` VARCHAR(45) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`nerdtrack`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `mydb`.`nerdtrack` ;

CREATE TABLE IF NOT EXISTS `mydb`.`nerdtrack` (
  `id` INT NOT NULL,
  `user_id` INT(11) NOT NULL,
  `posts_id` INT(11) NOT NULL,
  `content` TEXT NULL,
  `nerdtrack_types_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_nerdtrack_user_idx` (`user_id` ASC),
  INDEX `fk_nerdtrack_posts1_idx` (`posts_id` ASC),
  INDEX `fk_nerdtrack_nerdtrack_types1_idx` (`nerdtrack_types_id` ASC),
  CONSTRAINT `fk_nerdtrack_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `skynerd`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_nerdtrack_posts1`
    FOREIGN KEY (`posts_id`)
    REFERENCES `skynerd`.`posts` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_nerdtrack_nerdtrack_types1`
    FOREIGN KEY (`nerdtrack_types_id`)
    REFERENCES `mydb`.`nerdtrack_types` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;

USE `skynerd` ;

-- -----------------------------------------------------
-- Table `skynerd`.`badge`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`badge` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`badge` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NULL DEFAULT NULL,
  `description` VARCHAR(255) NULL DEFAULT NULL,
  `icon_url` VARCHAR(255) NULL DEFAULT NULL,
  `bonus_xp` BIGINT(20) NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 44
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`category`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`category` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`category` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 57
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`comment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`comment` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`comment` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `posts_id` INT(11) NOT NULL,
  `in_reply_to` INT(10) UNSIGNED NULL DEFAULT NULL,
  `comment` TEXT NULL DEFAULT NULL,
  `user_id` INT(11) NOT NULL,
  `date` DATETIME NULL DEFAULT NULL,
  `ip` VARCHAR(15) NULL DEFAULT NULL,
  `like_count` BIGINT(20) NOT NULL DEFAULT '0',
  `dislike_count` BIGINT(20) NOT NULL DEFAULT '0',
  `is_wp_comment` TINYINT(1) NOT NULL DEFAULT '0',
  `wp_comment_author` VARCHAR(255) NULL DEFAULT NULL,
  `wp_comment_author_email` VARCHAR(255) NULL DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT '1',
  `deleted_by_uid` INT(11) NOT NULL DEFAULT '1',
  `delete_date` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_comment_posts1_idx` (`posts_id` ASC),
  INDEX `fk_comment_user1_idx` (`user_id` ASC),
  INDEX `idx_comment_uid` (`user_id` ASC),
  INDEX `idx_comment_date` (`date` ASC),
  INDEX `idx_comment_irt` (`in_reply_to` ASC),
  INDEX `idx_comment_status` (`status` ASC),
  CONSTRAINT `fk_comment_posts1`
    FOREIGN KEY (`posts_id`)
    REFERENCES `skynerd`.`posts` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_comment_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `skynerd`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 1314743
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`experience`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`experience` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`experience` (
  `level` INT(11) NOT NULL,
  `exp_needed` BIGINT(20) NULL DEFAULT NULL,
  PRIMARY KEY (`level`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`favorites`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`favorites` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`favorites` (
  `posts_id` INT(11) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `date` DATETIME NULL DEFAULT NULL,
  INDEX `fk_favorites_posts1_idx` (`posts_id` ASC),
  INDEX `fk_favorites_user1_idx` (`user_id` ASC),
  CONSTRAINT `fk_favorites_posts1`
    FOREIGN KEY (`posts_id`)
    REFERENCES `skynerd`.`posts` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_favorites_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `skynerd`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`friendship`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`friendship` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`friendship` (
  `user_id` INT(11) NOT NULL,
  `friend_id` INT(11) NOT NULL,
  `date` DATETIME NULL DEFAULT NULL,
  `status` TINYINT(1) NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`, `friend_id`),
  INDEX `fk_friendship_user1_idx` (`friend_id` ASC),
  INDEX `idx_fid` (`friend_id` ASC),
  CONSTRAINT `fk_friendship_user1`
    FOREIGN KEY (`friend_id`)
    REFERENCES `skynerd`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`login_history`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`login_history` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`login_history` (
  `user_id` INT(11) NOT NULL,
  `date` DATETIME NOT NULL,
  `ip` CHAR(15) NULL DEFAULT NULL,
  PRIMARY KEY (`date`, `user_id`),
  INDEX `fk_login_history_user1_idx` (`user_id` ASC),
  CONSTRAINT `fk_login_history_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `skynerd`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`messages`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`messages` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`messages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `from_user_id` INT(11) NOT NULL,
  `to_user_id` INT(11) NOT NULL,
  `title` VARCHAR(45) NULL DEFAULT NULL,
  `message` TEXT NULL DEFAULT NULL,
  `date` DATETIME NULL DEFAULT NULL,
  `type` ENUM('Pvt','Pbc') NULL DEFAULT NULL,
  `status` ENUM('R','U','D') NULL DEFAULT NULL COMMENT 'Status:\n	R: Lida\n	U: Nao lida\n	D: Excluida',
  `in_reply_to` INT(11) NULL DEFAULT NULL,
  `ip` VARCHAR(15) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_table1_user1_idx` (`from_user_id` ASC),
  INDEX `fk_table1_user2_idx` (`to_user_id` ASC),
  INDEX `fk_status` USING BTREE (`status` ASC, `to_user_id` ASC),
  INDEX `fk_messages_messages1_idx` (`in_reply_to` ASC),
  CONSTRAINT `fk_messages_messages1`
    FOREIGN KEY (`in_reply_to`)
    REFERENCES `skynerd`.`messages` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_table1_user1`
    FOREIGN KEY (`from_user_id`)
    REFERENCES `skynerd`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_table1_user2`
    FOREIGN KEY (`to_user_id`)
    REFERENCES `skynerd`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 84424
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`nerdcasters`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`nerdcasters` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`nerdcasters` (
  `id` INT(11) NOT NULL,
  `name` VARCHAR(125) NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`nerdstore_products`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`nerdstore_products` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`nerdstore_products` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NULL DEFAULT NULL,
  `first_buyer_email` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 45
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`nerdstore_products_firstdaybuyers`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`nerdstore_products_firstdaybuyers` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`nerdstore_products_firstdaybuyers` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT(11) NULL DEFAULT NULL,
  `user_email` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 5774
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`nerdstore_users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`nerdstore_users` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`nerdstore_users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NULL DEFAULT NULL,
  `money_spent` DOUBLE NULL DEFAULT NULL,
  `quantity` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 28449
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`notifications`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`notifications` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`notifications` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `notify_user_id` INT(11) NOT NULL,
  `took_by_user_id` INT(11) NULL DEFAULT NULL,
  `action_type` INT(10) UNSIGNED NOT NULL,
  `action_id` INT(11) NOT NULL,
  `date` DATETIME NOT NULL,
  `readed` TINYINT(1) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_notifications_user1_idx` (`notify_user_id` ASC),
  INDEX `idx_notifications_uid_date` (`notify_user_id` ASC, `date` ASC),
  CONSTRAINT `fk_notifications_user1`
    FOREIGN KEY (`notify_user_id`)
    REFERENCES `skynerd`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 13342586
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`notifications_massive`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`notifications_massive` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`notifications_massive` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `notify_user_id` INT(11) NOT NULL,
  `took_by_user_id` INT(11) NULL DEFAULT NULL,
  `action_type` INT(10) UNSIGNED NOT NULL,
  `action_id` INT(11) NOT NULL,
  `date` DATETIME NOT NULL,
  `readed` TINYINT(1) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_notifications_user1` (`notify_user_id` ASC),
  INDEX `idx_notifications_uid_date` (`notify_user_id` ASC, `date` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 10272273
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`posts_has_category`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`posts_has_category` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`posts_has_category` (
  `posts_id` INT(11) NOT NULL,
  `category_id` INT(11) NOT NULL,
  PRIMARY KEY (`posts_id`, `category_id`),
  INDEX `fk_posts_has_category_category1_idx` (`category_id` ASC),
  INDEX `fk_posts_has_category_posts1_idx` (`posts_id` ASC),
  CONSTRAINT `fk_posts_has_category_category1`
    FOREIGN KEY (`category_id`)
    REFERENCES `skynerd`.`category` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_posts_has_category_posts1`
    FOREIGN KEY (`posts_id`)
    REFERENCES `skynerd`.`posts` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`rating`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`rating` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`rating` (
  `rating` INT(11) NOT NULL,
  `date` DATETIME NOT NULL,
  `ip` VARCHAR(15) NULL DEFAULT NULL,
  `posts_id` INT(11) NOT NULL,
  `comment_id` INT(10) UNSIGNED NOT NULL,
  `user_id` INT(11) NOT NULL,
  PRIMARY KEY (`posts_id`, `comment_id`, `user_id`),
  INDEX `fk_rating_user1_idx` (`user_id` ASC),
  INDEX `idx_pid` (`posts_id` ASC, `rating` ASC),
  INDEX `idx_cid` (`comment_id` ASC, `rating` ASC),
  CONSTRAINT `fk_rating_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `skynerd`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`social_network`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`social_network` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`social_network` (
  `id` INT(11) NOT NULL,
  `name` VARCHAR(145) NOT NULL,
  `external_link` TINYINT(1) NOT NULL,
  `available` TINYINT(1) NOT NULL,
  `can_be_listed` TINYINT(1) NULL DEFAULT '1',
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`user_data`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`user_data` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`user_data` (
  `user_id` INT(11) NOT NULL,
  `genre` ENUM('M','F') NULL DEFAULT NULL,
  `address` VARCHAR(255) NOT NULL,
  `telephone` VARCHAR(20) NOT NULL,
  `profession` VARCHAR(145) NOT NULL,
  `avatar` VARCHAR(255) NOT NULL,
  `cifra_bluehand` VARCHAR(120) NULL DEFAULT NULL,
  `minibio` VARCHAR(255) NULL DEFAULT NULL,
  `friend_count` INT(11) NULL DEFAULT '0',
  `show_nsfw` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`user_id`),
  INDEX `fk_user_data_user1_idx` (`user_id` ASC),
  CONSTRAINT `fk_user_data_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `skynerd`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`user_gamertags`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`user_gamertags` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`user_gamertags` (
  `user_id` INT(11) NOT NULL,
  `psn` VARCHAR(255) NULL DEFAULT NULL,
  `xboxlive` VARCHAR(255) NULL DEFAULT NULL,
  `steam` VARCHAR(255) NULL DEFAULT NULL,
  `battlelog` VARCHAR(255) NULL DEFAULT NULL,
  `nuuvem` VARCHAR(255) NULL DEFAULT NULL,
  `origin` VARCHAR(255) NULL DEFAULT NULL,
  `gamecenter` VARCHAR(255) NULL DEFAULT NULL,
  `raptr` VARCHAR(255) NULL DEFAULT NULL,
  `lol` VARCHAR(255) NULL DEFAULT NULL,
  `battlenet` VARCHAR(120) NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  INDEX `fk_user_gamertags_user1_idx` (`user_id` ASC),
  CONSTRAINT `fk_user_gamertags_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `skynerd`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 147535
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`user_has_social_network`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`user_has_social_network` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`user_has_social_network` (
  `user_id` INT(11) NOT NULL,
  `social_network_id` INT(11) NOT NULL,
  `access_token` TEXT NULL DEFAULT NULL,
  `active` TINYINT(1) NULL DEFAULT '1',
  `options` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`, `social_network_id`),
  INDEX `fk_user_has_social_network_social_network1_idx` (`social_network_id` ASC),
  INDEX `fk_user_has_social_network_user1_idx` (`user_id` ASC),
  CONSTRAINT `fk_user_has_social_network_social_network1`
    FOREIGN KEY (`social_network_id`)
    REFERENCES `skynerd`.`social_network` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_user_has_social_network_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `skynerd`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`user_lists`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`user_lists` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`user_lists` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(145) NULL DEFAULT NULL,
  `user_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_user_lists_user1_idx` (`user_id` ASC),
  CONSTRAINT `fk_user_lists_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `skynerd`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 15874
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`user_lists_has_category`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`user_lists_has_category` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`user_lists_has_category` (
  `user_lists_id` INT(11) NOT NULL,
  `category_id` INT(11) NOT NULL,
  PRIMARY KEY (`user_lists_id`, `category_id`),
  INDEX `fk_user_lists_has_category_category1_idx` (`category_id` ASC),
  INDEX `fk_user_lists_has_category_user_lists1_idx` (`user_lists_id` ASC),
  CONSTRAINT `fk_user_lists_has_category_category1`
    FOREIGN KEY (`category_id`)
    REFERENCES `skynerd`.`category` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_lists_has_category_user_lists1`
    FOREIGN KEY (`user_lists_id`)
    REFERENCES `skynerd`.`user_lists` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`user_lists_has_social_network`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`user_lists_has_social_network` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`user_lists_has_social_network` (
  `user_lists_id` INT(11) NOT NULL,
  `social_network_id` INT(11) NOT NULL,
  PRIMARY KEY (`user_lists_id`, `social_network_id`),
  INDEX `fk_user_lists_has_social_network_social_network1_idx` (`social_network_id` ASC),
  INDEX `fk_user_lists_has_social_network_user_lists1_idx` (`user_lists_id` ASC),
  CONSTRAINT `fk_user_lists_has_social_network_social_network1`
    FOREIGN KEY (`social_network_id`)
    REFERENCES `skynerd`.`social_network` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_user_lists_has_social_network_user_lists1`
    FOREIGN KEY (`user_lists_id`)
    REFERENCES `skynerd`.`user_lists` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`user_notification_settings`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`user_notification_settings` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`user_notification_settings` (
  `user_id` INT(11) NOT NULL,
  `action_type_ids` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  INDEX `fk_user_notification_settings_user1_idx` (`user_id` ASC),
  CONSTRAINT `fk_user_notification_settings_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `skynerd`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 147543
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`user_points`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`user_points` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`user_points` (
  `user_id` INT(11) NOT NULL,
  `exp` INT(11) NULL DEFAULT NULL,
  `hp` INT(11) NULL DEFAULT NULL,
  `gold` INT(11) NULL DEFAULT NULL,
  `current_level` BIGINT(20) NOT NULL DEFAULT '0',
  `exp_needed` BIGINT(20) NOT NULL DEFAULT '0',
  `exp_to_next_level` BIGINT(20) NOT NULL DEFAULT '0',
  INDEX `fk_user_points_user1_idx` (`user_id` ASC),
  CONSTRAINT `fk_user_points_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `skynerd`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`user_privacy_settings`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`user_privacy_settings` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`user_privacy_settings` (
  `user_id` INT(11) NOT NULL,
  `profile` TINYINT(1) NOT NULL,
  `posts` TINYINT(1) NOT NULL,
  `social_network` TINYINT(1) NOT NULL,
  `stats` TINYINT(1) NOT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_user_privacy_settings_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `skynerd`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`nerdtrack_has_nerdcaster`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`nerdtrack_has_nerdcaster` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`nerdtrack_has_nerdcaster` (
  `nerdcasters_id` INT(11) NOT NULL,
  `nerdtrack_id` INT NOT NULL,
  PRIMARY KEY (`nerdcasters_id`, `nerdtrack_id`),
  INDEX `fk_nerdcasters_has_nerdtrack_nerdtrack1_idx` (`nerdtrack_id` ASC),
  INDEX `fk_nerdcasters_has_nerdtrack_nerdcasters1_idx` (`nerdcasters_id` ASC),
  CONSTRAINT `fk_nerdcasters_has_nerdtrack_nerdcasters1`
    FOREIGN KEY (`nerdcasters_id`)
    REFERENCES `skynerd`.`nerdcasters` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_nerdcasters_has_nerdtrack_nerdtrack1`
    FOREIGN KEY (`nerdtrack_id`)
    REFERENCES `mydb`.`nerdtrack` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `skynerd`.`user_has_badge`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `skynerd`.`user_has_badge` ;

CREATE TABLE IF NOT EXISTS `skynerd`.`user_has_badge` (
  `user_id` INT(11) NOT NULL,
  `badge_id` INT(11) NOT NULL,
  `date` DATETIME NULL,
  PRIMARY KEY (`user_id`, `badge_id`),
  INDEX `fk_user_has_badge_badge1_idx` (`badge_id` ASC),
  INDEX `fk_user_has_badge_user1_idx` (`user_id` ASC),
  CONSTRAINT `fk_user_has_badge_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `skynerd`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_user_has_badge_badge1`
    FOREIGN KEY (`badge_id`)
    REFERENCES `skynerd`.`badge` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
