SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

-- -----------------------------------------------------
-- Table `account_group`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `account_group` ;

CREATE  TABLE IF NOT EXISTS `account_group` (
  `id_account_group` BIGINT NOT NULL AUTO_INCREMENT ,
  `email` VARCHAR(255) NULL ,
  PRIMARY KEY (`id_account_group`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `recipe`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `recipe` ;

CREATE  TABLE IF NOT EXISTS `recipe` (
  `id_recipe` BIGINT NOT NULL AUTO_INCREMENT ,
  `id_account_group` BIGINT NOT NULL ,
  `title` VARCHAR(255) NULL ,
  `ingredients` TEXT NULL ,
  `directions` TEXT NULL ,
  `yield` VARCHAR(45) NULL ,
  `source` TEXT NULL ,
  `notes` TEXT NULL ,
  `created` TIMESTAMP NULL ,
  PRIMARY KEY (`id_recipe`) ,
  INDEX `fk_recipe_account_group1` (`id_account_group` ASC) ,
  CONSTRAINT `fk_recipe_account_group1`
    FOREIGN KEY (`id_account_group` )
    REFERENCES `account_group` (`id_account_group` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `image`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `image` ;

CREATE  TABLE IF NOT EXISTS `image` (
  `id_image` BIGINT NOT NULL AUTO_INCREMENT ,
  `id_recipe` BIGINT NOT NULL ,
  `image_type` VARCHAR(32) NULL COMMENT 'recipe_scan\nphoto' ,
  `headline` VARCHAR(255) NULL ,
  `caption` TEXT NULL ,
  `filepath` VARCHAR(255) NULL ,
  PRIMARY KEY (`id_image`) ,
  INDEX `fk_image_recipe` (`id_recipe` ASC) ,
  CONSTRAINT `fk_image_recipe`
    FOREIGN KEY (`id_recipe` )
    REFERENCES `recipe` (`id_recipe` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tag`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `tag` ;

CREATE  TABLE IF NOT EXISTS `tag` (
  `id_tag` BIGINT NOT NULL AUTO_INCREMENT ,
  `id_account_group` BIGINT NOT NULL ,
  `tag` VARCHAR(255) NULL ,
  PRIMARY KEY (`id_tag`) ,
  INDEX `fk_tag_account_group1` (`id_account_group` ASC) ,
  UNIQUE INDEX `uq_account_group_tag` (`id_account_group` ASC, `tag` ASC) ,
  CONSTRAINT `fk_tag_account_group1`
    FOREIGN KEY (`id_account_group` )
    REFERENCES `account_group` (`id_account_group` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `recipe_to_tag`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `recipe_to_tag` ;

CREATE  TABLE IF NOT EXISTS `recipe_to_tag` (
  `id_recipe` BIGINT NOT NULL ,
  `id_tag` BIGINT NOT NULL ,
  PRIMARY KEY (`id_recipe`, `id_tag`) ,
  INDEX `fk_recipe_to_tag_recipe1` (`id_recipe` ASC) ,
  INDEX `fk_recipe_to_tag_tag1` (`id_tag` ASC) ,
  CONSTRAINT `fk_recipe_to_tag_recipe1`
    FOREIGN KEY (`id_recipe` )
    REFERENCES `recipe` (`id_recipe` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_recipe_to_tag_tag1`
    FOREIGN KEY (`id_tag` )
    REFERENCES `tag` (`id_tag` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `account`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `account` ;

CREATE  TABLE IF NOT EXISTS `account` (
  `id_account` BIGINT NOT NULL AUTO_INCREMENT ,
  `id_account_group` BIGINT NOT NULL ,
  `email` VARCHAR(255) NULL ,
  `password` VARCHAR(64) NULL ,
  `fname` VARCHAR(64) NULL ,
  `lname` VARCHAR(64) NULL ,
  `confirmed` TINYINT(1)  NULL DEFAULT 0 ,
  `confirmation_key` VARCHAR(64) NULL ,
  `unlock_key` VARCHAR(64) NULL ,
  `url_key` VARCHAR(64) NULL ,
  `created` TIMESTAMP NULL ,
  PRIMARY KEY (`id_account`) ,
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) ,
  INDEX `fk_account_account_group1` (`id_account_group` ASC) ,
  CONSTRAINT `fk_account_account_group1`
    FOREIGN KEY (`id_account_group` )
    REFERENCES `account_group` (`id_account_group` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `recent_recipe`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `recent_recipe` ;

CREATE  TABLE IF NOT EXISTS `recent_recipe` (
  `id_account` BIGINT NOT NULL ,
  `id_recipe` BIGINT NOT NULL ,
  `accessed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  INDEX `fk_recent_recipes_account1` (`id_account` ASC) ,
  INDEX `fk_recent_recipes_recipe1` (`id_recipe` ASC) ,
  PRIMARY KEY (`id_account`, `id_recipe`) ,
  CONSTRAINT `fk_recent_recipes_account1`
    FOREIGN KEY (`id_account` )
    REFERENCES `account` (`id_account` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_recent_recipes_recipe1`
    FOREIGN KEY (`id_recipe` )
    REFERENCES `recipe` (`id_recipe` )
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

