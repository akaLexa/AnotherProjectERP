CREATE
  DEFINER = CURRENT_USER
TRIGGER onCreateStageNotice
AFTER INSERT
  ON tbl_project_stage
FOR EACH ROW
  BEGIN
    IF new.col_statusID != 5 THEN
      INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
        VALUE (1, new.col_projectID, new.col_respID, (SELECT
                                                        col_StageName
                                                      FROM tbl_hb_project_stage
                                                      WHERE col_StageID = new.col_stageID));
    END IF;
  END