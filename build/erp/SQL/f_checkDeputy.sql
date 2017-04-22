CREATE FUNCTION f_checkDeputy(userID INT)
  RETURNS int(11)
  SQL SECURITY INVOKER
READS SQL DATA
  COMMENT 'проверка на замещение пользователя'
  BEGIN

    DECLARE uid int;
    SELECT tu.col_deputyID INTO uid FROM tbl_user tu WHERE tu.col_uID = userID;

    IF uid IS NULL THEN
      RETURN userID;
    ELSE
      RETURN uid;
    END IF;

  END