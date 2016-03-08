# YeAPF<br>
Yet Another PHP Framework<br>
(C) 2016 Esteban D.Dortta<br>
<br>
Easy steps<br>
1) Installing and configuring<br>
Assuming your base folder is /var/www/html<br>
Create a folder under that called 'sample'<br>
Place 'includes' folder inside it<br>
Place 'configure.php' and 'yeapf.db.ini' from skel/webApp/ inside 'sample' too<br>
Give enough rights to Apache rwx on 'sample' folder<br>
Modify yeapf.db.ini<br>
Invoke 'configure.php'<br>
<br>
2) Creating an app<br>
Let's say you has a table called employees (see https://github.com/datacharmer/test_db.git)<br>
Write an 'employees.html' as this:<br>
<br>
&lt;html&gt;<br>
&nbsp;&nbsp;&lt;table&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;#for("select * from employees order by last_name, first_name")<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;tr&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&gt;&lt;b&gt;#(last_name)&lt;/b&gt; #(first_name)&lt;/td&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&gt;#(emp_no)&lt;/td&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&gt;#date(birth_date)&lt;/td&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&gt;#(gender)&lt;/td&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/tr&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;#next<br>
&nbsp;&nbsp;&lt;/table&gt;<br>
&lt;/html&gt;<br>
<br>
Now write this little script called 'employees.php'<br>
&lt;?php<br>
&nbsp;&nbsp;require_once "yeapf.php";<br>
&nbsp;&nbsp;processFile("employees.html");<br>
?&gt;<br>
<br>
Run it.<br>
