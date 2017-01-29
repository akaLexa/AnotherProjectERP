--
-- Script date 29.01.2017 17:26:40
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
-- Definition for table tbl_doc_group_access
--
CREATE TABLE tbl_doc_group_access (
  col_dga int(11) NOT NULL AUTO_INCREMENT,
  col_dgID int(11) DEFAULT NULL,
  col_roleID int(11) DEFAULT NULL,
  col_access char(1) DEFAULT '0' COMMENT '1 - чтение, 2 - полный доступ',
  PRIMARY KEY (col_dga),
  CONSTRAINT FK_tbl_doc_group_access_col_dg FOREIGN KEY (col_dgID)
  REFERENCES tbl_hb_doc_group (col_dgID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_doc_group_access_col_ro FOREIGN KEY (col_roleID)
  REFERENCES tbl_user_roles (col_roleID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 19
  AVG_ROW_LENGTH = 2730
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'доступы к группам документов';

--
-- Definition for table tbl_events
--
CREATE TABLE tbl_events (
  col_evID int(11) NOT NULL AUTO_INCREMENT,
  col_dateCreate timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  col_etID int(11) DEFAULT NULL COMMENT 'id эвента',
  col_object int(11) DEFAULT NULL COMMENT 'id объекта, на который идет ссылка',
  col_userID int(11) DEFAULT NULL,
  col_isTop char(1) DEFAULT '0' COMMENT 'закреплен в топе',
  col_isNoticed char(1) DEFAULT '0' COMMENT 'ознакомлен',
  col_isMailed char(1) DEFAULT '0' COMMENT 'отправлен по почте',
  col_comment text DEFAULT NULL,
  PRIMARY KEY (col_evID),
  CONSTRAINT FK_tbl_events_col_etID FOREIGN KEY (col_etID)
  REFERENCES tbl_hb_events_relation (col_erID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_events_col_userID FOREIGN KEY (col_userID)
  REFERENCES tbl_user (col_uID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 16
  AVG_ROW_LENGTH = 1092
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'эыенты';

--
-- Definition for table tbl_files
--
CREATE TABLE tbl_files (
  col_fID int(11) NOT NULL AUTO_INCREMENT,
  col_fName varchar(255) DEFAULT NULL,
  col_ext varchar(50) DEFAULT NULL COMMENT 'расширение',
  col_isFolder char(1) DEFAULT '0',
  col_parentID int(11) DEFAULT NULL,
  col_size decimal(10, 2) DEFAULT NULL COMMENT 'размер',
  col_cDate datetime DEFAULT CURRENT_TIMESTAMP,
  col_isDel char(1) DEFAULT '0',
  col_dDate datetime DEFAULT NULL,
  col_uploaderID int(11) DEFAULT NULL,
  col_deleterID int(11) DEFAULT NULL,
  col_groupID int(11) DEFAULT NULL,
  col_projectID int(11) DEFAULT NULL,
  PRIMARY KEY (col_fID),
  CONSTRAINT FK_tbl_files_col_deleterID FOREIGN KEY (col_deleterID)
  REFERENCES tbl_user (col_uID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_files_col_groupID FOREIGN KEY (col_groupID)
  REFERENCES tbl_hb_doc_group (col_dgID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_files_col_projectID FOREIGN KEY (col_projectID)
  REFERENCES tbl_project (col_projectID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_files_col_uploaderID FOREIGN KEY (col_uploaderID)
  REFERENCES tbl_user (col_uID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 1
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'файлы';

--
-- Definition for table tbl_group_roles
--
CREATE TABLE tbl_group_roles (
  col_grID int(11) NOT NULL AUTO_INCREMENT,
  col_gID int(11) DEFAULT NULL,
  col_roleID int(11) DEFAULT NULL,
  PRIMARY KEY (col_grID),
  CONSTRAINT FK_tbl_group_roles_tbl_user_groups_col_gID FOREIGN KEY (col_gID)
  REFERENCES tbl_user_groups (col_gID) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_group_roles_tbl_user_roles_col_roleID FOREIGN KEY (col_roleID)
  REFERENCES tbl_user_roles (col_roleID) ON DELETE RESTRICT ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 1
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'какие роли к какой группе относятся';

--
-- Definition for table tbl_hb_doc_group
--
CREATE TABLE tbl_hb_doc_group (
  col_dgID int(11) NOT NULL AUTO_INCREMENT,
  col_docGroupName varchar(255) DEFAULT NULL,
  col_isDel char(1) DEFAULT '0',
  PRIMARY KEY (col_dgID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 2
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'справочник групп документов';

--
-- Definition for table tbl_hb_event_state
--
CREATE TABLE tbl_hb_event_state (
  col_esID int(11) NOT NULL AUTO_INCREMENT,
  col_esName varchar(255) DEFAULT NULL,
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
  col_etID int(11) NOT NULL AUTO_INCREMENT,
  col_etName varchar(255) DEFAULT NULL,
  PRIMARY KEY (col_etID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 8
  AVG_ROW_LENGTH = 2730
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'типы событий';

--
-- Definition for table tbl_hb_events_relation
--
CREATE TABLE tbl_hb_events_relation (
  col_erID int(11) NOT NULL AUTO_INCREMENT,
  col_etID int(11) DEFAULT NULL,
  col_esID int(11) DEFAULT NULL,
  col_message text DEFAULT NULL,
  PRIMARY KEY (col_erID),
  CONSTRAINT FK_tbl_hb_events_relation_col_ FOREIGN KEY (col_etID)
  REFERENCES tbl_hb_event_type (col_etID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_hb_events_relation_col2 FOREIGN KEY (col_esID)
  REFERENCES tbl_hb_event_state (col_esID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 20
  AVG_ROW_LENGTH = 862
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'готовые эвенты';

--
-- Definition for table tbl_hb_project_stage
--
CREATE TABLE tbl_hb_project_stage (
  col_StageID int(11) NOT NULL AUTO_INCREMENT,
  col_StageName varchar(200) DEFAULT NULL,
  col_isDel char(1) DEFAULT '0',
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
  col_StatusID int(11) NOT NULL AUTO_INCREMENT,
  col_StatusName varchar(255) DEFAULT NULL,
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
  col_tttID int(11) NOT NULL AUTO_INCREMENT,
  col_tName varchar(255) DEFAULT NULL,
  col_isDel char(1) DEFAULT '0',
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
  col_id int(11) NOT NULL AUTO_INCREMENT,
  col_mtitle varchar(255) DEFAULT NULL,
  col_mtype int(11) DEFAULT NULL,
  col_link varchar(255) DEFAULT NULL,
  col_modul varchar(255) DEFAULT NULL,
  col_Seq int(11) DEFAULT NULL,
  PRIMARY KEY (col_id)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 10
  AVG_ROW_LENGTH = 4096
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

--
-- Definition for table tbl_menu_type
--
CREATE TABLE tbl_menu_type (
  col_id int(11) NOT NULL AUTO_INCREMENT,
  col_ttitle varchar(255) DEFAULT NULL,
  col_seq int(11) DEFAULT NULL,
  PRIMARY KEY (col_id)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 5
  AVG_ROW_LENGTH = 4096
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

--
-- Definition for table tbl_module_groups
--
CREATE TABLE tbl_module_groups (
  col_mgID int(11) NOT NULL AUTO_INCREMENT,
  col_modID int(11) DEFAULT NULL,
  col_gID int(11) DEFAULT NULL,
  PRIMARY KEY (col_mgID),
  CONSTRAINT FK_tbl_module_groups_col_gID FOREIGN KEY (col_gID)
  REFERENCES tbl_user_groups (col_gID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_module_groups_col_modID FOREIGN KEY (col_modID)
  REFERENCES tbl_modules (col_modID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 16
  AVG_ROW_LENGTH = 3276
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'разрешения групп к модулям';

--
-- Definition for table tbl_module_roles
--
CREATE TABLE tbl_module_roles (
  col_mrID int(11) NOT NULL AUTO_INCREMENT,
  col_modID int(11) DEFAULT NULL,
  col_roleID int(11) DEFAULT NULL,
  PRIMARY KEY (col_mrID),
  CONSTRAINT FK_tbl_module_roles_tbl_modules_col_modID FOREIGN KEY (col_modID)
  REFERENCES tbl_modules (col_modID) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_module_roles_tbl_user_roles_col_roleID FOREIGN KEY (col_roleID)
  REFERENCES tbl_user_roles (col_roleID) ON DELETE RESTRICT ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 1
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'указание ролей с правами доступа';

--
-- Definition for table tbl_modules
--
CREATE TABLE tbl_modules (
  col_modID int(11) NOT NULL AUTO_INCREMENT,
  col_title varchar(255) DEFAULT NULL COMMENT 'идентификатор названия страницы',
  col_path varchar(255) DEFAULT NULL COMMENT 'местонахождения скрипта',
  col_cache int(11) DEFAULT 0 COMMENT 'кеширование в секундах',
  col_isClass char(1) DEFAULT '1' COMMENT 'mvc или обычный скрипт',
  col_moduleName varchar(255) DEFAULT NULL,
  PRIMARY KEY (col_modID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 11
  AVG_ROW_LENGTH = 3276
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'модули системы';

--
-- Definition for table tbl_plugins
--
CREATE TABLE tbl_plugins (
  col_pID int(11) NOT NULL AUTO_INCREMENT,
  col_pluginName varchar(255) DEFAULT NULL,
  col_seq int(11) DEFAULT 0 COMMENT 'очередь загрузки',
  col_isClass char(1) DEFAULT '0',
  col_pluginState char(1) DEFAULT '0' COMMENT '0 - выключен 1- включен',
  col_cache int(11) DEFAULT 0,
  PRIMARY KEY (col_pID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 4
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

--
-- Definition for table tbl_plugins_group
--
CREATE TABLE tbl_plugins_group (
  col_pgID int(11) NOT NULL AUTO_INCREMENT,
  col_pID int(11) DEFAULT NULL,
  col_gID int(11) DEFAULT NULL,
  PRIMARY KEY (col_pgID),
  CONSTRAINT FK_tbl_plugins_group_col_gID FOREIGN KEY (col_gID)
  REFERENCES tbl_user_groups (col_gID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_plugins_group_col_pID FOREIGN KEY (col_pID)
  REFERENCES tbl_plugins (col_pID) ON DELETE NO ACTION ON UPDATE RESTRICT
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
  col_prID int(11) NOT NULL AUTO_INCREMENT,
  col_pID int(11) DEFAULT NULL,
  col_roleID int(11) DEFAULT NULL,
  PRIMARY KEY (col_prID),
  CONSTRAINT FK_tbl_plugins_role_col_pID FOREIGN KEY (col_pID)
  REFERENCES tbl_plugins (col_pID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_plugins_role_col_roleID FOREIGN KEY (col_roleID)
  REFERENCES tbl_user_roles (col_roleID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 1
  AVG_ROW_LENGTH = 16384
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

--
-- Definition for table tbl_project
--
CREATE TABLE tbl_project (
  col_projectID int(11) NOT NULL AUTO_INCREMENT,
  col_projectName varchar(200) DEFAULT NULL,
  col_pnID int(11) DEFAULT NULL,
  col_founderID int(11) DEFAULT NULL,
  col_CreateDate timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  col_Desc text DEFAULT NULL,
  col_ProjectPlanState char(1) DEFAULT '0' COMMENT '0/1 не запущен, запущен план проекта',
  PRIMARY KEY (col_projectID),
  CONSTRAINT FK_tbl_project_col_founderID FOREIGN KEY (col_founderID)
  REFERENCES tbl_user (col_uID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_project_col_pnID FOREIGN KEY (col_pnID)
  REFERENCES tbl_project_num (col_pnID) ON DELETE NO ACTION ON UPDATE RESTRICT
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
  col_pmID int(11) NOT NULL AUTO_INCREMENT,
  col_AuthorID int(11) DEFAULT NULL,
  col_text text DEFAULT NULL,
  col_dateCreate timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  col_projectID int(11) DEFAULT NULL,
  col_system char(1) DEFAULT '0',
  PRIMARY KEY (col_pmID),
  CONSTRAINT FK_tbl_project_messages_col_Au FOREIGN KEY (col_AuthorID)
  REFERENCES tbl_user (col_uID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_project_messages_col_pr FOREIGN KEY (col_projectID)
  REFERENCES tbl_project (col_projectID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 19
  AVG_ROW_LENGTH = 910
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'вкладка переписок и событий в проекте';

--
-- Definition for table tbl_project_num
--
CREATE TABLE tbl_project_num (
  col_pnID int(11) NOT NULL AUTO_INCREMENT,
  col_serNum int(11) DEFAULT 1,
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
CREATE TABLE tbl_project_stage (
  col_pstageID int(11) NOT NULL AUTO_INCREMENT,
  col_projectID int(11) DEFAULT NULL,
  col_statusID int(11) DEFAULT NULL,
  col_respID int(11) DEFAULT NULL,
  col_dateCreate timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  col_dateStart datetime DEFAULT NULL,
  col_dateStartPlan datetime DEFAULT NULL,
  col_dateEnd datetime DEFAULT NULL COMMENT 'дата окончания принятия решения',
  col_dateEndPlan datetime DEFAULT NULL,
  col_dateEndFact datetime DEFAULT NULL,
  col_comment text DEFAULT NULL,
  col_stageID int(11) DEFAULT NULL,
  col_prevStageID int(11) DEFAULT NULL COMMENT 'предыдущий статус (для возврата при отказе)',
  col_seq int(11) DEFAULT NULL COMMENT 'очередность',
  col_duration int(11) DEFAULT 1,
  PRIMARY KEY (col_pstageID),
  CONSTRAINT FK_tbl_project_stage_col_proje FOREIGN KEY (col_projectID)
  REFERENCES tbl_project (col_projectID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_project_stage_col_respI FOREIGN KEY (col_respID)
  REFERENCES tbl_user (col_uID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_project_stage_col_stage FOREIGN KEY (col_stageID)
  REFERENCES tbl_hb_project_stage (col_StageID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_project_stage_col_statu FOREIGN KEY (col_statusID)
  REFERENCES tbl_hb_status (col_StatusID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 4
  AVG_ROW_LENGTH = 5461
  CHARACTER SET utf8
  COLLATE utf8_general_ci;

--
-- Definition for table tbl_project_stage_group
--
CREATE TABLE tbl_project_stage_group (
  col_psgID int(11) NOT NULL AUTO_INCREMENT,
  col_gID int(11) DEFAULT NULL,
  PRIMARY KEY (col_psgID),
  CONSTRAINT FK_tbl_project_stage_group_col FOREIGN KEY (col_gID)
  REFERENCES tbl_user_groups (col_gID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 5
  AVG_ROW_LENGTH = 4096
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'доступ к стадиям';

--
-- Definition for table tbl_project_stage_role
--
CREATE TABLE tbl_project_stage_role (
  col_psrID int(11) NOT NULL AUTO_INCREMENT,
  col_psgID int(11) DEFAULT NULL,
  col_roleID int(11) DEFAULT NULL,
  PRIMARY KEY (col_psrID),
  CONSTRAINT FK_tbl_project_stage_role_col_ FOREIGN KEY (col_psgID)
  REFERENCES tbl_project_stage_group (col_psgID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_project_stage_role_col2 FOREIGN KEY (col_roleID)
  REFERENCES tbl_user_roles (col_roleID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 5
  AVG_ROW_LENGTH = 16384
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'доступ через группы ролей к стадиям';

--
-- Definition for table tbl_roles_in_group
--
CREATE TABLE tbl_roles_in_group (
  col_rigID int(11) NOT NULL AUTO_INCREMENT,
  col_gID int(11) DEFAULT NULL COMMENT 'группа',
  col_roleID int(11) DEFAULT NULL COMMENT 'роль',
  PRIMARY KEY (col_rigID),
  CONSTRAINT FK_tbl_roles_in_group_col_gID FOREIGN KEY (col_gID)
  REFERENCES tbl_user_groups (col_gID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_roles_in_group_col_role FOREIGN KEY (col_roleID)
  REFERENCES tbl_user_roles (col_roleID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 8
  AVG_ROW_LENGTH = 3276
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'к какой группе, какая роль принадлежит';

--
-- Definition for table tbl_tasks
--
CREATE TABLE tbl_tasks (
  col_taskID int(11) NOT NULL AUTO_INCREMENT,
  col_taskName varchar(255) DEFAULT NULL,
  col_StatusID int(11) DEFAULT NULL,
  col_initID int(11) DEFAULT NULL COMMENT 'кто назначил',
  col_respID int(11) DEFAULT NULL COMMENT 'кому назначил',
  col_curatorID int(11) DEFAULT NULL COMMENT 'кто курирует',
  col_pstageID int(11) DEFAULT NULL,
  col_taskDesc text DEFAULT NULL,
  col_createDate datetime DEFAULT NULL,
  col_startPlan datetime DEFAULT NULL,
  col_startFact datetime DEFAULT NULL,
  col_endPlan datetime DEFAULT NULL,
  col_endFact datetime DEFAULT NULL,
  col_autoStart datetime DEFAULT NULL COMMENT 'дата, когда задача автоматически будет запущена (если была в 4 статусе) ',
  col_taskDur int(11) DEFAULT 0,
  col_seq int(11) DEFAULT 1,
  col_nextID int(11) DEFAULT NULL,
  col_bonding char(1) DEFAULT '0' COMMENT '0 - нет связи  1 - после окончания с nextID 2 - запуск параллельно с nextID 3 - завершение одновременно с nextID(?)',
  col_fromPlan char(1) DEFAULT '0',
  col_continueDes text DEFAULT NULL COMMENT 'причина последнего запроса на продление',
  col_failDes text DEFAULT NULL COMMENT 'причина отказа от задачи',
  col_lateFinishDesc text DEFAULT NULL COMMENT 'причина просрочки выполнения задачи',
  PRIMARY KEY (col_taskID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 10
  AVG_ROW_LENGTH = 5461
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'задачи';

--
-- Definition for table tbl_tasks_comments
--
CREATE TABLE tbl_tasks_comments (
  col_tcID int(11) NOT NULL AUTO_INCREMENT,
  col_taskID int(11) DEFAULT NULL,
  col_UserID int(11) DEFAULT NULL,
  col_text text DEFAULT NULL,
  col_date datetime DEFAULT NULL,
  col_trigger char(1) DEFAULT '1',
  PRIMARY KEY (col_tcID),
  CONSTRAINT FK_tbl_tasks_comments_col_task FOREIGN KEY (col_taskID)
  REFERENCES tbl_tasks (col_taskID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 15
  AVG_ROW_LENGTH = 8192
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'комментраии к задаче';

--
-- Definition for table tbl_user
--
CREATE TABLE tbl_user (
  col_uID int(11) NOT NULL AUTO_INCREMENT,
  col_Name varchar(100) DEFAULT NULL COMMENT 'имя',
  col_Sername varchar(100) DEFAULT NULL COMMENT 'фамилия',
  col_Lastname varchar(100) DEFAULT NULL COMMENT 'очество',
  col_login varchar(100) DEFAULT NULL COMMENT 'логин',
  col_pwd varchar(255) DEFAULT NULL COMMENT 'пароль',
  col_roleID int(11) DEFAULT NULL COMMENT 'роль',
  col_isBaned char(1) DEFAULT '0' COMMENT 'забанен ли?',
  col_deputyID int(11) DEFAULT NULL,
  col_StartDep datetime DEFAULT NULL,
  col_banDate datetime DEFAULT NULL,
  col_regDate timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (col_uID),
  CONSTRAINT FK_tbl_user_col_roleID FOREIGN KEY (col_roleID)
  REFERENCES tbl_user_roles (col_roleID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 4
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'пользователи';

--
-- Definition for table tbl_user_groups
--
CREATE TABLE tbl_user_groups (
  col_gID int(11) NOT NULL AUTO_INCREMENT,
  col_gName varchar(250) DEFAULT NULL,
  PRIMARY KEY (col_gID)
)
  ENGINE = INNODB
  AUTO_INCREMENT = 6
  AVG_ROW_LENGTH = 3276
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'группы пользователей';

--
-- Definition for table tbl_user_roles
--
CREATE TABLE tbl_user_roles (
  col_roleID int(11) NOT NULL AUTO_INCREMENT,
  col_roleName varchar(250) DEFAULT NULL,
  col_isDel char(1) DEFAULT '0',
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
CREATE PROCEDURE sp_CalcProjectPlan (IN projectID int, IN dateStart date)
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
      FROM tbl_project_stage tps,
        tbl_hb_project_stage thps,
        tbl_hb_status ths
      WHERE tps.col_projectID = projectID
            AND tps.col_statusID = 5
            AND thps.col_StageID = tps.col_stageID
            AND ths.col_StatusID = tps.col_statusID
      ORDER BY tps.col_seq;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cu_Work;

    read_loop:
    LOOP
      FETCH cu_Work INTO currentStage, currentDuration;
      IF done THEN
        LEAVE read_loop;
      END IF;

      SET currentEndDate = DATE_ADD(dateStart, INTERVAL currentDuration DAY);
      UPDATE tbl_project_stage tps
      SET tps.col_dateStartPlan = dateStart,
        tps.col_dateEndPlan = currentEndDate
      WHERE tps.col_pstageID = currentStage;
      CALL sp_setTaskPlanQuenue(currentStage, dateStart, NULL);
      SET dateStart = currentEndDate;

    END LOOP;
    CLOSE cu_Work;
  END
$$

--
-- Definition for procedure sp_setTaskPlanQuenue
--
CREATE PROCEDURE sp_setTaskPlanQuenue (IN stageID int, IN dateStart datetime, IN relationID int)
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
      FROM tbl_tasks tt
      WHERE tt.col_pstageID = stageID
      ORDER BY tt.col_seq, tt.col_taskID;

    DECLARE taskRelationCur CURSOR FOR
      SELECT
        tt.col_taskID,
        tt.col_nextID,
        tt.col_bonding,
        tt.col_taskDur
      FROM tbl_tasks tt
      WHERE tt.col_pstageID = stageID
            AND tt.col_nextID = relationID
      ORDER BY tt.col_seq, tt.col_taskID;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    SET max_sp_recursion_depth = 255;

    IF relationID IS NULL THEN
      OPEN taskCursor;
      read_loop:
      LOOP
        FETCH taskCursor INTO curTask, curID, curType, taskDur;
        IF done THEN
          LEAVE read_loop;
        END IF;

        IF curType = 0 THEN -- простая задача
          -- помнить про то, что у простых задач очередость считается в триггере, по максимальной +1
          UPDATE tbl_tasks tt
          SET tt.col_startPlan = dateStart,
            tt.col_endPlan = DATE_ADD(dateStart, INTERVAL tt.col_taskDur DAY)
          WHERE tt.col_taskID = curTask;
          CALL sp_setTaskPlanQuenue(stageID, dateStart, curTask);
        END IF;


      END LOOP;
      CLOSE taskCursor;
    ELSE
      OPEN taskRelationCur;
      read_loop:
      LOOP
        FETCH taskRelationCur INTO curTask, curID, curType, taskDur;
        IF done THEN
          LEAVE read_loop;
        END IF;

        IF curType != 0 THEN -- на самом деле, не особо нужен, оставил на всякий случай
          SELECT
            tt1.col_seq,
            tt1.col_startPlan,
            tt1.col_endPlan INTO curSeq, planStart, planEnd
          FROM tbl_tasks tt1
          WHERE tt1.col_taskID = relationID;

          CASE
            WHEN curType = 1 THEN -- конончание - начало
            UPDATE tbl_tasks tt
            SET tt.col_seq = curSeq + 1,
              tt.col_startPlan = planEnd,
              tt.col_endPlan = DATE_ADD(planEnd, INTERVAL tt.col_taskDur DAY)
            WHERE tt.col_taskID = curTask;
            WHEN curType = 2 THEN -- начало - начало
            UPDATE tbl_tasks tt
            SET tt.col_seq = curSeq,
              tt.col_startPlan = planStart,
              tt.col_endPlan = DATE_ADD(planStart, INTERVAL tt.col_taskDur DAY)
            WHERE tt.col_taskID = curTask;
            WHEN curType = 3 THEN -- окончание - окончание
            UPDATE tbl_tasks tt
            SET tt.col_seq = curSeq,
              tt.col_startPlan = DATE_ADD(planEnd, INTERVAL -tt.col_taskDur DAY),
              tt.col_endPlan = planEnd
            WHERE tt.col_taskID = curTask;
          END CASE;

          CALL sp_setTaskPlanQuenue(stageID, dateStart, curTask);
        END IF;

      END LOOP;
      CLOSE taskRelationCur;
    END IF;
  END
$$

--
-- Definition for function f_getNextTaskSDR
--
CREATE FUNCTION f_getNextTaskSDR (stageID int)
  RETURNS int(11)
  SQL SECURITY INVOKER
READS SQL DATA
  COMMENT 'возвращает следующую СДР для задачи в плане'
  BEGIN
    RETURN (SELECT
              MAX(tt.col_seq) + 1
            FROM tbl_tasks tt
            WHERE tt.col_pstageID = stageID);
  END
$$

--
-- Definition for function f_getProjectName
--
CREATE FUNCTION f_getProjectName (projectID int)
  RETURNS varchar(255) charset utf8
  SQL SECURITY INVOKER
  BEGIN
    RETURN (SELECT
              tp.col_projectName
            FROM tbl_project tp
            WHERE tp.col_projectID = projectID);
  END
$$

--
-- Definition for function f_GetProjectNum
--
CREATE FUNCTION f_GetProjectNum (projectID int)
  RETURNS int(11)
  SQL SECURITY INVOKER
  COMMENT 'номер по id проекта'
  BEGIN

    RETURN (SELECT
              tp.col_pnID
            FROM tbl_project tp
            WHERE tp.col_projectID = projectID);
  END
$$

--
-- Definition for function f_getUserFIO
--
CREATE FUNCTION f_getUserFIO (userID int)
  RETURNS varchar(255) charset utf8
  SQL SECURITY INVOKER
READS SQL DATA
  COMMENT 'возвращает Фамилия И.О. по id'
  BEGIN

    RETURN (SELECT
              CONCAT(tu.col_Sername, ' ', COALESCE(LEFT(tu.col_Name, 1), '?'), '.', COALESCE(LEFT(tu.col_Lastname, 1), '?'), '.')
            FROM tbl_user tu
            WHERE tu.col_uID = userID);

  END
$$

--
-- Definition for function f_pushEvent
--
CREATE FUNCTION f_pushEvent (evID int, userID int)
  RETURNS int(11)
  SQL SECURITY INVOKER
MODIFIES SQL DATA
  COMMENT 'закрепить или открепить евент '
  BEGIN
    DECLARE statusEv char(1);

    SELECT
      te.col_isTop INTO statusEv
    FROM tbl_events te
    WHERE te.col_evID = evID
          AND te.col_userID = userID;

    IF statusEv = 1 THEN
      SET statusEv = 0;
    ELSE
      SET statusEv = 1;
    END IF;

    UPDATE tbl_events te
    SET te.col_isTop = statusEv
    WHERE te.col_evID = evID
          AND te.col_userID = userID;

    RETURN statusEv;
  END
$$

--
-- Definition for function f_setProjectNum
--
CREATE FUNCTION f_setProjectNum (orderNum int)
  RETURNS int(11)
  SQL SECURITY INVOKER
MODIFIES SQL DATA
  COMMENT 'получение id номера'
  BEGIN
    DECLARE numsID int DEFAULT NULL;

    IF orderNum > 0 THEN
      SELECT
        tpn.col_serNum INTO numsID
      FROM tbl_project_num tpn
      WHERE tpn.col_pnID = orderNum;
      IF numsID IS NOT NULL
         AND numsID > 0 THEN
        UPDATE tbl_project_num
        SET orderNum = numsID + 1
        WHERE col_pnID = orderNum;
        RETURN orderNum;
      END IF;
    ELSE
      INSERT INTO tbl_project_num (col_serNum)
        VALUE (1);
      RETURN LAST_INSERT_ID();
    END IF;
    RETURN 1;
  END
$$

DELIMITER ;

--
-- Dumping data for table tbl_doc_group_access
--
INSERT INTO tbl_doc_group_access VALUES
  (13, 1, 1, '2'),
  (14, 1, 2, '2'),
  (15, 1, 3, '0'),
  (16, 1, 4, '0'),
  (17, 1, 5, '0'),
  (18, 1, 6, '0');

--
-- Dumping data for table tbl_events
--
INSERT INTO tbl_events VALUES
  (1, '2016-12-31 09:43:27', 11, 1, 1, '0', '1', '0', 'Тестовый проект'),
  (2, '2016-12-31 10:00:35', 11, 1, 1, '0', '1', '0', '1:тестовое снова соовбщение...'),
  (3, '2016-12-31 10:01:21', 11, 1, 1, '0', '1', '0', '#1: еще разочек...'),
  (4, '2016-12-31 10:02:50', 11, 1, 1, '0', '1', '0', '#1 - тестовый т.т.: добавляем недобавленное...'),
  (5, '2016-12-31 10:59:20', 11, 1, 1, '0', '1', '0', 'Проект 1, написал тестовый т.т.: ы...'),
  (6, '2016-12-31 11:00:14', 11, 1, 1, '0', '1', '0', 'Проект [1], написал [тестовый т.т.]: ы х2...'),
  (7, '2017-01-02 16:24:28', 6, 8, 3, '0', '0', '0', 'тест'),
  (8, '2017-01-06 16:18:56', 16, 9, 1, '0', '1', '0', 'тестовая задача1'),
  (9, '2017-01-06 16:21:12', 16, 9, 1, '0', '1', '0', 'тестовая задача1'),
  (10, '2017-01-06 16:23:54', 16, 9, 1, '0', '1', '0', 'тестовая задача1'),
  (11, '2017-01-06 16:26:32', 16, 9, 1, '0', '1', '0', 'тестовая задача1'),
  (12, '2017-01-06 16:28:14', 16, 9, 1, '0', '1', '0', 'тестовая задача1'),
  (13, '2017-01-06 16:31:27', 16, 9, 1, '0', '1', '0', 'тестовая задача1'),
  (14, '2017-01-06 16:33:43', 16, 9, 1, '0', '1', '0', 'тестовая задача1'),
  (15, '2017-01-06 16:51:06', 18, 9, 3, '0', '0', '0', '&lt;p&gt;ыфвфы&lt;/p&gt;...');

--
-- Dumping data for table tbl_files
--

-- Table erp_db.tbl_files does not contain any data (it is empty)

--
-- Dumping data for table tbl_group_roles
--

-- Table erp_db.tbl_group_roles does not contain any data (it is empty)

--
-- Dumping data for table tbl_hb_doc_group
--
INSERT INTO tbl_hb_doc_group VALUES
  (1, 'Остальное', '0');

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
  (17, 5, 10, 'План проекта был снова запущен'),
  (18, 2, 9, ' '),
  (19, 4, 9, '  ');

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
  (8, 'auto_title9', 3, 'page/EventJournal.html', 'EventJournal', 0),
  (9, 'auto_title10', 4, 'page/tasks.html', 'tasks', 0);

--
-- Dumping data for table tbl_menu_type
--
INSERT INTO tbl_menu_type VALUES
  (1, 'controlMenu', 4),
  (2, 'mainMenu', 1),
  (3, 'evJournal', 2),
  (4, 'menuTask', 3);

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
  (13, 8, 3),
  (14, 9, 3),
  (15, 10, 3);

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
  (7, 'auto_title8', 'adm/hbTaskTypes', 0, '1', 'hbTaskTypes'),
  (8, 'auto_title9', 'main/EventJournal', 0, '1', 'EventJournal'),
  (9, 'auto_title10', 'main/tasks', 0, '1', 'tasks'),
  (10, 'auto_title11', 'main/Docs', 0, '1', 'Docs');

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
  (10, 3, 'ы х2', '2016-12-31 11:00:13', 1, '0'),
  (11, 2, 'Пользователь Админов А.А. сменил менеджера проекта с Админов А.А. на тестовый т.т.', '2017-01-28 10:52:14', 1, '1'),
  (12, 2, 'Пользователь Админов А.А. сменил менеджера проекта с тестовый т.т. на Админов А.А.', '2017-01-28 10:52:30', 1, '1'),
  (13, 2, 'Пользователь «Админов А.А.» сменил менеджера проекта с «Админов А.А.» на «тестовый т.т.»', '2017-01-28 10:53:46', 1, '1'),
  (14, 2, 'Пользователь «Админов А.А.» сменил менеджера проекта с «тестовый т.т.» на «Админов А.А.»', '2017-01-28 10:54:01', 1, '1'),
  (15, 2, 'Пользователь «Админов А.А.» сменил менеджера проекта с «Админов А.А.» на «тестовый т.т.»', '2017-01-28 10:58:16', 1, '1'),
  (16, 2, 'Пользователь «Админов А.А.» сменил менеджера проекта с «тестовый т.т.» на «Админов А.А.»', '2017-01-28 10:58:26', 1, '1'),
  (17, 2, 'Пользователь «Админов А.А.» сменил менеджера проекта с «Админов А.А.» на «тестовый т.т.»', '2017-01-29 14:52:28', 1, '1'),
  (18, 2, 'Пользователь «Админов А.А.» сменил менеджера проекта с «тестовый т.т.» на «Админов А.А.»', '2017-01-29 14:53:03', 1, '1');

--
-- Dumping data for table tbl_project_num
--
INSERT INTO tbl_project_num VALUES
  (1, 1);

--
-- Dumping data for table tbl_project_stage
--
INSERT INTO tbl_project_stage VALUES
  (1, 1, 1, 1, '2016-12-15 11:18:59', '2017-01-29 15:11:42', NULL, '2016-12-15 11:18:59', '2016-12-19 11:18:59', NULL, 'Создан автоматически при заведении проекта', 1, NULL, 1, 1),
  (2, 1, 5, 1, '2016-12-21 16:01:04', NULL, '2017-01-28 00:00:00', NULL, '2017-02-02 00:00:00', NULL, NULL, 2, NULL, 2, 5),
  (3, 1, 5, 1, '2016-12-21 16:03:28', NULL, '2017-02-02 00:00:00', NULL, '2017-02-03 00:00:00', NULL, NULL, 3, NULL, 3, 1);

--
-- Dumping data for table tbl_project_stage_group
--
INSERT INTO tbl_project_stage_group VALUES
  (1, 1),
  (2, 5),
  (3, 5),
  (4, 5);

--
-- Dumping data for table tbl_project_stage_role
--
INSERT INTO tbl_project_stage_role VALUES
  (2, 1, 1);

--
-- Dumping data for table tbl_roles_in_group
--
INSERT INTO tbl_roles_in_group VALUES
  (2, 1, 1),
  (3, 5, 3),
  (4, 5, 4),
  (5, 5, 5),
  (6, 5, 6);

--
-- Dumping data for table tbl_tasks
--
INSERT INTO tbl_tasks VALUES
  (1, 'Задача 1', 5, 1, 1, NULL, 2, NULL, '2016-12-23 10:20:32', '2017-01-28 00:00:00', NULL, '2017-01-30 00:00:00', NULL, NULL, 2, 1, NULL, '0', '0', NULL, NULL, NULL),
  (3, 'Задача 3', 5, 1, 1, NULL, 2, NULL, '2016-12-23 11:03:31', '2017-01-30 00:00:00', NULL, '2017-02-02 00:00:00', NULL, NULL, 3, 2, 1, '1', '0', NULL, NULL, NULL),
  (6, 'тест', 5, 1, 1, NULL, 2, NULL, '2016-12-24 17:41:13', '2017-02-02 00:00:00', NULL, '2017-02-03 00:00:00', NULL, NULL, 1, 3, 3, '1', '1', NULL, NULL, NULL),
  (8, 'тест', 4, 1, 3, NULL, 1, '&lt;p&gt;тестовая&lt;/p&gt;', '2017-01-02 16:24:28', NULL, '2017-01-02 16:51:04', '2017-01-08 17:00:00', NULL, '2017-01-04 16:24:28', 2, 1, NULL, '0', '0', NULL, NULL, NULL),
  (9, 'тестовая задача1', 2, 3, 1, NULL, 1, '&lt;p&gt;какое-то описание&lt;/p&gt;', '2017-01-05 11:13:44', NULL, '2017-01-06 16:33:50', '2017-01-09 17:00:00', '2017-01-06 16:33:50', '2017-01-07 11:13:44', 2, 1, NULL, '0', '0', NULL, '!!!&quot;№;', NULL);

--
-- Dumping data for table tbl_tasks_comments
--
INSERT INTO tbl_tasks_comments VALUES
  (13, 9, 1, '&lt;p&gt;цв&lt;/p&gt;', '2017-01-06 16:49:47', '1'),
  (14, 9, 1, '&lt;p&gt;ыфвфы&lt;/p&gt;', '2017-01-06 16:51:06', '1');

--
-- Dumping data for table tbl_user
--
INSERT INTO tbl_user VALUES
  (1, 'Админ', 'Админов', 'Админович', 'admin', '8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918', 1, '0', NULL, NULL, '2016-12-03 16:12:32', '2016-12-03 11:56:42'),
  (2, 'О', 'РОБ', 'Т', 'erpBot', 'somePasswordMaybeHere...', NULL, '1', NULL, NULL, '2016-12-27 12:44:09', '2016-12-27 12:43:51'),
  (3, 'тест', 'тестовый', 'тестович', 'test', '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08', 1, '0', NULL, NULL, NULL, '2016-12-31 09:27:10');

--
-- Dumping data for table tbl_user_groups
--
INSERT INTO tbl_user_groups VALUES
  (1, 'IT отдел'),
  (2, 'Гости'),
  (3, 'Пользователи'),
  (4, 'Все'),
  (5, 'Менеджеры');

--
-- Dumping data for table tbl_user_roles
--
INSERT INTO tbl_user_roles VALUES
  (1, 'Администратор системы', '0'),
  (2, 'Пользователь', '0'),
  (3, 'тестовая роль 2', '0'),
  (4, 'тестовая роль 3', '0'),
  (5, 'тестовая роль 4', '0'),
  (6, 'тестовая роль 5', '0');

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

    IF new.col_nextID IS NULL
       AND new.col_StatusID = 5 THEN
      SET new.col_bonding = 0;

      SELECT
        COALESCE(MAX(col_seq), 0) + 1 INTO orderSeq
      FROM tbl_tasks
      WHERE col_pstageID = NEW.col_pstageID
            AND col_nextID IS NULL;
      SET new.col_seq = orderSeq;

      IF orderSeq > 1 THEN
        /*SELECT col_endPlan INTO endPlan  from tbl_tasks WHERE col_pstageID = NEW.col_pstageID AND col_nextID is NULL order BY col_seq DESC limit 1; -- последняя забитая задача-родитель
        set new.col_endPlan = DATE_ADD(endPlan, INTERVAL new.col_taskDur DAY); -- отталкиваемся от ее даты завершения
        set new.col_startPlan = endPlan;*/
        SET new.col_startPlan = NULL;
        SET new.col_endPlan = NULL;
      END IF;
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
      INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
        VALUE (1, new.col_pstageID, new.col_respID, (SELECT
                                                       col_StageName
                                                     FROM tbl_hb_project_stage
                                                     WHERE col_StageID = new.col_stageID));
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
        IF new.col_curatorID IS NOT NULL
           AND new.col_curatorID != new.col_respID THEN
          INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
            VALUE (5, new.col_taskID, new.col_curatorID, new.col_taskName);
        END IF;
      END IF;

      IF new.col_respID != new.col_initID THEN
        IF new.col_StatusID = 4 THEN       -- оповещение ответственного о новой задаче
          INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
            VALUE (6, new.col_taskID, new.col_respID, new.col_taskName);
        END IF;
      END IF;
    END IF;
  END
$$

--
-- Definition for trigger onNewTComment
--
CREATE
  DEFINER = CURRENT_USER
TRIGGER onNewTComment
BEFORE INSERT
  ON tbl_tasks_comments
FOR EACH ROW
  BEGIN
    DECLARE curator int;
    DECLARE init int;
    DECLARE resp int;

    IF new.col_trigger = 1 THEN

      SELECT
        col_initID,
        col_respID,
        col_curatorID INTO init, resp, curator
      FROM tbl_tasks
      WHERE col_taskID = new.col_taskID;

      IF curator IS NOT NULL
         AND new.col_UserID != curator THEN
        INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
          VALUE (19, new.col_taskID, curator, CONCAT(LEFT(new.col_text, 50), '...'));
      END IF;

      IF new.col_UserID != init THEN
        INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
          VALUE (18, new.col_taskID, init, CONCAT(LEFT(new.col_text, 50), '...'));
      END IF;

      IF new.col_UserID != resp THEN
        INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
          VALUE (18, new.col_taskID, resp, CONCAT(LEFT(new.col_text, 50), '...'));
      END IF;

    END IF;
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
    IF old.col_statusID = 5
       AND new.col_statusID != old.col_statusID THEN
      INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
        VALUE (2, new.col_pstageID, new.col_respID, (SELECT
                                                       col_StageName
                                                     FROM tbl_hb_project_stage
                                                     WHERE col_StageID = new.col_stageID));
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
    IF new.col_nextID IS NULL
       AND new.col_StatusID = 5 THEN
      SET new.col_bonding = 0;

      SELECT
        COALESCE(MAX(col_seq), 0) + 1 INTO orderSeq
      FROM tbl_tasks
      WHERE col_pstageID = NEW.col_pstageID
            AND col_nextID IS NULL
            AND col_taskID != new.col_taskID
            AND COALESCE(new.col_seq, 0) > col_seq;

      IF orderSeq > 1 THEN
        /*SELECT col_endPlan INTO endPlan  from tbl_tasks WHERE col_pstageID = NEW.col_pstageID AND col_nextID is NULL AND col_taskID != new.col_taskID AND col_seq < new.col_seq order BY col_seq DESC limit 1; -- последняя забитая задача-родитель
        set new.col_endPlan = DATE_ADD(endPlan, INTERVAL new.col_taskDur DAY); -- отталкиваемся от ее даты завершения
        set new.col_startPlan = endPlan;*/
        SET new.col_startPlan = NULL;
        SET new.col_endPlan = NULL;
      ELSE
        SELECT
          col_dateStartPlan INTO endPlan
        FROM tbl_project_stage
        WHERE col_pstageID = new.col_pstageID;
        SET new.col_startPlan = endPlan;
        SET new.col_endPlan = DATE_ADD(endPlan, INTERVAL new.col_taskDur DAY);
      END IF;

    END IF;

    -- создание нотиса в журнал событий
    IF new.col_StatusID != 5
       AND new.col_StatusID != old.col_StatusID THEN
      -- курируемая задача
      IF new.col_curatorID IS NOT NULL
         AND new.col_curatorID != new.col_respID THEN
        IF new.col_StatusID = 1 THEN -- старт
          INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
            VALUE (5, new.col_taskID, new.col_curatorID, new.col_taskName);
        END IF;

        IF new.col_StatusID = 2 THEN -- отказ
          INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
            VALUE (13, new.col_taskID, new.col_curatorID, CONCAT(new.col_taskName, ' причина: ', new.col_failDes));
        END IF;

        IF new.col_StatusID = 3 THEN -- завершение
          INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
            VALUE (12, new.col_taskID, new.col_curatorID, new.col_taskName);
        END IF;

        IF new.col_StatusID = 4
           AND old.col_StatusID = 2 THEN
          INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
            VALUE (15, new.col_taskID, new.col_curatorID, new.col_taskName);
        END IF;
      END IF;

      -- оповещение инициатора
      IF new.col_respID != new.col_initID THEN
        IF new.col_StatusID = 1 THEN --  старт задачи
          INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
            VALUE (3, new.col_taskID, new.col_initID, new.col_taskName);
        END IF;

        IF new.col_StatusID = 2 THEN -- отказ
          INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
            VALUE (14, new.col_taskID, new.col_initID, CONCAT(new.col_taskName, ' причина: ', new.col_failDes));
        END IF;

        IF new.col_StatusID = 3 THEN -- завершение
          INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
            VALUE (4, new.col_taskID, new.col_initID, new.col_taskName);
        END IF;

      END IF;

      -- перезапуск задачи
      IF new.col_StatusID = 4
         AND old.col_StatusID = 2 THEN
        INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
          VALUE (16, new.col_taskID, new.col_respID, new.col_taskName);
      END IF;
    END IF;

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
    SELECT
      COALESCE(MAX(col_seq), 0) + 1 INTO orderSeq
    FROM tbl_project_stage
    WHERE col_projectID = NEW.col_projectID;
    SET new.col_seq = orderSeq;
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
/*!40014 SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS */;