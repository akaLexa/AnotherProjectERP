CREATE TABLE tbl_user (
  col_uID int(11) NOT NULL AUTO_INCREMENT,
  col_Name varchar(100) DEFAULT NULL COMMENT 'имя',
  col_Sername varchar(100) DEFAULT NULL COMMENT 'фамилия',
  col_Lastname varchar(100) DEFAULT NULL COMMENT 'очество',
  col_login varchar(100) DEFAULT NULL COMMENT 'логин',
  col_pwd varchar(255) DEFAULT NULL COMMENT 'пароль',
  col_roleID int(11) DEFAULT NULL COMMENT 'роль',
  col_isBaned char(1) DEFAULT '0' COMMENT 'забанен ли?',
  col_deputyID int(11) DEFAULT NULL COMMENT 'кем замещается',
  col_StartDep datetime DEFAULT NULL COMMENT 'дата начала замещения',
  col_banDate datetime DEFAULT NULL COMMENT 'Дата блокировки',
  col_regDate timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'дата создания учетки',
  col_workPhone varchar(255) DEFAULT NULL,
  col_privatePhone varchar(255) DEFAULT NULL,
  col_workMail varchar(255) DEFAULT NULL,
  col_privateMail varchar(255) DEFAULT NULL,
  PRIMARY KEY (col_uID),
  CONSTRAINT FK_tbl_user_col_roleID FOREIGN KEY (col_roleID)
  REFERENCES tbl_user_roles (col_roleID) ON DELETE NO ACTION ON UPDATE RESTRICT
)
  ENGINE = INNODB
  AUTO_INCREMENT = 4
  CHARACTER SET utf8
  COLLATE utf8_general_ci
  COMMENT = 'пользователи';