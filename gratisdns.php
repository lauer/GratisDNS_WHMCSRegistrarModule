<?php
/**
 * Gratisdns Register Module for WHMCS
 * Created by Jesper Grann Laursen - FairWeb ApS <jesper@fairweb.dk>
 * https://github.com/lauer/GratisDNS_WHMCSRegistrarModule
 * 
 * Read more about here
 *  - http://www.gratisdns.dk/emailordersystem/formular_0_93.txt
 *  - http://docs.whmcs.com/Registrar_Module_Developer_Docs
 * 
 * INSTALL
 *  - To be placed in /modules/registrars/gratisdns directory.
 *  - Remeber to activate and configure in Setup -> Products/services -> Domain registrars
 * 
 * 2013-01-21: Version 1.0 - Initial version, Only register domains
 *
 * Copyright FairWeb ApS 2013
 */

function gratisdns_getConfigArray() {
	$configarray = array(
	 "bestillingsemail" => array( "Type" => "text", "Size" => "20", "Description" => "Bestillings email", ),
	 "gdns_bruger" => array( "Type" => "text", "Size" => "20", "Description" => "GratisDNS brugernavn", ),
	 "gdns_template" => array( "Type" => "text", "Size" => "20", "Description" => "GratisDNS template", ),
	 "gdns_handle" => array( "Type" => "text", "Size" => "20", "Description" => "Betaler Handle", ),
	 "gdns_password" => array( "Type" => "password", "Size" => "20", "Description" => "Miniregistator MD5 kodeord", ),
	 "TestMode" => array( "Type" => "yesno", ),
	);
	return $configarray;
}

function gratisdns_RegisterDomain($params) {
	$bestillingsemail = $params["bestillingsemail"];
	$gdns_password = $params["gdns_password"]; 
	$gdns_bruger = $params["gdns_bruger"];
	$gdns_handle = $params["gdns_handle"];
	$gdns_template = $params["gdns_template"];
	$testmode = $params["TestMode"];
	$tld = $params["tld"];
	$sld = $params["sld"];
	$regperiod = $params["regperiod"];
	$nameserver1 = $params["ns1"];
	$nameserver2 = $params["ns2"];
    $nameserver3 = $params["ns3"];
    $nameserver4 = $params["ns4"];
	# Registrant Details
	$RegistrantCompany = $params["company"];
	$RegistrantFirstName = $params["firstname"];
	$RegistrantLastName = $params["lastname"];
	$RegistrantAddress1 = $params["address1"];
	$RegistrantAddress2 = $params["address2"];
	$RegistrantCity = $params["city"];
	$RegistrantStateProvince = $params["state"];
	$RegistrantPostalCode = $params["postcode"];

	if (!empty($RegistrantCompany)) {
		$RegistrantCVR = '00000000';
	} else {
		$RegistrantCVR = '0000000000';
	}

	switch ($params["country"]) {
		case 'DK':
			$RegistrantCountry = 'Danmark';
			break;
		case 'NO':
			$RegistrantCountry = 'Norway';
			break;
		case 'SE': 
			$RegistrantCountry = 'Sweden';
			break;
		case 'DE':
			$RegistrantCountry = 'Germany';
			break;
		default:
			$values['error'] = sprintf("Customer country not supported! (%s)", $params["country"]);
			return $values;
			break;
	}

	$RegistrantEmailAddress = $params["email"];
	$RegistrantPhone = !empty($params["phonenumber"]) ? $params["phonenumber"] : '00000000';
	# Admin Details
	$AdminFirstName = $params["adminfirstname"];
	$AdminLastName = $params["adminlastname"];
	$AdminAddress1 = $params["adminaddress1"];
	$AdminAddress2 = $params["adminaddress2"];
	$AdminCity = $params["admincity"];
	$AdminStateProvince = $params["adminstate"];
	$AdminPostalCode = $params["adminpostcode"];
	$AdminCountry = $params["admincountry"];
	$AdminEmailAddress = $params["adminemail"];
	$AdminPhone = $params["adminphonenumber"];
	# Put your code to register domain here
	# If error, return the error message in the value below

	$mail = "#LDnet E-mail Order system\n
#Version 0.932\n
#Dato: 16/01-2004\n
\n
#\n
# System info\n
#\n
1.1 Type..............: opret\n
1.2 Email Kontakt.....: ".$bestillingsemail."\n
1.3 Domæne............: ".$sld.".".$tld."\n
1.4 Domænekodeord.....: \n
1.5 GratisDNS bruger..: ".$gdns_bruger."\n
1.5.0 GDNS template...: ".$gdns_template."\n
1.6 MD5 Checksum......: ".md5(strtolower("&".$gdns_password."&".$sld.".".$tld."&".$gdns_handle."&"))."\n
1.7 Handelsbetingelser: ACCEPT\n
\n 
#\n
# Registrant info\n
#\n
2.1 Offentlig ejer....: Nej\n
2.2 Firma.............: ".$RegistrantCompany."\n
2.3 Navn..............: ".$RegistrantFirstName." ".$RegistrantLastName."\n
2.4 CVR/CPR...........: ".$RegistrantCVR."\n
2.5 Adresse1..........: ".$RegistrantAddress1."\n
2.6 Adresse2..........: ".$RegistrantAddress2."\n
2.7 Postnummer........: ".$RegistrantPostalCode."\n
2.8 By................: ".$RegistrantCity."\n
2.9 Land..............: ".$RegistrantCountry."\n
2.A Ejer e-mail.......: ".$RegistrantEmailAddress."\n
2.B Telefon...........: ".$RegistrantPhone."\n
2.C Fax...............: 00000000\n
2.D Fuldmægtig........: ".$gdns_handle."\n
2.E Betaler...........: ".$gdns_handle."\n
2.F Nameserver1.......: ns1.gratisdns.dk\n
2.G Nameserver2.......: ns2.gratisdns.dk\n
2.H Nameserver3.......: ns3.gratisdns.dk\n
2.I Nameserver4.......: ns4.gratisdns.dk\n
2.J Nameserver5.......: ns5.gratisdns.dk\n
2.K Nameserver6.......: \n
\n
#\n
# special felter til type sunriseDK\n
# \n
# Kun gyldig til sunrise periode for domæner med æøåäöüé\n
# tegn i perioden 01/01-2004-31/01-2004\n
\n
3.1 Kendetegn Type....:\n
3.2 Kendetegn Tekst...:\n";

	// check TLD
	if (!in_array($tld, array("dk","se","eu","com","net","org","biz","info"))) {
		$error = sprintf("TLD error: %s is not supported by GratisDNS", $tld);
		$values["error"] = $error;
		return $values;
	}

	// Ready for registration
	$headers = "From: ".$AdminFirstName." ".$AdminLastName." <".$bestillingsemail.">\n";

	if ($testmode) {
		$error = 'Running in testmode - order sent to '.$bestillingsemail;	
		mail($bestillingsemail, "Domainorder", $mail, $headers);		
	} else {
		mail("robot@ldnet.dk", "Domainorder", $mail, $headers);	
	}
	
	$values["error"] = $error;
	return $values;
}

// function gratisdns_GetNameservers($params) {
// 	$username = $params["Username"];
// 	$password = $params["Password"];
// 	$testmode = $params["TestMode"];
// 	$tld = $params["tld"];
// 	$sld = $params["sld"];
// 	# Put your code to get the nameservers here and return the values below
// 	$values["ns1"] = $nameserver1;
// 	$values["ns2"] = $nameserver2;
//     $values["ns3"] = $nameserver3;
//     $values["ns4"] = $nameserver4;
// 	# If error, return the error message in the value below
// 	$values["error"] = $error;
// 	return $values;
// }

// function gratisdns_SaveNameservers($params) {
// 	$username = $params["Username"];
// 	$password = $params["Password"];
// 	$testmode = $params["TestMode"];
// 	$tld = $params["tld"];
// 	$sld = $params["sld"];
//     $nameserver1 = $params["ns1"];
// 	$nameserver2 = $params["ns2"];
//     $nameserver3 = $params["ns3"];
// 	$nameserver4 = $params["ns4"];
// 	# Put your code to save the nameservers here
// 	# If error, return the error message in the value below
// 	$values["error"] = $error;
// 	return $values;
// }

// function gratisdns_GetDNS($params) {
//     $username = $params["Username"];
// 	$password = $params["Password"];
// 	$testmode = $params["TestMode"];
// 	$tld = $params["tld"];
// 	$sld = $params["sld"];
//     # Put your code here to get the current DNS settings - the result should be an array of hostname, record type, and address
//     $hostrecords = array();
//     $hostrecords[] = array( "hostname" => "ns1", "type" => "A", "address" => "192.168.0.1", );
//     $hostrecords[] = array( "hostname" => "ns2", "type" => "A", "address" => "192.168.0.2", );
// 	return $hostrecords;
// }

// function gratisdns_SaveDNS($params) {
//     $username = $params["Username"];
// 	$password = $params["Password"];
// 	$testmode = $params["TestMode"];
// 	$tld = $params["tld"];
// 	$sld = $params["sld"];
//     # Loop through the submitted records
// 	foreach ($params["dnsrecords"] AS $key=>$values) {
// 		$hostname = $values["hostname"];
// 		$type = $values["type"];
// 		$address = $values["address"];
// 		# Add your code to update the record here
// 	}
//     # If error, return the error message in the value below
// 	$values["error"] = $Enom->Values["Err1"];
// 	return $values;
// }



// function gratisdns_RenewDomain($params) {
// 	$username = $params["Username"];
// 	$password = $params["Password"];
// 	$testmode = $params["TestMode"];
// 	$tld = $params["tld"];
// 	$sld = $params["sld"];
// 	$regperiod = $params["regperiod"];
// 	# Put your code to renew domain here
// 	# If error, return the error message in the value below
// 	$values["error"] = $error;
// 	return $values;
#}
?>