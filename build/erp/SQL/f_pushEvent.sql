CREATE FUNCTION f_pushEvent (evID int, userID int)
  RETURNS int(11)
  SQL SECURITY INVOKER
MODIFIES SQL DATA
  COMMENT 'закрепить или открепить евент '
  BEGIN
    DECLARE statusEv char(1);

    SELECT
      te.col_isTop INTO statusEv
    FROM tbl_events te
    WHERE te.col_evID = evID
          AND te.col_userID = userID;

    IF statusEv = 1 THEN
      SET statusEv = 0;
    ELSE
      SET statusEv = 1;
    END IF;

    UPDATE tbl_events te
    SET te.col_isTop = statusEv
    WHERE te.col_evID = evID
          AND te.col_userID = userID;

    RETURN statusEv;
  END