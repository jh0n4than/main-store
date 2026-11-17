SELECT 
     ident_number, 
     lastname,
     ident_number,
     firstname,
     mobile_phone,
     url_photo,
     status
FROM
    users
WHERE
     mobile_phone is not  null --and 
     --status = true 
     
order by 
    lastname ASC;

-- select * from users ;
-- UPDATED users SET url_photo = 'photos/photo_default.png  WHERE  id = 14, 
-- DELETE  FROM usrs;
-- ALTER SEQUENCE users_id_seq RESTART WITH 1;

 UPDATE users  SET status  = false WHERE id = 32;

 SELECT * FROM users;

 ALTER SEQUENCE users_id_seq  RESTART WITH 1;