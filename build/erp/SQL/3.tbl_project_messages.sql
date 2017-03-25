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
  AUTO_INCREMENT = 2
  AVG_ROW_LENGTH = 1024
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'вкладка переписок и событий в проекте';