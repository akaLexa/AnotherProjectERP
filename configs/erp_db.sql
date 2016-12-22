﻿-- Script date 22.12.2016 15:58:06
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
-- Definition for table tbl_hb_project_stage
--
DROP TABLE IF EXISTS tbl_hb_project_stage;
CREATE TABLE tbl_hb_project_stage (
  col_StageID INT(11) NOT NULL AUTO_INCREMENT,
  col_StageName VARCHAR(200) DEFAULT NULL,
  col_isDel CHAR(1) DEFAULT '0',
  PRIMARY KEY (col_StageID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 5
  AVG_ROW_LENGTH = 5461
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'стадии проекта';

--
-- Definition for table tbl_hb_status
--
DROP TABLE IF EXISTS tbl_hb_status;
CREATE TABLE tbl_hb_status (
  col_StatusID INT(11) NOT NULL AUTO_INCREMENT,
  col_StatusName VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (col_StatusID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 6
  AVG_ROW_LENGTH = 4096
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'статусы';

--
-- Definition for table tbl_hb_task_types
--
DROP TABLE IF EXISTS tbl_hb_task_types;
CREATE TABLE tbl_hb_task_types (
  col_tttID INT(11) NOT NULL AUTO_INCREMENT,
  col_tName VARCHAR(255) DEFAULT NULL,
  col_isDel CHAR(1) DEFAULT '0',
  PRIMARY KEY (col_tttID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 4
  AVG_ROW_LENGTH = 5461
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'справочник типовых названий задач';

--
-- Definition for table tbl_menu
--
DROP TABLE IF EXISTS tbl_menu;
CREATE TABLE tbl_menu (
  col_id INT(11) NOT NULL AUTO_INCREMENT,
  col_mtitle VARCHAR(255) DEFAULT NULL,
  col_mtype INT(11) DEFAULT NULL,
  col_link VARCHAR(255) DEFAULT NULL,
  col_modul VARCHAR(255) DEFAULT NULL,
  col_Seq INT(11) DEFAULT NULL,
  PRIMARY KEY (col_id)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 8
  AVG_ROW_LENGTH = 3276
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

--
-- Definition for table tbl_menu_type
--
DROP TABLE IF EXISTS tbl_menu_type;
CREATE TABLE tbl_menu_type (
  col_id INT(11) NOT NULL AUTO_INCREMENT,
  col_ttitle VARCHAR(255) DEFAULT NULL,
  col_seq INT(11) DEFAULT NULL,
  PRIMARY KEY (col_id)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 3
  AVG_ROW_LENGTH = 8192
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

--
-- Definition for table tbl_module_groups
--
DROP TABLE IF EXISTS tbl_module_groups;
CREATE TABLE tbl_module_groups (
  col_mgID INT(11) NOT NULL AUTO_INCREMENT,
  col_modID INT(11) DEFAULT NULL,
  col_gID INT(11) DEFAULT NULL,
  PRIMARY KEY (col_mgID),
  CONSTRAINT FK_tbl_module_groups_col_gID FOREIGN KEY (col_gID)
  REFERENCES tbl_user_groups(col_gID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_module_groups_col_modID FOREIGN KEY (col_modID)
  REFERENCES tbl_modules(col_modID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 13
  AVG_ROW_LENGTH = 2730
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'разрешения групп к модулям';

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
-- Definition for table tbl_modules
--
DROP TABLE IF EXISTS tbl_modules;
CREATE TABLE tbl_modules (
  col_modID INT(11) NOT NULL AUTO_INCREMENT,
  col_title VARCHAR(255) DEFAULT NULL COMMENT 'идентификатор названия страницы',
  col_path VARCHAR(255) DEFAULT NULL COMMENT 'местонахождения скрипта',
  col_cache INT(11) DEFAULT 0 COMMENT 'кеширование в секундах',
  col_isClass CHAR(1) DEFAULT '1' COMMENT 'mvc или обычный скрипт',
  col_moduleName VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (col_modID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 8
  AVG_ROW_LENGTH = 2730
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'модули системы';

--
-- Definition for table tbl_plugins
--
DROP TABLE IF EXISTS tbl_plugins;
CREATE TABLE tbl_plugins (
  col_pID INT(11) NOT NULL AUTO_INCREMENT,
  col_pluginName VARCHAR(255) DEFAULT NULL,
  col_seq INT(11) DEFAULT 0 COMMENT 'очередь загрузки',
  col_isClass CHAR(1) DEFAULT '0',
  col_pluginState CHAR(1) DEFAULT '0' COMMENT '0 - выключен 1- включен',
  col_cache INT(11) DEFAULT 0,
  PRIMARY KEY (col_pID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 4
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

--
-- Definition for table tbl_plugins_group
--
DROP TABLE IF EXISTS tbl_plugins_group;
CREATE TABLE tbl_plugins_group (
  col_pgID INT(11) NOT NULL AUTO_INCREMENT,
  col_pID INT(11) DEFAULT NULL,
  col_gID INT(11) DEFAULT NULL,
  PRIMARY KEY (col_pgID),
  CONSTRAINT FK_tbl_plugins_group_col_gID FOREIGN KEY (col_gID)
  REFERENCES tbl_user_groups(col_gID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_plugins_group_col_pID FOREIGN KEY (col_pID)
  REFERENCES tbl_plugins(col_pID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 18
  AVG_ROW_LENGTH = 8192
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'группы и плагины';

--
-- Definition for table tbl_plugins_roles
--
DROP TABLE IF EXISTS tbl_plugins_roles;
CREATE TABLE tbl_plugins_roles (
  col_prID INT(11) NOT NULL AUTO_INCREMENT,
  col_pID INT(11) DEFAULT NULL,
  col_roleID INT(11) DEFAULT NULL,
  PRIMARY KEY (col_prID),
  CONSTRAINT FK_tbl_plugins_role_col_pID FOREIGN KEY (col_pID)
  REFERENCES tbl_plugins(col_pID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_plugins_role_col_roleID FOREIGN KEY (col_roleID)
  REFERENCES tbl_user_roles(col_roleID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 1
  AVG_ROW_LENGTH = 16384
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

--
-- Definition for table tbl_project
--
DROP TABLE IF EXISTS tbl_project;
CREATE TABLE tbl_project (
  col_projectID INT(11) NOT NULL AUTO_INCREMENT,
  col_projectName VARCHAR(200) DEFAULT NULL,
  col_pnID INT(11) DEFAULT NULL,
  col_founderID INT(11) DEFAULT NULL,
  col_CreateDate TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  col_Desc TEXT DEFAULT NULL,
  col_ProjectPlanState CHAR(1) DEFAULT '0' COMMENT '0/1 не запущен, запущен план проекта',
  PRIMARY KEY (col_projectID),
  CONSTRAINT FK_tbl_project_col_founderID FOREIGN KEY (col_founderID)
  REFERENCES tbl_user(col_uID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_project_col_pnID FOREIGN KEY (col_pnID)
  REFERENCES tbl_project_num(col_pnID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 2
  AVG_ROW_LENGTH = 16384
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

--
-- Definition for table tbl_project_num
--
DROP TABLE IF EXISTS tbl_project_num;
CREATE TABLE tbl_project_num (
  col_pnID INT(11) NOT NULL AUTO_INCREMENT,
  col_serNum INT(11) DEFAULT 1,
  PRIMARY KEY (col_pnID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 2
  AVG_ROW_LENGTH = 16384
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'серийные номера';

--
-- Definition for table tbl_project_stage
--
DROP TABLE IF EXISTS tbl_project_stage;
CREATE TABLE tbl_project_stage (
  col_pstageID INT(11) NOT NULL AUTO_INCREMENT,
  col_projectID INT(11) DEFAULT NULL,
  col_statusID INT(11) DEFAULT NULL,
  col_respID INT(11) DEFAULT NULL,
  col_dateCreate TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  col_dateStart DATETIME DEFAULT NULL,
  col_dateStartPlan DATETIME DEFAULT NULL,
  col_dateEnd DATETIME DEFAULT NULL COMMENT 'дата окончания принятия решения',
  col_dateEndPlan DATETIME DEFAULT NULL,
  col_dateEndFact DATETIME DEFAULT NULL,
  col_comment TEXT DEFAULT NULL,
  col_stageID INT(11) DEFAULT NULL,
  col_prevStageID INT(11) DEFAULT NULL COMMENT 'предыдущий статус (для возврата при отказе)',
  col_seq INT(11) DEFAULT NULL COMMENT 'очередность',
  col_duration INT(11) DEFAULT 1,
  PRIMARY KEY (col_pstageID),
  CONSTRAINT FK_tbl_project_stage_col_proje FOREIGN KEY (col_projectID)
  REFERENCES tbl_project(col_projectID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_project_stage_col_respI FOREIGN KEY (col_respID)
  REFERENCES tbl_user(col_uID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_project_stage_col_stage FOREIGN KEY (col_stageID)
  REFERENCES tbl_hb_project_stage(col_StageID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_project_stage_col_statu FOREIGN KEY (col_statusID)
  REFERENCES tbl_hb_status(col_StatusID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 5
  AVG_ROW_LENGTH = 4096
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

--
-- Definition for table tbl_roles_in_group
--
DROP TABLE IF EXISTS tbl_roles_in_group;
CREATE TABLE tbl_roles_in_group (
  col_rigID INT(11) NOT NULL AUTO_INCREMENT,
  col_gID INT(11) DEFAULT NULL COMMENT 'группа',
  col_roleID INT(11) DEFAULT NULL COMMENT 'роль',
  PRIMARY KEY (col_rigID),
  CONSTRAINT FK_tbl_roles_in_group_col_gID FOREIGN KEY (col_gID)
  REFERENCES tbl_user_groups(col_gID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_roles_in_group_col_role FOREIGN KEY (col_roleID)
  REFERENCES tbl_user_roles(col_roleID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 3
  AVG_ROW_LENGTH = 16384
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'к какой группе, какая роль принадлежит';

--
-- Definition for table tbl_tasks
--
DROP TABLE IF EXISTS tbl_tasks;
CREATE TABLE tbl_tasks (
  col_taskID INT(11) NOT NULL AUTO_INCREMENT,
  col_taskName VARCHAR(255) DEFAULT NULL,
  col_StatusID INT(11) DEFAULT NULL,
  col_initID INT(11) DEFAULT NULL COMMENT 'кто назначил',
  col_respID INT(11) DEFAULT NULL COMMENT 'кому назначил',
  col_curatorID INT(11) DEFAULT NULL COMMENT 'кто курирует',
  col_pstageID INT(11) DEFAULT NULL,
  col_taskDesc TEXT DEFAULT NULL,
  col_createDate DATETIME DEFAULT NULL,
  col_startPlan DATETIME DEFAULT NULL,
  col_startFact DATETIME DEFAULT NULL,
  col_endPlan DATETIME DEFAULT NULL,
  col_endFact DATETIME DEFAULT NULL,
  col_autoStart DATETIME DEFAULT NULL COMMENT 'дота, когда задача автоматически будет щапущена (если была в 4 статусе)',
  col_taskDur INT(11) DEFAULT 0,
  PRIMARY KEY (col_taskID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 1
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'задачи';

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
  col_roleID INT(11) DEFAULT NULL COMMENT 'роль',
  col_isBaned CHAR(1) DEFAULT '0' COMMENT 'забанен ли?',
  col_deputyID INT(11) DEFAULT NULL,
  col_StartDep DATETIME DEFAULT NULL,
  col_banDate DATETIME DEFAULT NULL,
  col_regDate TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (col_uID),
  CONSTRAINT FK_tbl_user_col_roleID FOREIGN KEY (col_roleID)
  REFERENCES tbl_user_roles(col_roleID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 2
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
  AVG_ROW_LENGTH = 4096
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
  PRIMARY KEY (col_roleID),
  UNIQUE INDEX UK_tbl_user_roles_col_roleName (col_roleName)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 7
  AVG_ROW_LENGTH = 2730
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'роли для пользователя';

DELIMITER $$

--
-- Definition for procedure sp_CalcProjectPlan
--
DROP PROCEDURE IF EXISTS sp_CalcProjectPlan$$
CREATE PROCEDURE sp_CalcProjectPlan(IN projectID INT, IN dateStart DATE)
  SQL SECURITY INVOKER
MODIFIES SQL DATA
  COMMENT 'расчет плана проекта'
  BEGIN

    DECLARE currentStage int;
    DECLARE currentDuration int;
    DECLARE currentEndDate date;
    DECLARE i int DEFAULT 0;
    DECLARE done int DEFAULT FALSE;

    DECLARE cu_Work CURSOR FOR

      SELECT
        tps.col_pstageID,
        tps.col_duration
      FROM
        tbl_project_stage tps,
        tbl_hb_project_stage thps,
        tbl_hb_status ths
      WHERE
        tps.col_projectID = projectID
        AND tps.col_statusID = 5
        AND thps.col_StageID = tps.col_stageID
        AND ths.col_StatusID = tps.col_statusID
      ORDER BY tps.col_seq;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cu_Work;

    read_loop:
    LOOP
      FETCH cu_Work INTO currentStage, currentDuration;
      IF done
      THEN
        LEAVE read_loop;
      END IF;

      set currentEndDate = DATE_ADD(dateStart, INTERVAL currentDuration DAY);
      UPDATE tbl_project_stage tps set tps.col_dateStartPlan = dateStart, tps.col_dateEndPlan = currentEndDate WHERE tps.col_pstageID = currentStage;
      set dateStart = currentEndDate;

    END LOOP;
    CLOSE cu_Work;
  END
$$

--
-- Definition for function f_getUserFIO
--
DROP FUNCTION IF EXISTS f_getUserFIO$$
CREATE FUNCTION f_getUserFIO(userID INT)
  RETURNS varchar(255) CHARSET utf8
  SQL SECURITY INVOKER
READS SQL DATA
  COMMENT 'возвращает Фамилия И.О. по id'
  BEGIN

    RETURN (SELECT CONCAT(tu.col_Sername,' ',COALESCE(LEFT(tu.col_Name,1),'?'),'.',COALESCE(LEFT(tu.col_Lastname,1),'?'),'.') FROM tbl_user tu WHERE tu.col_uID = userID);

  END
$$

--
-- Definition for function f_setProjectNum
--
DROP FUNCTION IF EXISTS f_setProjectNum$$
CREATE FUNCTION f_setProjectNum(orderNum INT)
  RETURNS int(11)
  SQL SECURITY INVOKER
MODIFIES SQL DATA
  COMMENT 'получение id номера'
  BEGIN
    DECLARE numsID int DEFAULT NULL;

    IF orderNum > 0 THEN
      SELECT tpn.col_serNum INTO numsID FROM tbl_project_num tpn WHERE tpn.col_pnID = orderNum;
      IF numsID IS not NULL AND numsID >0 THEN
        UPDATE tbl_project_num set orderNum = numsID + 1 WHERE col_pnID = orderNum;
        RETURN orderNum;
      END IF;
    ELSE
      INSERT INTO  tbl_project_num (col_serNum)  VALUE (1);
      RETURN LAST_INSERT_ID();
    END IF;
    RETURN 1;
  END
$$

DELIMITER ;

--
-- Dumping data for table tbl_group_roles
--

-- Table erp_db.tbl_group_roles does not contain any data (it is empty)

--
-- Dumping data for table tbl_hb_project_stage
--
INSERT INTO tbl_hb_project_stage VALUES
  (1, 'Создание. Сбор информации', '0'),
  (2, 'Выполнение проекта', '0'),
  (3, 'Проект завершен', '0'),
  (4, 'Отказ', '0');

--
-- Dumping data for table tbl_hb_status
--
INSERT INTO tbl_hb_status VALUES
  (1, 'В работе'),
  (2, 'Отклонено'),
  (3, 'Завершено'),
  (4, 'Принятие решения'),
  (5, 'План');

--
-- Dumping data for table tbl_hb_task_types
--
INSERT INTO tbl_hb_task_types VALUES
  (1, 'тестовое название 1', '0'),
  (2, 'тестовое название 2.1', '0'),
  (3, 'тестовое название 3', '1');

--
-- Dumping data for table tbl_menu
--
INSERT INTO tbl_menu VALUES
  (1, 'auto_title2', 1, 'page/UnitManager.html', 'UnitManager', 2),
  (2, 'auto_title3', 1, '', '-1', 1),
  (3, 'auto_title4', 2, '', '-1', 1),
  (4, 'auto_title1', 2, 'page/projectList.html', 'projectList', 2),
  (5, 'auto_title5', 2, 'page/addProject.html', 'addProject', 3),
  (6, 'auto_title7', 1, 'page/ProjectManager.html', 'ProjectManager', 3),
  (7, 'auto_title8', 1, 'page/hbTaskTypes.html', 'hbTaskTypes', 4);

--
-- Dumping data for table tbl_menu_type
--
INSERT INTO tbl_menu_type VALUES
  (1, 'controlMenu', 2),
  (2, 'mainMenu', 1);

--
-- Dumping data for table tbl_module_groups
--
INSERT INTO tbl_module_groups VALUES
  (5, 2, 4),
  (6, 1, 1),
  (8, 4, 1),
  (9, 5, 1),
  (10, 3, 3),
  (11, 6, 1),
  (12, 7, 1);

--
-- Dumping data for table tbl_module_roles
--

-- Table erp_db.tbl_module_roles does not contain any data (it is empty)

--
-- Dumping data for table tbl_modules
--
INSERT INTO tbl_modules VALUES
  (1, 'title_2', 'adm/UnitManager', 0, '1', 'UnitManager'),
  (2, 'title_1', 'main/MainPage', 0, '1', 'MainPage'),
  (3, 'auto_title1', 'project/projectList', 0, '1', 'projectList'),
  (4, 'auto_title5', 'project/addProject', 0, '1', 'addProject'),
  (5, 'auto_title6', 'project/inProject', 0, '1', 'inProject'),
  (6, 'auto_title7', 'adm/ProjectManager', 0, '1', 'ProjectManager'),
  (7, 'auto_title8', 'adm/hbTaskTypes', 0, '1', 'hbTaskTypes');

--
-- Dumping data for table tbl_plugins
--
INSERT INTO tbl_plugins VALUES
  (2, 'Login', 0, '1', '1', 0),
  (3, 'mainMenu', 0, '1', '1', 3600);

--
-- Dumping data for table tbl_plugins_group
--
INSERT INTO tbl_plugins_group VALUES
  (8, 2, 4),
  (17, 3, 3);

--
-- Dumping data for table tbl_plugins_roles
--

-- Table erp_db.tbl_plugins_roles does not contain any data (it is empty)

--
-- Dumping data for table tbl_project
--
INSERT INTO tbl_project VALUES
  (1, 'Тестовый проект', 1, 1, '2016-12-10 11:34:09', '&lt;p&gt;asdasd&lt;/p&gt;', '0');

--
-- Dumping data for table tbl_project_num
--
INSERT INTO tbl_project_num VALUES
  (1, 1);

--
-- Dumping data for table tbl_project_stage
--
INSERT INTO tbl_project_stage VALUES
  (1, 1, 1, 1, '2016-12-15 11:18:59', '2016-12-15 11:18:59', NULL, '2016-12-15 11:18:59', '2016-12-19 11:18:59', NULL, 'Создан автоматически при заведении проекта', 1, NULL, 1, 1),
  (2, 1, 5, 1, '2016-12-21 16:01:04', NULL, '2016-12-19 11:18:59', NULL, '2016-12-24 11:18:59', NULL, NULL, 2, NULL, 2, 5),
  (3, 1, 5, 1, '2016-12-21 16:03:28', NULL, '2016-12-24 11:18:59', NULL, '2016-12-25 11:18:59', NULL, NULL, 3, NULL, 3, 1),
  (4, 1, 5, 1, '2016-12-22 13:20:24', NULL, '2016-12-25 11:18:59', NULL, '2016-12-30 11:18:59', NULL, NULL, 4, NULL, 4, 5);

--
-- Dumping data for table tbl_roles_in_group
--
INSERT INTO tbl_roles_in_group VALUES
  (2, 1, 1);

--
-- Dumping data for table tbl_tasks
--

-- Table erp_db.tbl_tasks does not contain any data (it is empty)

--
-- Dumping data for table tbl_user
--
INSERT INTO tbl_user VALUES
  (1, 'Админ', 'Админов', 'Админович', 'admin', '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 1, '0', NULL, NULL, '2016-12-03 16:12:32', '2016-12-03 11:56:42');

--
-- Dumping data for table tbl_user_groups
--
INSERT INTO tbl_user_groups VALUES
  (1, 'IT отдел'),
  (2, 'Гости'),
  (3, 'Пользователи'),
  (4, 'Все');

--
-- Dumping data for table tbl_user_roles
--
INSERT INTO tbl_user_roles VALUES
  (1, 'Администратор системы'),
  (2, 'тестовая роль 1'),
  (3, 'тестовая роль 2'),
  (4, 'тестовая роль 3'),
  (5, 'тестовая роль 4'),
  (6, 'тестовая роль 5');

DELIMITER $$

--
-- Definition for trigger t_OnCreateNewStage
--
DROP TRIGGER IF EXISTS t_OnCreateNewStage$$
CREATE
  DEFINER = 'root'@'localhost'
TRIGGER t_OnCreateNewStage
BEFORE INSERT
  ON tbl_project_stage
FOR EACH ROW
  BEGIN
    DECLARE orderSeq int;
    SELECT COUNT(*)+1 INTO orderSeq FROM tbl_project_stage WHERE col_projectID = NEW.col_projectID;
    set new.col_seq = orderSeq;
  END
$$

DELIMITER ;

-- 
-- Restore previous SQL mode
-- 
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;

-- 
-- Enable foreign keys
-- 
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;