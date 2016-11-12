--
-- Script date 12.11.2016 9:51:10
-- Server version: 5.5.5-10.1.17-MariaDB
-- Client version: 4.1
--


-- 
-- Disable foreign keys
-- 
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

-- 
-- Set SQL mode
-- 
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 
-- Set character set the client will use to send SQL statements to the server
--
SET NAMES 'utf8';

-- 
-- Set default database
--
USE erp_db;

--
-- Definition for table tbl_modules
--
DROP TABLE IF EXISTS tbl_modules;
CREATE TABLE tbl_modules (
  col_modID INT(11) NOT NULL AUTO_INCREMENT,
  col_title VARCHAR(255) DEFAULT NULL COMMENT 'идентификатор названия страницы',
  col_path VARCHAR(255) DEFAULT NULL COMMENT 'местонахождения скрипта',
  col_cache INT(11) DEFAULT 0 COMMENT 'кеширование в секундах',
  col_isOn CHAR(1) DEFAULT '1' COMMENT 'состояние on/off',
  col_isClass CHAR(1) DEFAULT '1' COMMENT 'mvc или обычный скрипт',
  PRIMARY KEY (col_modID)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT = 'модули системы';

--
-- Definition for table tbl_user
--
DROP TABLE IF EXISTS tbl_user;
CREATE TABLE tbl_user (
  col_uID INT(11) NOT NULL AUTO_INCREMENT,
  col_Name VARCHAR(100) DEFAULT NULL COMMENT 'имя',
  col_Sername VARCHAR(100) DEFAULT NULL COMMENT 'фамилия',
  col_Lastname VARCHAR(100) DEFAULT NULL COMMENT 'очество',
  col_login VARCHAR(100) DEFAULT NULL COMMENT 'логин',
  col_pwd VARCHAR(255) DEFAULT NULL COMMENT 'пароль',
  col_gID INT(11) DEFAULT NULL COMMENT 'группа',
  col_isBaned CHAR(1) DEFAULT '0' COMMENT 'забанен ли?',
  PRIMARY KEY (col_uID)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT = 'пользователи';

--
-- Definition for table tbl_user_groups
--
DROP TABLE IF EXISTS tbl_user_groups;
CREATE TABLE tbl_user_groups (
  col_gID INT(11) NOT NULL AUTO_INCREMENT,
  col_gName VARCHAR(250) DEFAULT NULL,
  PRIMARY KEY (col_gID)
)
ENGINE = INNODB
AUTO_INCREMENT = 5
AVG_ROW_LENGTH = 3276
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT = 'группы пользователей';

--
-- Definition for table tbl_user_roles
--
DROP TABLE IF EXISTS tbl_user_roles;
CREATE TABLE tbl_user_roles (
  col_roleID INT(11) NOT NULL AUTO_INCREMENT,
  col_roleName VARCHAR(250) DEFAULT NULL,
  PRIMARY KEY (col_roleID)
)
ENGINE = INNODB
AUTO_INCREMENT = 2
AVG_ROW_LENGTH = 16384
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT = 'роли для пользователя';

--
-- Definition for table tbl_group_roles
--
DROP TABLE IF EXISTS tbl_group_roles;
CREATE TABLE tbl_group_roles (
  col_grID INT(11) NOT NULL AUTO_INCREMENT,
  col_gID INT(11) DEFAULT NULL,
  col_roleID INT(11) DEFAULT NULL,
  PRIMARY KEY (col_grID),
  CONSTRAINT FK_tbl_group_roles_tbl_user_groups_col_gID FOREIGN KEY (col_gID)
    REFERENCES tbl_user_groups(col_gID) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_group_roles_tbl_user_roles_col_roleID FOREIGN KEY (col_roleID)
    REFERENCES tbl_user_roles(col_roleID) ON DELETE RESTRICT ON UPDATE RESTRICT
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT = 'какие роли к какой группе относятся';

--
-- Definition for table tbl_module_roles
--
DROP TABLE IF EXISTS tbl_module_roles;
CREATE TABLE tbl_module_roles (
  col_mrID INT(11) NOT NULL AUTO_INCREMENT,
  col_modID INT(11) DEFAULT NULL,
  col_roleID INT(11) DEFAULT NULL,
  PRIMARY KEY (col_mrID),
  CONSTRAINT FK_tbl_module_roles_tbl_modules_col_modID FOREIGN KEY (col_modID)
    REFERENCES tbl_modules(col_modID) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_module_roles_tbl_user_roles_col_roleID FOREIGN KEY (col_roleID)
    REFERENCES tbl_user_roles(col_roleID) ON DELETE RESTRICT ON UPDATE RESTRICT
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT = 'указание ролей с правами доступа';

--
-- Definition for table tbl_user_role_rule
--
DROP TABLE IF EXISTS tbl_user_role_rule;
CREATE TABLE tbl_user_role_rule (
  col_urID INT(11) NOT NULL AUTO_INCREMENT,
  col_uID INT(11) DEFAULT NULL,
  PRIMARY KEY (col_urID),
  CONSTRAINT FK_tbl_user_role_tbl_user_col_uID FOREIGN KEY (col_uID)
    REFERENCES tbl_user(col_uID) ON DELETE RESTRICT ON UPDATE RESTRICT
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT = 'роли, что назначены пользователю';

-- 
-- Dumping data for table tbl_modules
--

-- Table erp_db.tbl_modules does not contain any data (it is empty)

-- 
-- Dumping data for table tbl_user
--

-- Table erp_db.tbl_user does not contain any data (it is empty)

-- 
-- Dumping data for table tbl_user_groups
--
INSERT INTO tbl_user_groups VALUES
(1, 'Администратор'),
(2, 'Гость'),
(3, 'Пользователь'),
(4, 'Все');

-- 
-- Dumping data for table tbl_user_roles
--
INSERT INTO tbl_user_roles VALUES
(1, 'Администратор системы');

-- 
-- Dumping data for table tbl_group_roles
--

-- Table erp_db.tbl_group_roles does not contain any data (it is empty)

-- 
-- Dumping data for table tbl_module_roles
--

-- Table erp_db.tbl_module_roles does not contain any data (it is empty)

-- 
-- Dumping data for table tbl_user_role_rule
--

-- Table erp_db.tbl_user_role_rule does not contain any data (it is empty)

-- 
-- Restore previous SQL mode
-- 
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;

-- 
-- Enable foreign keys
-- 
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;