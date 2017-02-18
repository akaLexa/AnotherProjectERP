CREATE FUNCTION f_getUserFIO (userID int)
  RETURNS varchar(255) charset utf8
  SQL SECURITY INVOKER
READS SQL DATA
  COMMENT 'возвращает Фамилия И.О. по id'
  BEGIN

    RETURN (SELECT
              CONCAT(tu.col_Sername, ' ', COALESCE(LEFT(tu.col_Name, 1), '?'), '.', COALESCE(LEFT(tu.col_Lastname, 1), '?'), '.')
            FROM tbl_user tu
            WHERE tu.col_uID = userID);

  END