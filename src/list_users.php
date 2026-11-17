<?php
/*
Use this URL to print data in browser:
http://localhost:80/main-store/src/list_users.php
*/

   include('../settings/database.php');

   // Get data frm users table
   $sql = "
      select 
          id,
          firstname || ' ' || lastname as fullname ,
          mobile_phone,
          email,
          case when status = true  then 'Active' else 'Inactive' end as status,
          url_photo 
       from 
          users
   "; 
   $res = pg_query($local_conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>
    <body>
        <table border = "2" align = "center" class="table table-striped">

            <tr>
               <th>photo</th> 
               <th>First name</th>
               <th>Email</th>
               <th>Mobile phone</th>
               <th>Status</th>
               <th>Options</th>
            </tr>
            <?php while($row = pg_fetch_assoc($res)) {?>
            <tr>
                <!-- here print data from database-->
               <td>
                 <img src="photos/photo_default.png" width="40" alt="User photo profile">
               </td>
               <td><?= $row['fullname']?></td>
               <td><?= $row['email']?></td>
               <td><?= $row['mobile_phone']?></td>
               <td><?= $row['status']?></td>
               
               <td  align="center">
                  <a href="#">
                     <img src="icons/pencil.png" alt="Edit user" width="20">
                  </a>
                  <a href="icons/search.png">
                     <img src="icons/search.png" alt="Search user" width="20">
                  </a>
                  <a href="delete_user.php?userID=<?= $row['id'] ?>">
                     <img src="icons/Trash.png" alt="Delete user" width="20">
                  </a>
               </td>

            </tr>
             <?php }?>
            
       </table>    
    </body>
</html>