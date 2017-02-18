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
  AUTO_INCREMENT = 27
  AVG_ROW_LENGTH = 1489
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'комментраии к задаче';