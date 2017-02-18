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