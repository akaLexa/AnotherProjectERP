CREATE
  DEFINER = CURRENT_USER
TRIGGER onNewTComment
BEFORE INSERT
  ON tbl_tasks_comments
FOR EACH ROW
  BEGIN
    DECLARE curator int;
    DECLARE init int;
    DECLARE resp int;

    IF new.col_trigger = 1 THEN

      SELECT
        col_initID,
        col_respID,
        col_curatorID INTO init, resp, curator
      FROM tbl_tasks
      WHERE col_taskID = new.col_taskID;

      IF curator IS NOT NULL
         AND new.col_UserID != curator THEN
        INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
          VALUE (19, new.col_taskID, curator, CONCAT(LEFT(new.col_text, 50), '...'));
      END IF;

      IF new.col_UserID != init THEN
        INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
          VALUE (18, new.col_taskID, init, CONCAT(LEFT(new.col_text, 50), '...'));
      END IF;

      IF new.col_UserID != resp THEN
        INSERT INTO tbl_events (col_etID, col_object, col_userID, col_comment)
          VALUE (18, new.col_taskID, resp, CONCAT(LEFT(new.col_text, 50), '...'));
      END IF;

    END IF;
  END