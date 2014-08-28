<?php
/*
*    Copyright 2014 Maarch
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
*
*
* @file
* @author <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

require_once 'core' . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR
    . 'class_request.php';
$db = new dbquery();
$db->connect();

if (isset($_GET['mode']) && $_GET['mode'] == 'up') {
	$extra = ' AND contact_id = '.$_SESSION['contact']['current_contact_id'].' and ca_id = '.$_SESSION['contact']['current_address_id'];
} else if (isset($_GET['contactid']) && $_GET['contactid'] <> '' && isset($_GET['addressid']) && $_GET['addressid'] <> ''){
	$extra = ' AND contact_id = '.$_GET['contactid'].' and ca_id = '.$_GET['addressid'];
} 
else {
	$extra = ' ORDER BY creation_date DESC limit 1';
}

$db->query("SELECT is_corporate_person, 
					contact_lastname, 
					contact_firstname, 
					society, 
					society_short, 
					contact_id, 
					ca_id, 
					lastname,
					firstname,
					address_num,
					address_street,
					address_town,
					address_postal_code,
					creation_date,
					update_date 
			FROM view_contacts 
			WHERE user_id = '". $_SESSION['user']['UserId'] . "' " . $extra);

$res = $db->fetch_object();

$address = '';
$address = $db->protect_string_db($res->address_num) . ' ' . $db->protect_string_db($res->address_street) . ' ' . $db->protect_string_db($res->address_postal_code) . ' ' . strtoupper($db->protect_string_db($res->address_town));

if($res->is_corporate_person == 'N') {
	$contact = $db->protect_string_db($res->contact_lastname) . ' ' . $db->protect_string_db($res->contact_firstname);
	if($res->society_short <> '') {
		$contact .= ' (' . $db->protect_string_db($res->society_short) . ')';
	} else if($res->society <> '') {
		$contact .= ' (' . $db->protect_string_db($res->society) . ')';
	}

} else {
	$contact = $db->protect_string_db($res->society);
	if($res->society_short <> '') {
		$contact .= ' (' . $db->protect_string_db($res->society_short) . ')';
	}
}

if ($res->lastname <> '' || $res->firstname <> '') {
	$contact .= ' - ' . $db->protect_string_db($res->lastname) . ' ' . $db->protect_string_db($res->firstname);
}
if (!empty($address)) {
	$contact .= ', ' . $address;
}

$contactId = $res->contact_id;
$addressId = $res->ca_id;

echo "{ status: 1, contactName: '" . $contact . "', contactId: '" . $contactId . "', addressId: '" . $addressId . "'}";
exit;