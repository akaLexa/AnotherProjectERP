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