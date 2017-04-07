CREATE TABLE tbl_project_messages_interlocutor (
  col_intID int(11) NOT NULL AUTO_INCREMENT,
  col_pmID int(11) DEFAULT NULL,
  col_UserID int(11) DEFAULT NULL,
  PRIMARY KEY (col_intID),
  CONSTRAINT FK_tbl_project_messages_inter2 FOREIGN KEY (col_UserID)
  REFERENCES tbl_user (col_uID) ON DELETE NO ACTION ON UPDATE RESTRICT,
  CONSTRAINT FK_tbl_project_messages_interl FOREIGN KEY (col_pmID)
  REFERENCES tbl_project_messages (col_pmID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT = 'собеседники в беседе';