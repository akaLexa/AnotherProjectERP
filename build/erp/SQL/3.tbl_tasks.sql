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
  AUTO_INCREMENT = 16
  AVG_ROW_LENGTH = 1820
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'задачи';