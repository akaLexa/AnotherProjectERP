-- Script date 31.12.2016 18:19:20
-- Server version: 5.5.5-10.1.17-MariaDB
-- Client version: 4.1
--


--
-- Definition for database erp_db
--
DROP DATABASE IF EXISTS erp_db;
CREATE DATABASE erp_db
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

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
-- Definition for table tbl_hb_event_state
--
CREATE TABLE tbl_hb_event_state (
  col_esID INT(11) NOT NULL AUTO_INCREMENT,
  col_esName VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (col_esID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 11
  AVG_ROW_LENGTH = 2048
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'статусы эвентов';

--
-- Definition for table tbl_hb_event_type
--
CREATE TABLE tbl_hb_event_type (
  col_etID INT(11) NOT NULL AUTO_INCREMENT,
  col_etName VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (col_etID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 8
  AVG_ROW_LENGTH = 2730
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'типы событий';

--
-- Definition for table tbl_hb_project_stage
--
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
CREATE TABLE tbl_hb_task_types (
  col_tttID INT(11) NOT NULL AUTO_INCREMENT,
  col_tName VARCHAR(255) DEFAULT NULL,
  col_isDel CHAR(1) DEFAULT '0',
  PRIMARY KEY (col_tttID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 4
  AVG_ROW_LENGTH = 8192
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'справочник типовых названий задач';

--
-- Definition for table tbl_menu
--
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
  AUTO_INCREMENT = 9
  AVG_ROW_LENGTH = 3276
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

--
-- Definition for table tbl_menu_type
--
CREATE TABLE tbl_menu_type (
  col_id INT(11) NOT NULL AUTO_INCREMENT,
  col_ttitle VARCHAR(255) DEFAULT NULL,
  col_seq INT(11) DEFAULT NULL,
  PRIMARY KEY (col_id)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 5
  AVG_ROW_LENGTH = 5461
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

--
-- Definition for table tbl_modules
--
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
  AUTO_INCREMENT = 9
  AVG_ROW_LENGTH = 2730
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'модули системы';

--
-- Definition for table tbl_plugins
--
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
-- Definition for table tbl_project_num
--
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
-- Definition for table tbl_tasks
--
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
  col_seq INT(11) DEFAULT 1,
  col_nextID INT(11) DEFAULT NULL,
  col_bonding CHAR(1) DEFAULT '0' COMMENT '0 - нет связи  1 - после окончания с nextID 2 - запуск параллельно с nextID 3 - завершение одновременно с nextID(?)',
  col_fromPlan CHAR(1) DEFAULT '0',
  col_continueDes TEXT DEFAULT NULL COMMENT 'причина последнего запроса на продление',
  col_failDes TEXT DEFAULT NULL COMMENT 'причина отказа от задачи',
  col_lateFinishDesc TEXT DEFAULT NULL COMMENT 'причина просрочки выполнения задачи',
  PRIMARY KEY (col_taskID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 7
  AVG_ROW_LENGTH = 5461
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'задачи';

--
-- Definition for table tbl_user_groups
--
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

--
-- Definition for table tbl_group_roles
--
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
-- Definition for table tbl_hb_events_relation
--
CREATE TABLE tbl_hb_events_relation (
  col_erID INT(11) NOT NULL AUTO_INCREMENT,
  col_etID INT(11) DEFAULT NULL,
  col_esID INT(11) DEFAULT NULL,
  col_message TEXT DEFAULT NULL,
  PRIMARY KEY (col_erID),
  CONSTRAINT FK_tbl_hb_events_relation_col_ FOREIGN KEY (col_etID)
  REFERENCES tbl_hb_event_type(col_etID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_hb_events_relation_col2 FOREIGN KEY (col_esID)
  REFERENCES tbl_hb_event_state(col_esID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 18
  AVG_ROW_LENGTH = 963
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'готовые эвенты';

--
-- Definition for table tbl_module_groups
--
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
  AUTO_INCREMENT = 14
  AVG_ROW_LENGTH = 2730
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'разрешения групп к модулям';

--
-- Definition for table tbl_module_roles
--
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
-- Definition for table tbl_plugins_group
--
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
-- Definition for table tbl_roles_in_group
--
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
-- Definition for table tbl_user
--
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
  AUTO_INCREMENT = 4
  AVG_ROW_LENGTH = 16384
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'пользователи';

--
-- Definition for table tbl_events
--
CREATE TABLE tbl_events (
  col_evID INT(11) NOT NULL AUTO_INCREMENT,
  col_dateCreate TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  col_etID INT(11) DEFAULT NULL COMMENT 'id эвента',
  col_object INT(11) DEFAULT NULL COMMENT 'id объекта, на который идет ссылка',
  col_userID INT(11) DEFAULT NULL,
  col_isTop CHAR(1) DEFAULT '0' COMMENT 'закреплен в топе',
  col_isNoticed CHAR(1) DEFAULT '0' COMMENT 'ознакомлен',
  col_isMailed CHAR(1) DEFAULT '0' COMMENT 'отправлен по почте',
  col_comment TEXT DEFAULT NULL,
  PRIMARY KEY (col_evID),
  CONSTRAINT FK_tbl_events_col_etID FOREIGN KEY (col_etID)
  REFERENCES tbl_hb_events_relation(col_erID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_events_col_userID FOREIGN KEY (col_userID)
  REFERENCES tbl_user(col_uID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 7
  AVG_ROW_LENGTH = 2730
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'эыенты';

--
-- Definition for table tbl_project
--
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
-- Definition for table tbl_project_messages
--
CREATE TABLE tbl_project_messages (
  col_pmID INT(11) NOT NULL AUTO_INCREMENT,
  col_AuthorID INT(11) DEFAULT NULL,
  col_text TEXT DEFAULT NULL,
  col_dateCreate TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  col_projectID INT(11) DEFAULT NULL,
  col_system CHAR(1) DEFAULT '0',
  PRIMARY KEY (col_pmID),
  CONSTRAINT FK_tbl_project_messages_col_Au FOREIGN KEY (col_AuthorID)
  REFERENCES tbl_user(col_uID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_project_messages_col_pr FOREIGN KEY (col_projectID)
  REFERENCES tbl_project(col_projectID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 11
  AVG_ROW_LENGTH = 1638
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'вкладка переписок и событий в проекте';

--
-- Definition for table tbl_project_stage
--
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
  AUTO_INCREMENT = 4
  AVG_ROW_LENGTH = 4096
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

DELIMITER $$

--
-- Definition for procedure sp_CalcProjectPlan
--
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
      CALL sp_setTaskPlanQuenue(currentStage,dateStart,null);
      set dateStart = currentEndDate;

    END LOOP;
    CLOSE cu_Work;
  END
$$

--
-- Definition for procedure sp_setTaskPlanQuenue
--
CREATE PROCEDURE sp_setTaskPlanQuenue(IN stageID INT, IN dateStart DATETIME, IN relationID INT)
  SQL SECURITY INVOKER
MODIFIES SQL DATA
  COMMENT 'Рассчет планового запуска задач по стадии'
  BEGIN
    DECLARE curSeq int; -- очередность выпоплнения
    DECLARE curID int; -- текущий полученный перент
    DECLARE curType char(1); -- тип связи
    DECLARE curTask int;
    DECLARE taskDur int;
    DECLARE planStart datetime;
    DECLARE planEnd datetime;
    DECLARE done int DEFAULT FALSE;

    DECLARE taskCursor CURSOR FOR
      SELECT
        tt.col_taskID,
        tt.col_nextID,
        tt.col_bonding,
        tt.col_taskDur
      FROM
        tbl_tasks tt
      WHERE
        tt.col_pstageID = stageID
      ORDER BY
        tt.col_seq, tt.col_taskID;

    DECLARE taskRelationCur CURSOR FOR
      SELECT
        tt.col_taskID,
        tt.col_nextID,
        tt.col_bonding,
        tt.col_taskDur
      FROM
        tbl_tasks tt
      WHERE
        tt.col_pstageID = stageID
        AND tt.col_nextID = relationID
      ORDER BY
        tt.col_seq, tt.col_taskID;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    SET max_sp_recursion_depth=255;

    IF relationID is NULL THEN
      OPEN taskCursor;
      read_loop:
      LOOP
        FETCH taskCursor INTO curTask, curID,curType,taskDur;
        IF done
        THEN
          LEAVE read_loop;
        END IF;

        IF curType = 0 THEN -- простая задача
          -- помнить про то, что у простых задач очередость считается в триггере, по максимальной +1
          UPDATE tbl_tasks tt set tt.col_startPlan = dateStart, tt.col_endPlan = DATE_ADD(dateStart,INTERVAL tt.col_taskDur DAY)  WHERE tt.col_taskID = curTask;
          CALL sp_setTaskPlanQuenue(stageID,dateStart,curTask);
        END IF;


      END LOOP;
      CLOSE taskCursor;
    ELSE
      OPEN taskRelationCur;
      read_loop:
      LOOP
        FETCH taskRelationCur INTO curTask, curID,curType,taskDur;
        IF done
        THEN
          LEAVE read_loop;
        END IF;

        IF curType != 0 THEN -- на самом деле, не особо нужен, оставил на всякий случай
          SELECT tt1.col_seq, tt1.col_startPlan, tt1.col_endPlan INTO curSeq,planStart,planEnd FROM tbl_tasks tt1 WHERE tt1.col_taskID = relationID;

          CASE
            WHEN curType = 1 THEN -- конончание - начало
            UPDATE tbl_tasks tt set tt.col_seq = curSeq + 1, tt.col_startPlan = planEnd, tt.col_endPlan =  DATE_ADD(planEnd,INTERVAL tt.col_taskDur DAY)  WHERE tt.col_taskID = curTask;
            WHEN curType = 2 THEN -- начало - начало
            UPDATE tbl_tasks tt set tt.col_seq = curSeq, tt.col_startPlan = planStart, tt.col_endPlan =  DATE_ADD(planStart,INTERVAL tt.col_taskDur DAY)  WHERE tt.col_taskID = curTask;
            WHEN curType = 3 THEN -- окончание - окончание
            UPDATE tbl_tasks tt set tt.col_seq = curSeq, tt.col_startPlan = DATE_ADD(planEnd,INTERVAL - tt.col_taskDur DAY), tt.col_endPlan =  planEnd  WHERE tt.col_taskID = curTask;
          END CASE;

          CALL sp_setTaskPlanQuenue(stageID,dateStart,curTask);
        END IF;

      END LOOP;
      CLOSE taskRelationCur;
    END IF;
  END
$$

--
-- Definition for function f_getNextTaskSDR
--
CREATE FUNCTION f_getNextTaskSDR(stageID INT)
  RETURNS int(11)
  SQL SECURITY INVOKER
READS SQL DATA
  COMMENT 'возвращает следующую СДР для задачи в плане'
  BEGIN
    RETURN (SELECT MAX(tt.col_seq) + 1 FROM tbl_tasks tt WHERE tt.col_pstageID = stageID);
  END
$$

--
-- Definition for function f_getProjectName
--
CREATE FUNCTION f_getProjectName(projectID INT)
  RETURNS varchar(255) CHARSET utf8
  SQL SECURITY INVOKER
  BEGIN
    RETURN (SELECT tp.col_projectName FROM tbl_project tp WHERE tp.col_projectID = projectID);
  END
$$

--
-- Definition for function f_GetProjectNum
--
CREATE FUNCTION f_GetProjectNum(projectID INT)
  RETURNS int(11)
  SQL SECURITY INVOKER
  COMMENT 'номер по id проекта'
  BEGIN

    RETURN (SELECT tp.col_pnID FROM tbl_project tp WHERE tp.col_projectID = projectID);
  END
$$

--
-- Definition for function f_getUserFIO
--
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
-- Definition for function f_pushEvent
--
CREATE FUNCTION f_pushEvent(evID INT, userID INT)
  RETURNS int(11)
  SQL SECURITY INVOKER
MODIFIES SQL DATA
  COMMENT 'закрепить или открепить евент '
  BEGIN
    DECLARE statusEv char(1);

    SELECT te.col_isTop INTO statusEv FROM tbl_events te WHERE te.col_evID = evID AND te.col_userID = userID;

    IF statusEv = 1 THEN
      set statusEv = 0;
    ELSE
      set statusEv = 1;
    end IF;

    UPDATE tbl_events te set te.col_isTop = statusEv WHERE te.col_evID = evID AND te.col_userID = userID;

    RETURN statusEv;
  END
$$

--
-- Definition for function f_setProjectNum
--
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
-- Dumping data for table tbl_hb_event_state
--
INSERT INTO tbl_hb_event_state VALUES
  (1, 'Запуск'),
  (2, 'Завершение'),
  (3, 'Получение'),
  (4, 'Отказ'),
  (5, 'Остановка'),
  (6, 'Новая стадия'),
  (8, 'Принятие решения'),
  (9, 'Новое сообщение'),
  (10, 'Перезапуск');

--
-- Dumping data for table tbl_hb_event_type
--
INSERT INTO tbl_hb_event_type VALUES
  (1, 'Проект'),
  (2, 'Задача'),
  (3, 'Новость'),
  (4, 'Курируемая задача'),
  (5, 'План проекта'),
  (6, 'Задача из плана проекта'),
  (7, 'Стадия из плана проекта');

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
  (7, 'auto_title8', 1, 'page/hbTaskTypes.html', 'hbTaskTypes', 4),
  (8, 'auto_title9', 3, 'page/EventJournal.html', 'EventJournal', 0);

--
-- Dumping data for table tbl_menu_type
--
INSERT INTO tbl_menu_type VALUES
  (1, 'controlMenu', 3),
  (2, 'mainMenu', 1),
  (3, 'evJournal', 2);

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
  (7, 'auto_title8', 'adm/hbTaskTypes', 0, '1', 'hbTaskTypes'),
  (8, 'auto_title9', 'main/EventJournal', 0, '1', 'EventJournal');

--
-- Dumping data for table tbl_plugins
--
INSERT INTO tbl_plugins VALUES
  (2, 'Login', 0, '1', '1', 0),
  (3, 'mainMenu', 0, '1', '1', 3600);

--
-- Dumping data for table tbl_project_num
--
INSERT INTO tbl_project_num VALUES
  (1, 1);

--
-- Dumping data for table tbl_tasks
--
INSERT INTO tbl_tasks VALUES
  (1, 'Задача 1', 5, 1, 1, NULL, 2, NULL, '2016-12-23 10:20:32', '2016-12-24 00:00:00', NULL, '2016-12-26 00:00:00', NULL, NULL, 2, 1, NULL, '0', '0', NULL, NULL, NULL),
  (3, 'Задача 3', 5, 1, 1, NULL, 2, NULL, '2016-12-23 11:03:31', '2016-12-26 00:00:00', NULL, '2016-12-29 00:00:00', NULL, NULL, 3, 2, 1, '1', '0', NULL, NULL, NULL),
  (6, 'тест', 5, 1, 1, NULL, 2, NULL, '2016-12-24 17:41:13', '2016-12-29 00:00:00', NULL, '2016-12-30 00:00:00', NULL, NULL, 1, 3, 3, '1', '1', NULL, NULL, NULL);

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
  (2, 'Пользователь'),
  (3, 'тестовая роль 2'),
  (4, 'тестовая роль 3'),
  (5, 'тестовая роль 4'),
  (6, 'тестовая роль 5');

--
-- Dumping data for table tbl_group_roles
--

-- Table erp_db.tbl_group_roles does not contain any data (it is empty)

--
-- Dumping data for table tbl_hb_events_relation
--
INSERT INTO tbl_hb_events_relation VALUES
  (1, 1, 6, 'Проект перешел на новую стадию'),
  (2, 7, 1, 'Запущена стадия из плана проекта '),
  (3, 2, 1, 'Было принято решение о запуске задачи'),
  (4, 2, 2, 'Задача завершена'),
  (5, 4, 1, 'Была запущена курируемая задача'),
  (6, 2, 8, 'Получена новая задача, требуется принять решение'),
  (7, 6, 1, 'Была запущена задача из плана проекта'),
  (8, 3, 3, 'Была получена новость'),
  (9, 5, 1, 'План проекта был запущен'),
  (10, 5, 5, 'План проекта был остановлен'),
  (11, 1, 9, 'В переписке по проекту появилось новое сообщение'),
  (12, 4, 2, 'Курируемая задача была завершена'),
  (13, 4, 4, 'Отказ от курируемой задачи'),
  (14, 2, 4, 'Поставленная задача была отклонена'),
  (15, 4, 10, 'Перезапущена курируемая задача'),
  (16, 2, 10, 'Задача была перезапущена'),
  (17, 5, 10, 'План проекта был снова запущен');

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
  (12, 7, 1),
  (13, 8, 3);

--
-- Dumping data for table tbl_module_roles
--

-- Table erp_db.tbl_module_roles does not contain any data (it is empty)

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
-- Dumping data for table tbl_roles_in_group
--
INSERT INTO tbl_roles_in_group VALUES
  (2, 1, 1);

--
-- Dumping data for table tbl_user
--
INSERT INTO tbl_user VALUES
  (1, 'Админ', 'Админов', 'Админович', 'admin', '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 1, '0', NULL, NULL, '2016-12-03 16:12:32', '2016-12-03 11:56:42'),
  (2, 'О', 'РОБ', 'Т', 'erpBot', 'somePasswordMaybeHere...', NULL, '1', NULL, NULL, '2016-12-27 12:44:09', '2016-12-27 12:43:51'),
  (3, 'тест', 'тестовый', 'тестович', 'test', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', 1, '0', NULL, NULL, NULL, '2016-12-31 09:27:10');

--
-- Dumping data for table tbl_events
--
INSERT INTO tbl_events VALUES
  (1, '2016-12-31 09:43:27', 11, 1, 1, '0', '1', '0', 'Тестовый проект'),
  (2, '2016-12-31 10:00:35', 11, 1, 1, '0', '1', '0', '1:тестовое снова соовбщение...'),
  (3, '2016-12-31 10:01:21', 11, 1, 1, '0', '1', '0', '#1: еще разочек...'),
  (4, '2016-12-31 10:02:50', 11, 1, 1, '0', '1', '0', '#1 - тестовый т.т.: добавляем недобавленное...'),
  (5, '2016-12-31 10:59:20', 11, 1, 1, '0', '1', '0', 'Проект 1, написал тестовый т.т.: ы...'),
  (6, '2016-12-31 11:00:14', 11, 1, 1, '0', '1', '0', 'Проект [1], написал [тестовый т.т.]: ы х2...');

--
-- Dumping data for table tbl_project
--
INSERT INTO tbl_project VALUES
  (1, 'Тестовый проект', 1, 1, '2016-12-10 11:34:09', '&lt;p&gt;asdasd&lt;/p&gt;', '0');

--
-- Dumping data for table tbl_project_messages
--
INSERT INTO tbl_project_messages VALUES
  (1, 1, 'тестовый заход', '2016-12-27 12:28:14', 1, '0'),
  (2, 1, 'еще одно тестовое сообщение...\r\nну... на всякий случай...', '2016-12-27 12:46:56', 1, '0'),
  (3, 2, 'тестовое системное сообщение', '2016-12-27 13:18:00', 1, '1'),
  (4, 3, 'рас рас двас двас', '2016-12-31 09:43:27', 1, '0'),
  (5, 3, 'тестовое снова соовбщение', '2016-12-31 09:59:29', 1, '0'),
  (6, 3, 'тестовое снова соовбщение', '2016-12-31 10:00:35', 1, '0'),
  (7, 3, 'еще разочек', '2016-12-31 10:01:21', 1, '0'),
  (8, 3, 'добавляем недобавленное', '2016-12-31 10:02:49', 1, '0'),
  (9, 3, 'ы', '2016-12-31 10:59:20', 1, '0'),
  (10, 3, 'ы х2', '2016-12-31 11:00:13', 1, '0');

--
-- Dumping data for table tbl_project_stage
--
INSERT INTO tbl_project_stage VALUES
  (1, 1, 1, 1, '2016-12-15 11:18:59', '2016-12-15 11:18:59', NULL, '2016-12-15 11:18:59', '2016-12-19 11:18:59', NULL, 'Создан автоматически при заведении проекта', 1, NULL, 1, 1),
  (2, 1, 5, 1, '2016-12-21 16:01:04', NULL, '2016-12-24 00:00:00', NULL, '2016-12-29 00:00:00', NULL, NULL, 2, NULL, 2, 5),
  (3, 1, 5, 1, '2016-12-21 16:03:28', NULL, '2016-12-29 00:00:00', NULL, '2016-12-30 00:00:00', NULL, NULL, 3, NULL, 3, 1);

DELIMITER $$

--
-- Definition for trigger onCreateNewTask
--
CREATE
  DEFINER = CURRENT_USER
TRIGGER onCreateNewTask
BEFORE INSERT
  ON tbl_tasks
FOR EACH ROW
  BEGIN
    DECLARE orderSeq int DEFAULT 0;
    DECLARE endPlan datetime;

    /*
    выставление СДР у задачи без связи
    рассчет даты старта/завершения в случае, если это не 1 задача
    */

    IF new.col_nextID IS NULL THEN
      set new.col_bonding = 0;

      SELECT COALESCE(MAX(col_seq),0) + 1 INTO orderSeq FROM tbl_tasks WHERE col_pstageID = NEW.col_pstageID AND col_nextID is null;
      set new.col_seq = orderSeq;

      IF orderSeq > 1 THEN
        /*SELECT col_endPlan INTO endPlan  from tbl_tasks WHERE col_pstageID = NEW.col_pstageID AND col_nextID is NULL order BY col_seq DESC limit 1; -- последняя забитая задача-родитель
        set new.col_endPlan = DATE_ADD(endPlan, INTERVAL new.col_taskDur DAY); -- отталкиваемся от ее даты завершения
        set new.col_startPlan = endPlan;*/
        set new.col_startPlan = NULL;
        set new.col_endPlan = NULL;
      end IF;
    END IF;
  END
$$

--
-- Definition for trigger onCreateStageNotice
--
CREATE
  DEFINER = CURRENT_USER
TRIGGER onCreateStageNotice
AFTER INSERT
  ON tbl_project_stage
FOR EACH ROW
  BEGIN
    IF new.col_statusID != 5 THEN
      INSERT INTO tbl_events (col_etID,col_object,col_userID,col_comment) VALUE (1,new.col_pstageID,new.col_respID, (SELECT col_StageName FROM tbl_hb_project_stage WHERE col_StageID = new.col_stageID));
    END IF;
  END
$$

--
-- Definition for trigger onCreateTaskNotice
--
CREATE
  DEFINER = CURRENT_USER
TRIGGER onCreateTaskNotice
AFTER INSERT
  ON tbl_tasks
FOR EACH ROW
  BEGIN
    IF new.col_StatusID != 5 THEN

      IF new.col_StatusID = 1 THEN
        -- оповещение куратора о задаче
        IF new.col_curatorID IS NOT NULL AND new.col_curatorID != new.col_respID THEN
          INSERT INTO tbl_events (col_etID,col_object,col_userID,col_comment) VALUE (5,new.col_taskID,new.col_curatorID, new.col_taskName);
        end if;
      end IF;

      IF new.col_respID != new.col_initID THEN
        IF new.col_StatusID = 4 THEN       -- оповещение ответственного о новой задаче
          INSERT INTO tbl_events (col_etID,col_object,col_userID,col_comment) VALUE (6,new.col_taskID,new.col_respID, new.col_taskName);
        end IF;
      end if;
    end if;
  END
$$

--
-- Definition for trigger onStageUpdateNotice
--
CREATE
  DEFINER = CURRENT_USER
TRIGGER onStageUpdateNotice
AFTER UPDATE
  ON tbl_project_stage
FOR EACH ROW
  BEGIN
    IF old.col_statusID = 5 AND new.col_statusID != old.col_statusID THEN
      INSERT INTO tbl_events (col_etID,col_object,col_userID,col_comment) VALUE (2,new.col_pstageID,new.col_respID, (SELECT col_StageName FROM tbl_hb_project_stage WHERE col_StageID = new.col_stageID));
    END IF;
  END
$$

--
-- Definition for trigger onUpdateTask
--
CREATE
  DEFINER = CURRENT_USER
TRIGGER onUpdateTask
BEFORE UPDATE
  ON tbl_tasks
FOR EACH ROW
  BEGIN
    DECLARE orderSeq int DEFAULT 0;
    DECLARE endPlan datetime;

    /*
    выставление СДР у задачи без связи
    рассчет даты старта/завершения в случае, если это не 1 задача
    */
    IF new.col_nextID IS NULL THEN
      set new.col_bonding = 0;

      SELECT COALESCE(MAX(col_seq),0) + 1 INTO orderSeq FROM tbl_tasks WHERE col_pstageID = NEW.col_pstageID AND col_nextID is NULL AND col_taskID != new.col_taskID AND COALESCE(new.col_seq,0)> col_seq;

      IF orderSeq > 1 THEN
        /*SELECT col_endPlan INTO endPlan  from tbl_tasks WHERE col_pstageID = NEW.col_pstageID AND col_nextID is NULL AND col_taskID != new.col_taskID AND col_seq < new.col_seq order BY col_seq DESC limit 1; -- последняя забитая задача-родитель
        set new.col_endPlan = DATE_ADD(endPlan, INTERVAL new.col_taskDur DAY); -- отталкиваемся от ее даты завершения
        set new.col_startPlan = endPlan;*/
        set new.col_startPlan = NULL;
        set new.col_endPlan = NULL;
      ELSE
        SELECT col_dateStartPlan INTO endPlan  FROM tbl_project_stage WHERE col_pstageID = new.col_pstageID;
        set new.col_startPlan = endPlan;
        set new.col_endPlan = DATE_ADD(endPlan, INTERVAL new.col_taskDur DAY);
      end IF;

    END IF;

    -- создание нотиса в журнал событий
    IF new.col_StatusID != 5 AND new.col_StatusID != old.col_StatusID THEN
      -- курируемая задача
      IF new.col_curatorID IS NOT NULL AND new.col_curatorID != new.col_respID THEN
        IF new.col_StatusID = 1 THEN -- старт
          INSERT INTO tbl_events (col_etID,col_object,col_userID,col_comment) VALUE (5,new.col_taskID,new.col_curatorID, new.col_taskName);
        end IF;

        IF new.col_StatusID = 2 THEN -- отказ
          INSERT INTO tbl_events (col_etID,col_object,col_userID,col_comment) VALUE (13,new.col_taskID,new.col_curatorID, CONCAT(new.col_taskName,' причина: ',new.col_failDes));
        end IF;

        IF new.col_StatusID = 3 THEN -- завершение
          INSERT INTO tbl_events (col_etID,col_object,col_userID,col_comment) VALUE (12,new.col_taskID,new.col_curatorID, new.col_taskName);
        end IF;

        IF new.col_StatusID = 4 AND old.col_StatusID = 2 THEN
          INSERT INTO tbl_events (col_etID,col_object,col_userID,col_comment) VALUE (15,new.col_taskID,new.col_curatorID, new.col_taskName);
        end IF;
      end if;

      -- оповещение инициатора
      IF new.col_respID != new.col_initID THEN
        IF new.col_StatusID = 1 THEN --  старт задачи
          INSERT INTO tbl_events (col_etID,col_object,col_userID,col_comment) VALUE (3,new.col_taskID,new.col_initID, new.col_taskName);
        END IF;

        IF new.col_StatusID = 2 THEN -- отказ
          INSERT INTO tbl_events (col_etID,col_object,col_userID,col_comment) VALUE (14,new.col_taskID,new.col_initID, CONCAT(new.col_taskName,' причина: ',new.col_failDes));
        END IF;

        IF new.col_StatusID = 3 THEN -- завершение
          INSERT INTO tbl_events (col_etID,col_object,col_userID,col_comment) VALUE (4,new.col_taskID,new.col_initID, new.col_taskName);
        end IF;

      end if;

      -- перезапуск задачи
      IF new.col_StatusID = 4 AND old.col_StatusID = 2 THEN
        INSERT INTO tbl_events (col_etID,col_object,col_userID,col_comment) VALUE (16,new.col_taskID,new.col_respID, new.col_taskName);
      end IF;
    end IF;

  END
$$

--
-- Definition for trigger t_OnCreateNewStage
--
CREATE
  DEFINER = CURRENT_USER
TRIGGER t_OnCreateNewStage
BEFORE INSERT
  ON tbl_project_stage
FOR EACH ROW
  BEGIN
    DECLARE orderSeq int;
    SELECT COALESCE(MAX(col_seq),0)+1 INTO orderSeq FROM tbl_project_stage WHERE col_projectID = NEW.col_projectID;
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