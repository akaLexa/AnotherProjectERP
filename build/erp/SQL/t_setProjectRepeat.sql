CREATE
  DEFINER = CURRENT_USER
TRIGGER t_setProjectRepeat
BEFORE INSERT
  ON tbl_project
FOR EACH ROW
  BEGIN
    DECLARE num int;
    SELECT col_serNum INTO num FROM tbl_project_num WHERE col_pnID = new.col_pnID;
    set new.col_repeat = num;
  END