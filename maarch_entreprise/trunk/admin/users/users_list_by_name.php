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
* @brief List of users for autocompletion
*
*
* @file
* @author  Laurent Giovannoni <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

$db = new dbquery();
$db->connect();
$db->query(
    "select lastname as tag from ".$_SESSION['tablename']['users']
    . " where ("
        . "lower(lastname) like lower('".$_REQUEST['what']."%') "
        . " or lower(user_id) like lower('".$_REQUEST['what']."%') "
    . ") and status <> 'DEL'"
    . " order by lastname"
);

$listArray = array();
while($line = $db->fetch_object())
{
    array_push($listArray, $line->tag);
}
echo "<ul>\n";
$authViewList = 0;
$flagAuthView = false;
foreach($listArray as $what)
{
    if(isset($authViewList ) && $authViewList>= 10)
    {
        $flagAuthView = true;
    }
    //if(stripos($what, $_REQUEST['what']) === 0)
    //{
        echo "<li>".$what."</li>\n";
        if($flagAuthView)
        {
            echo "<li>...</li>\n";
            break;
        }
        $authViewList++;
    //}
}
echo "</ul>";
