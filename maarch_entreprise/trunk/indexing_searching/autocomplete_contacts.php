<?php
/*
*    Copyright 2008,2009 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief Script used by an Ajax autocompleter object to get the contacts data (from users or contacts)
*
* @file autocomplete_contacts.php
* @author Claire Figueras <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup indexing_searching_mlb
*/
require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");

$req = new request();
$req->connect();

if(empty($_REQUEST['table']))
{
	exit();
}
$table = $_REQUEST['table'];

if($table == 'users')
{
	$select = array();
	$select[$_SESSION['tablename']['users']]= array('lastname', 'firstname', 'user_id');
	$where = " (lower(lastname) like lower('%".$req->protect_string_db($_REQUEST['Input'])."%') "
		."or lower(firstname) like lower('%".$req->protect_string_db($_REQUEST['Input'])."%') "
		."or user_id like '%".$req->protect_string_db($_REQUEST['Input'])."%') and (status = 'OK' or status = 'ABS') and enabled = 'Y'";

	$other = 'order by lastname, firstname';

	$res = $req->select($select, $where, $other, $_SESSION['config']['databasetype'], 31,false,"","","", false);

	echo "<ul>\n";
	for($i=0; $i< min(count($res), 30)  ;$i++)
	{
		echo "<li>".$req->show_string($res[$i][0]['value']).', '.$req->show_string($res[$i][1]['value']).' ('.$res[$i][2]['value'].")</li>\n";
	}
	if(count($res) == 31)
	{
			echo "<li>...</li>\n";
	}
	echo "</ul>";
}
elseif($table == 'contacts')
{
	$select = array();
	$select[$_SESSION['tablename']['contacts']]= array('is_corporate_person','society', 'lastname', 'firstname', 'contact_id');
	$where = " (lower(lastname) like lower('%".$req->protect_string_db($_REQUEST['Input'])."%') "
		//."or lower(firstname) like lower('%".$req->protect_string_db($_REQUEST['Input'])."%') "
		."or lower(society) like lower('%".$req->protect_string_db($_REQUEST['Input'])."%')) ";
	
	$where .= " and (user_id = '' or user_id is null or user_id = '".$req->protect_string_db($_SESSION['user']['UserId'])."' ) and enabled = 'Y'";
	$other = 'order by society, lastname, firstname';

	$res = $req->select($select, $where, $other, $_SESSION['config']['databasetype'], 31,false,"","","", false);

	echo "<ul>\n";
	for($i=0; $i< min(count($res), 30)  ;$i++)
	{
		if($res[$i][0]['value'] == 'Y')
		{
			echo "<li>".$req->show_string($res[$i][1]['value']).' ('.$res[$i][4]['value'].")</li>\n";
		}
		else
		{
			if($res[$i][1]['value'] != '')
			{
				echo "<li>".$req->show_string($res[$i][1]['value']).', '.$req->show_string($res[$i][2]['value']).' '.$req->show_string($res[$i][3]['value'])." (".$res[$i][4]['value'].")</li>\n";
			}
			else
			{
				echo "<li>".$req->show_string($res[$i][2]['value']).', '.$req->show_string($res[$i][3]['value'])." (".$res[$i][4]['value'].")</li>\n";
			}
		}

	}
	if(count($res) == 31)
	{
			echo "<li>...</li>\n";
	}
	echo "</ul>";
}
