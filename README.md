# YeAPF
Yet Another PHP Framework
(C) 2016 Esteban D.Dortta

Easy steps
1) Installing and configuring
Assuming your base folder is /var/www/html
Create a folder under that called 'sample'
Place 'includes' folder inside it
Place 'configure.php' and 'yeapf.db.ini' from skel/webApp/ inside 'sample' too
Give enough rights to Apache rwx on 'sample' folder
Modify yeapf.db.ini
Invoke 'configure.php'

2) Creating an app
Let's say you has a table called employees (see https://github.com/datacharmer/test_db.git)
Write an 'employees.html' as this:

<html>
  <table>
    #for("select * from employees order by last_name, first_name")
      <tr>
        <td><b>#(last_name)</b> #(first_name)</td>
        <td>#(emp_no)</td>
        <td>#date(birth_date)</td>
        <td>#(gender)</td>
      </tr>
    #next
  </table>
</html>

Now write this little script called 'employees.php'
<?php
  require_once "yeapf.php";
  processFile("employees.html");
?>

Run it.
