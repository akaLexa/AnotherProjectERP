CREATE
  DEFINER = CURRENT_USER
TRIGGER t_OnCreateNewStage
BEFORE INSERT
  ON tbl_project_stage
FOR EACH ROW
  BEGIN
    DECLARE orderSeq int;
    SELECT
      COALESCE(MAX(col_seq), 0) + 1 INTO orderSeq
    FROM tbl_project_stage
    WHERE col_projectID = NEW.col_projectID;
    SET new.col_seq = orderSeq;
  END