CREATE
  DEFINER = CURRENT_USER
TRIGGER deletePlanStagePos
BEFORE DELETE
  ON tbl_project_plan_name
FOR EACH ROW
  BEGIN
    DELETE FROM tbl_project_plan_list WHERE col_ppnID = old.col_ppnID;
  END