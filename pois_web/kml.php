<?php
header('Content-Type: application/vnd.google-earth.kml+xml kml');
header('Content-Disposition: attachment; filename="test.kml"');

$username = 'asynadak_openskg';
$password = '0p3nskg!';
$database = 'asynadak_openthess';
$server = 'localhost';

$connection = mysql_connect ($server, $username, $password);
$db_selected = mysql_select_db($database, $connection);

if (!$db_selected) 
{
  die('Can\'t use db : ' . mysql_error());
}

mysql_query("SET NAMES UTF8;");

$query = 'select node.title as NAME,geoloc.field_coordinates_lat as LAT,geoloc.field_coordinates_lon as LON,addr.field_address_thoroughfare as ADDRESS,addr.field_address_postal_code as PCODE,addr.field_address_locality as CITY,taxterm.name as CATEGORY from node inner join field_data_field_coordinates as geoloc on node.nid=geoloc.entity_id inner join field_data_field_address as addr on node.nid=addr.entity_id, taxonomy_term_data as taxterm';
$result = mysql_query($query);

if (!$result) 
{
  die('Invalid query: ' . mysql_error());
}

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom">';
echo '<Document>';

  // Now iterate over all placemarks (rows)
while ($row = @mysql_fetch_assoc($result)) {

    // This writes out a placemark with some data

	echo '<Placemark>';
	echo '<name>'.$row['NAME'].'</name>';
	echo '<description>'.$row['ADDRESS'].'</description>';
	echo '<Point>';
	echo '<coordinates>'.$row['LON'].','.$row['LAT'].'</coordinates>';
	echo '</Point>';
	echo '</Placemark>';

  };

// And finish the document

echo '</Document>';
echo '</kml>';


?>
