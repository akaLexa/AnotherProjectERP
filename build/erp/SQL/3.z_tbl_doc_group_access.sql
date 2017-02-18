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