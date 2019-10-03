All installation files required can be found in the "install files" folder.

1. To use this system you need to install excute hosts.sql on your mysql server.

2. Edit the pleskapi.conf.php and place this file wherever you want (preferably outside of the htdocs area).

3. You need to edit the init.php file to load the pleskapi.conf.php from the correct location.

4. The system should now be ready for use and you can start using the class.

Notes:

If you use the improved security, this system will use so called "secret keys". There are only 2 ways to manage these. 

1 way is by specifying the exact key in combination with the API.

The 2nd way is to browse your PSA database and find the table secret_keys where you can find all keys.

The input system can be used by calling index.php?act=input&orderid=2030 with 2030 being the sample id of course.

Only pleskapi.conf.php needs to be encoded/secured at this point.

PHP needs to be compiled with mcrypt for this system.