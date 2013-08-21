#!/bin/php
<?php
//require "defaultincludes.inc";
//global $tbl_entry,$tbl_repeat,$tbl_users,$tbl_room;
//Written by: Wang Shilong <wangsl.fnst@cn.fujitsu.com>
//Please change config before using it.Thanks.

function send_setup()
{
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
	return $con;
}

//precondition: the system has guranteed that there is only one booking
//for a special point which makes it easier to process.
//simple case for booking once
function send_mrbs_entry($con)
{
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
}

//handle repeated case,this is a little complex. will deal it latter.
//now the idea is:
//Step1: select all the repeated entries whose start_time <= now_time + 10 * 60 and end_date >= now_time + 10 * 60.
//Step2: check that if there is a satified time that meets condition:
//----->calculate interval time for repeated entry due to repeated type(defined as interval)
//----->if condition (end_date - start_time) % interval <= 10 * 60 meets then we catch it.
function send_mrbs_repeat($con)
{
	echo "test mrbs repeat"	;
}

function send_close($con)
{
	mysql_close($con);
}

$con=send_setup();
send_mrbs_entry($con);
send_mrbs_repeat($con);
send_close($con);

?>
