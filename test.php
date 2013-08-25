#!/bin/php
<?php
//require "defaultincludes.inc";
//global $tbl_entry,$tbl_repeat,$tbl_users,$tbl_room;
//Written by: Wang Shilong <wangsl.fnst@cn.fujitsu.com>
//Please change config before using it.Thanks.
require_once "functions_mail.inc";

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
	$check_start=(time()+15*60);
	$check_end=($check_start+1*60);
	$sql="select start_time,end_time,room_id,description,create_by".
		" from mrbs_entry where start_time<='$check_end' and start_time>$check_start";
	$result=mysql_query($sql, $con);
	if (!$result) {
		echo "NULL\n";
		return;
	}
	$row=mysql_fetch_array($result);
	while ($row)
	{
		$sql="select id,area_id,room_name from mrbs_room where area_id='$row[room_id]'";
		$res=mysql_query($sql);
		$res=mysql_fetch_array($res, MYSQL_ASSOC);
/*
		$sql_area="select * from mrbs_area where id='$res[area_id]'";
		$res_area=mysql_query($sql);
		$res_area=mysql_fetch_array($res_area, MYSQL_ASSOC);
		if ($res_area['area_name'])
			$area_out="$res_area[area_name]";
		else
			$area_out="$res[area_id]";
*/
		$msg="【会议地点】: area $res[area_id] room $res[room_name]\n";
		$tmp=date('Y-m-d H:i:s',$row['start_time']);
		$msg=$msg."【会议开始时间】 $tmp\n";
		$tmp=date('Y-m-d H:i:s',$row['end_time']);
		$msg=$msg."【会议结束时间】: $tmp\n";
		$msg=$msg."如果您不需要此次会议,请尽快登录取消，避免浪费:\n";
		$msg=$msg."http://10.167.226.104/web\n";
		echo $msg;

		$sql="select name,email from mrbs_users where name='$row[create_by]'";
		$res=mysql_query($sql, $con);
		if (!$res) {
			echo "NULL\n";
			goto skip;
		}
		$res=mysql_fetch_array($res, MYSQL_ASSOC);
		echo "email: $res[email]\n";
		//mail($res[email], "booking meeting reminder", $msg, "");
		$subject="booking meeting reminder";
		$mail="$res[email]";
		shell_exec("sh /var/www/html/web/mail.sh '$mail' '$subject' '$msg'");
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
	$check_start=(time()+25*60);
	$check_end=($check_start+1*60);
	$sql="select start_time,end_time,room_id,description,create_by".
		" from mrbs_repeat where start_time<'$check_end' and start_time>=$check_start";
	$result=mysql_query($sql, $con);
	if (!$result) {
		echo "NULL\n";
		return;
	}
	$row=mysql_fetch_array($result);
	while ($row)
	{
		$sql="select id,area_id,room_name from mrbs_room where id='$row[room_id]'";

		$res=mysql_query($sql);
		$res=mysql_fetch_array($res, MYSQL_ASSOC);
		$msg="【会议地点】: area $res[area_id] room $res[room_name]\n";
		
		$tmp=date('Y-m-d H:i:s',$row['start_time']);
		$msg=$msg."【会议开始时间】 $tmp\n";
		$tmp=date('Y-m-d H:i:s',$row['end_time']);
		$msg=$msg."【会议结束时间】: $tmp\n";
		$msg=$msg."如果您不需要此次会议,请尽快登录取消，避免浪费:\n";
		$msg=$msg."http://10.167.226.104/web\n";
		echo $msg;

		$sql="select name,email from mrbs_users where name='$row[create_by]'";
		$res=mysql_query($sql, $con);
		if (!$res) {
			echo "NULL\n";
			goto skip;
		}
		$res=mysql_fetch_array($res, MYSQL_ASSOC);
		echo "email: $res[email]\n";
		//mail($res[email], "booking meeting reminder", $msg, "");
		$subject="booking meeting reminder";
		$mail="$res[email]";
		//shell_exec("sh /var/www/html/web/mail.sh '$mail' '$subject' '$msg'");
skip:
		$row=mysql_fetch_array($result, MYSQL_ASSOC);
	}
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
