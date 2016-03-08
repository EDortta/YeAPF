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

&lt;html&gt;
&nbsp;&nbsp;&lt;table&gt;
&nbsp;&nbsp;&nbsp;&nbsp;#for("select * from employees order by last_name, first_name")
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;tr&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&gt;&lt;b&gt;#(last_name)&lt;/b&gt; #(first_name)&lt;/td&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&gt;#(emp_no)&lt;/td&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&gt;#date(birth_date)&lt;/td&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&gt;#(gender)&lt;/td&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/tr&gt;
&nbsp;&nbsp;&nbsp;&nbsp;#next
&nbsp;&nbsp;&lt;/table&gt;
&lt;/html&gt;

Now write this little script called 'employees.php'
&lt;?php
&nbsp;&nbsp;require_once "yeapf.php";
&nbsp;&nbsp;processFile("employees.html");
?&gt;

Run it.
