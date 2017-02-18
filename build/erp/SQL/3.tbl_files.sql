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
  AUTO_INCREMENT = 2
  AVG_ROW_LENGTH = 16384
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'файлы';