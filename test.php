#!/bin/php
<?php
//require "defaultincludes.inc";
//global $tbl_entry,$tbl_repeat,$tbl_users,$tbl_room;
//Written by: Wang Shilong <wangsl.fnst@cn.fujitsu.com>
//Please change config before using it.Thanks.
$con=mysql_connect("localhost","root","123456");
if (!$con)
  	die('Could not connect: ' . mysql_error());
else
	echo "connect mysql successful\n";
mysql_select_db("my_db", $con);
if ($con)
	echo "connect successul\n";
else
  	die('Could not select: ' . mysql_error());

function send_mrbs_entry()
{
	echo "hello world\n";
}

send_mrbs_entry();
//$now_time=(time()+10*60);
$now_time=0;
$sql="select start_time,end_time,room_id,description,create_by".
	" from mrbs_entry where mrbs_entry.start_time>=0";
$result=mysql_query($sql, $con);
if (!$result) {
	echo "NULL\n";
	return;
}
$row=mysql_fetch_array($result);
//print_r($row);
while ($row)
{
	$sql="select id,area_id from mrbs_room where id='$row[room_id]'";
	$res=mysql_query($sql);
	$res=mysql_fetch_array($res, MYSQL_ASSOC);
	echo "roomid: $res[id], area_id: $res[area_id]\n";
	echo "start_time: $row[start_time], end_time: $row[end_time]\n";

	$sql="select name,email from mrbs_users where name='$row[create_by]'";
	$res=mysql_query($sql, $con);
	if (!$res) {
		echo "NULL\n";
		goto skip;
	}
	$res=mysql_fetch_array($res, MYSQL_ASSOC);
	echo "email: $res[email]\n";
	//mail("public", "Test mail", "Fucking the world!", "headers");
skip:
	$row=mysql_fetch_array($result, MYSQL_ASSOC);
}
mysql_close($con);
?>
