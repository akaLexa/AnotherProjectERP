CREATE FUNCTION f_setProjectNum(orderNum int)
  RETURNS int(11)
  SQL SECURITY INVOKER
MODIFIES SQL DATA
  COMMENT 'получение id номера'
  BEGIN
    DECLARE numsID int DEFAULT NULL;

    IF orderNum > 0 THEN
      SELECT tpn.col_serNum INTO numsID FROM tbl_project_num tpn WHERE tpn.col_pnID = orderNum;

      IF numsID IS NOT NULL AND numsID > 0 THEN
        UPDATE tbl_project_num SET col_serNum = numsID + 1 WHERE col_pnID = orderNum;
      ELSE

        INSERT INTO tbl_project_num (col_serNum) VALUE (1);
        RETURN LAST_INSERT_ID();

      END IF;
    ELSE
      INSERT INTO tbl_project_num (col_serNum) VALUE (1);
      RETURN LAST_INSERT_ID();
    END IF;
    RETURN 1;
  END