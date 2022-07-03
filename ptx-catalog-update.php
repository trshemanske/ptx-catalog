<!-- https://www.w3schools.com/php/php_form_complete.asp -->


<!DOCTYPE HTML>
<html lang="en-US">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">


  <style>
  .alert {color: #FF0000; font-size: 18pt;}
  .error {color: #FF0000;}

  body {
    font-family: Arial, Helvetica, sans-serif;
    position: relative;
    max-width: 904px;  /* To match PreTeXt */
    margin: 0 auto 0 auto;  /* centered content */
    padding: 0;
    box-sizing: border-box;
  }

  /* Style the header */
  .header {
    background-color:  hsl(192, 98%, 23%);
    padding:1px;
    margin-bottom: 15px;
    text-align: center;
    font-size: 20px;
    color: white;
  }

  .form-button {
    height: 24px;
  }
  /* Container for flexboxes */
  section {
    display: -webkit-flex;
    display: flex;
  }

  /* Responsive layout - makes the menu and the content (inside the section) sit on top of each other instead of next to each other */
  @media (max-width: 600px) {
    section {
      -webkit-flex-direction: column;
      flex-direction: column;
    }
  }

</style>
</head>

<!-- General Layout
First Pass:  if ($whichpass == 0)
Display form and prepopulate with existing values in database

Second/Third pass:  if ($whichpass == 1 and $validated == 0)
Check for required fields.  Flag errors and force a resubmit; otherwise validate

if ($whichpass == 1 or $validated == 1)
Write all data to file in Mysql format for updating database
-->


<body>
  <?php
  /* Start (resume) session which pushed the XML id to this page */
  session_start();
  $xml_id = $_SESSION['xml_id'];
  //  echo "Session id is ".session_id();
  //  echo "<br>XML id  " . $xml_id;

  function safehtml($text) {
    $text = trim($text);
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
  }

  function safehtml_decode($text) {
    $text = trim($text);
    return htmlspecialchars_decode($text, ENT_QUOTES, 'UTF-8');
  }

  function stripslahes_safehtml($raw_text) {
    $new_text = trim($raw_text);
    $new_text = stripslashes($new_text);
    $new_text = htmlspecialchars($new_text, ENT_QUOTES, 'UTF-8');
    return $new_text;
  }

  $reqd = "<span class='alert'>*</span>";

  /* Database access information */
  $servername = "localhost";
  $username = "ptx";
  $password = "ptx_db_pw";
  $dbname = "ptx_catalog";

  // Create connection
  $connx = new mysqli($servername, $username, $password, $dbname);

  /* Pull in all the needed database information */
  $sql = "SELECT * FROM projects  WHERE project_xml_id = '$xml_id'";
  $projects = $connx->query($sql);

  $sql = "SELECT * FROM subjects";
  $subjects = $connx->query($sql);

  $sql = "SELECT * FROM levels";
  $levels = $connx->query($sql);

  $sql = "SELECT * FROM phases";
  $phases = $connx->query($sql);

  $sql = "SELECT * FROM features";
  $features = $connx->query($sql);

  $sql = "SELECT * FROM licenses";
  $licenses = $connx->query($sql);

  $sql = "SELECT * FROM recognition";
  $recognitions = $connx->query($sql);

  $sql = "SELECT * FROM coauthors WHERE coauthors_project_id = '$xml_id'";
  $coauthors = $connx->query($sql);

  // Should be exactly one row for projects
  $project = ($projects->fetch_assoc());

  ?>



  <?php
  /* First pass  Display values currently in database */
  if ($_SESSION['whichpass'] == 0) {
    $_SESSION['validated'] = 0;


    $display_project_title = $project['project_title'];
    $display_project_subtitle = $project['project_subtitle'];
    $display_project_subject_id = $project['project_subject_id'];
    $display_project_target_level_id = $project['project_target_level_id'];
    $display_project_phase_id = $project['project_phase_id'];
    /* Features handled with old and new badges */
    $display_project_recognition_code=$project['project_recognition_code'];


    $display_project_award_id = $project['project_award_id'];
    $display_project_license_code = $project['project_license_code'];
    $display_project_license_variant = $project['project_license_variant'];
    $display_project_license_version = $project['project_license_version'];

    $display_project_publication_year = $project['project_publication_year'];
    $display_project_price_amount = $project['project_price_amount'];
    $display_project_price_currency = $project['project_price_currency'];

    $display_project_description_onesentence = $project['project_description_onesentence'];
    $display_project_description_full = $project['project_description_full'];

    $display_project_landing_URL = $project['project_landing_URL'];
    $display_project_source_URL = $project['project_source_URL'];
    $display_project_html_URL = $project['project_html_URL'];
    $display_project_pdf_URL = $project['project_pdf_URL'];
    $display_project_print_URL = $project['project_print_URL'];
    $display_project_ancillary_1 = $project['project_ancillary_1'];
    $display_project_ancillary_2 = $project['project_ancillary_2'];

    $projectbadges = $project['project_features'];
    // $projectbadges is a string of 0s and 1s
    for ($i=1; $i <= strlen($projectbadges); $i++) {
      $oldbadges[$i] = substr($projectbadges,$i-1,1);
      $newbadges[$i] = 0;
    }

    // Badge Names as indexed array
    /*    $f_i=1;
    $featureIndexed = [];
    foreach ($features as $feature) {
    $featureIndexed[$f_i] = $feature['feature_name'];
    $f_i += 1;
  }
  */

  /* Define ptx_catalog database variables and set to empty values  */
  $TitleErr = $Desc1Err = $DescFullErr = "";

}
else{ /*whichpass =1 (so form submitted at least once) */
  $display_project_title = $_POST['project_title'];
  $display_project_subtitle = $_POST['project_subtitle'];
  $display_project_subject_id = $_POST['project_subject_id'];
  $display_project_target_level_id = $_POST['project_target_level_id'];
  $display_project_phase_id = $_POST['project_phase_id'];
  /* Features handled with old and new badges */
  $display_project_recognition_code = $_POST['project_recognition_code'];


  $display_project_award_id = $_POST['project_award_id'];
  $display_project_license_code = $_POST['project_license_code'];
  $display_project_license_variant = $_POST['project_license_variant'];
  $display_project_license_version = $_POST['project_license_version'];
  $display_project_publication_year = $_POST['project_publication_year'];
  if (empty($display_project_publication_year)){$display_project_publication_year=date("Y");}

  $display_project_price_amount = $_POST['project_price_amount'];
  if (empty($display_project_price_amount)){$display_project_price_amount = 0;}

  $display_project_price_currency = $_POST['project_price_currency'];
  $display_project_description_onesentence = $_POST['project_description_onesentence'];
  $display_project_description_full = $_POST['project_description_full'];

  $display_project_landing_URL = $_POST['project_landing_URL'];
  $display_project_source_URL = $_POST['project_source_URL'];
  $display_project_html_URL = $_POST['project_html_URL'];
  $display_project_pdf_URL = $_POST['project_pdf_URL'];
  $display_project_print_URL = $_POST['project_print_URL'];
  $display_project_ancillary_1 = $_POST['project_ancillary_1'];
  $display_project_ancillary_2 = $_POST['project_ancillary_2'];

  $newbadges = $_POST['newbadges'];
  $oldbadges = array_fill(1,strlen($project['project_features']),0);
  for ($i=0; $i < count($newbadges); $i++) {
    $oldbadges[$newbadges[$i]] = 1;
  }

  if ($display_project_title == ""){
    $TitleErr = "<br><span class = \"error\">Project Title is required</span>";
  }
  else {
    $TitleErr = "";
  }
  if ($display_project_description_onesentence == ""){
    $Desc1Err = "     <span class = \"error\">One sentence project description is required</span>";
  }
  else {
    $Desc1Err = "";
  }
  if ($display_project_description_full == ""){
    $DescFullErr = "     <span class = \"error\">Project description is required</span>";
  }
  else {
    $DescFullErr = "";
  }

/* Validate if no missing required fields */
  if ($TitleErr == "" and $Desc1Err == "" and $DescFullErr == ""){
    $_SESSION['validated'] = 1;
  }

} /* End of which pass if/then */

// Badge Names as indexed array
$f_i=1;
$featureIndexed = [];
foreach ($features as $feature) {
  $featureIndexed[$f_i] = $feature['feature_name'];
  $f_i += 1;
}
?>

<div class="header">
  <h2>PreTeXt Catalog Update Page</h2>
  <p><?php echo $reqd ?> <span class="error">required field</span></p>
  <p style="display:flex; justify-content: space-between; margin: 10px; font-size: 14px;">
    <span>XML id: <?php echo $xml_id ?></span>
    <span>Database: <?php echo $dbname ?></span>
  </p>
</div>


<?php
if ($_SESSION['validated'] == 0) {
  $whichpass = $_SESSION['whichpass'];
//  $outstr = "Which pass is: ".$whichpass." <br>";


  $outstr = "   <form method=\"post\" action=\"". htmlspecialchars($_SERVER['PHP_SELF']) . "\">\n";

  $outstr .= "     <label>Project Title".$reqd.": &nbsp;</label>\n";
  $outstr .= "     <input type=\"text\" name=\"project_title\"  value=\"". $display_project_title . "\" size=\"80\">";
  $outstr .=      $TitleErr . "<br><br><br>\n";
  $outstr .= "     <label>Subtitle: &nbsp;</label>\n";
  $outstr .= "     <input type=\"text\" name=\"project_subtitle\"  value=\"" . $display_project_subtitle . "\" size=\"80\"><br><br><br>\n";

  $outstr .= "     <label>Subject".$reqd.": &nbsp;</label>\n";
  $outstr .= "     <select name=\"project_subject_id\"><br>\n";
  foreach ($subjects as $subject) {
    if ($display_project_subject_id == $subject['subject_id']) {$sstr = " selected";} else {$sstr = "";}
    $outstr .= "        <option value=\"". $subject['subject_id'] . "\""  . $sstr.">". $subject['subject_name']."</option><br><br>\n";
  }
  $outstr .= "     </select>\n\n";


  $outstr .= "     <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Target Level".$reqd.": &nbsp;</label>\n";
  $outstr .= "     <select id=\"level_id\"  name=\"project_target_level_id\"><br>\n";
  foreach ($levels as $level) {
    if ($display_project_target_level_id == $level['level_id']) {$sstr = " selected";} else {$sstr = "";}
    $outstr .= "        <option value=\"". $level['level_id']  . "\""  . $sstr.">". $level['level_name']."</option><br>\n";
  }
  $outstr .= "     </select>\n\n";

  $outstr .= "     <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Phase".$reqd.": &nbsp;</label>\n";
  $outstr .= "     <select id=\"phase_id\"  name=\"project_phase_id\"><br>\n";
  foreach ($phases as $phase) {
    if ($display_project_phase_id == $phase['phase_id']) {$sstr = " selected";} else {$sstr = "";}
/*    $outstr .= "          <label>".$featureIndexed[$i]."&emsp;&emsp;</label>\n"; */
    $outstr .= "        <option value=\"". $phase['phase_id'] . "\""  . $sstr.">". $phase['phase_name']. "</option><br>\n";
  }
  $outstr .= "     </select><br><br><br>\n";
  echo $outstr;
  ?>


  <?php
  /* Features and badges */

  $outstr = "    <label>Features:&nbsp;</label>\n";
  $outstr .= "      <table>\n";

  for ($i=1; $i <= strlen($project['project_features']); $i++) {
    if ($i % 4 == 1){$outstr .= "       <tr> \n";}
    if ($oldbadges[$i] == "1") {$cstr = " checked";} else {$cstr = "";}
    $outstr .= "        <td>\n";
    $outstr .= "          <input type=\"checkbox\" name=\"newbadges[]\" value=\"".$i ."\"" .$cstr ." >\n";
    $outstr .= "          <label>".$featureIndexed[$i]."&emsp;&emsp;</label>\n";
    $outstr .= "        </td>\n";
    if ($i % 4 == 0){$outstr .= "       </tr> \n";}
  }
  if (strlen($project['project_features']) %4 != 0){
    $outstr .= "      </tr><br><br>\n";
  }
  $outstr .= "     </table><br><br>\n\n";

  /* Recognitions and Awards */
  $outstr .= "     <label>Recognition code: &nbsp;</label>\n";
  $outstr .= "     <input type=\"text\" name=\"project_recognition_code\"  value=\"" . $display_project_recognition_code . "\" size=\"20\">\n";
  $outstr .= "     <label>&emsp;&emsp;Award id: &nbsp;</label>\n";
  $outstr .= "     <input type=\"text\" name=\"project_award_id\"  value=\"" . $display_project_award_id . "\" placeholder=\"(none currently in database)\" size=\"20\">\n";
  $outstr .= "     <br><br><br>\n";

  /*  License Information */

  $outstr .= "     <label>License".$reqd.": &nbsp;</label>\n";
  $outstr .= "     <select id=\"license_id\"  name=\"project_license_code\"><br>\n";
  foreach ($licenses as $license) {
    if ($display_project_license_code == $license['license_code']) {$sstr = " selected";} else {$sstr = "";}
    $outstr .= "        <option value=\"". $license['license_code'] ."\"" . $sstr .">". $license['license_name']. "</option><br>\n";
  }
  $outstr .= "     </select><br><br><br>\n\n";


  $outstr .= "     <label>License Variant: &nbsp;</label>\n";
  $outstr .= "     <input type=\"text\" name=\"project_license_variant\"  value=\"" . $display_project_license_variant . "\" size=\"20\">\n";
  $outstr .= "     <label>&emsp;&emsp;License Version: &nbsp;</label>\n";
  $outstr .= "     <input type=\"text\" name=\"project_license_version\"  value=\"" . $display_project_license_version . "\" size=\"20\">\n";
  $outstr .= "     <br><br><br>\n\n";

  /* Publication and Price Information */
  $outstr .= "     <label>Publication Year: &nbsp;</label>\n";
  $outstr .= "     <input type=\"text\" name=\"project_publication_year\"  value=\"" . $display_project_publication_year . "\" size=\"4\">\n\n";
  $outstr .= "     <label>&emsp;&emsp;Price amount: &nbsp;</label>\n";
  $outstr .= "     <input type=\"number\" name=\"project_price_amount\"  min=\"0\" max=\"999.99\" step=\".01\" value=\"" . $display_project_price_amount . "\" size=\"20\">\n\n";
  $outstr .= "     <label>&emsp;&emsp;Currency: &nbsp;</label>\n";
  $outstr .= "     <input type=\"text\" name=\"project_price_currency\"  placeholder=\"(e.g., US Dollars or Euros)\"";
  $outstr .= "     value=\"".$display_project_price_currency . "\" size=\"20\"><br><br><br>\n\n";

  /* Project Description */
  $safedesc = $display_project_description_onesentence;
  $outstr .= "     <label>Project Description (one sentence)".$reqd.": &nbsp;</label>\n";
  $outstr .=      $Desc1Err . "<br><br>\n";
  $outstr .= "     <textarea name=\"project_description_onesentence\" rows=\"4\" cols=\"80\">$safedesc</textarea>";
  $outstr .= "     <br><br><br>\n\n";

  $safedesc = $display_project_description_full;
  $outstr .= "     <label>Project Desription Full (HTML allowed)".$reqd.": &nbsp;</label>\n";
  $outstr .=      $DescFullErr . "<br><br>\n";
  $outstr .= "     <textarea name=\"project_description_full\" rows=\"20\" cols=\"80\">$safedesc</textarea><br>\n";
  $outstr .= "     <br><br><br>\n\n";

  /* URLs */

  $outstr .= "     <label><b>URLs:</b></label><br>\n\n";

  $outstr .= "     <table>\n       <tr>\n";
  $outstr .= "         <td>Landing: &nbsp;</td>\n";
  $outstr .= "         <td><input type=\"text\" name=\"project_landing_URL\"  value=\"" . $display_project_landing_URL . "\" size=\"90\"></td>\n       </tr>\n";
  $outstr .= "       <tr>\n         <td>Source: &nbsp;</td>\n";
  $outstr .= "         <td><input type=\"text\" name=\"project_source_URL\"  value=\"" . $display_project_source_URL . "\" size=\"90\"></td>\n      </tr>\n";
  $outstr .= "      <tr>\n         <td>HTML: &nbsp;</td>\n";
  $outstr .= "         <td><input type=\"text\" name=\"project_html_URL\"  value=\"" . $display_project_html_URL . "\" size=\"90\"></td>\n      </tr>\n";
  $outstr .= "      <tr>\n        <td>PDF: &nbsp;</td>\n";
  $outstr .= "        <td><input type=\"text\" name=\"project_pdf_URL\"  value=\"" . $display_project_pdf_URL . "\" size=\"90\"></td>\n      </tr>\n";
  $outstr .= "      <tr>\n        <td>Print: &nbsp;</td>\n";
  $outstr .= "        <td><input type=\"text\" name=\"project_print_URL\"  value=\"" . $display_project_print_URL . "\" size=\"90\"></td>\n      </tr><br>\n";
  $outstr .= "      <tr>\n        <td>Ancillary 1: &nbsp;</td>\n";
  $outstr .= "        <td><input type=\"text\" name=\"project_ancillary_1\"  value=\"" . $display_project_ancillary_1 . "\" size=\"90\"></td>\n      </tr><br>\n";
  $outstr .= "      <tr>\n        <td>Ancillary 2: &nbsp;</td>\n";
  $outstr .= "        <td><input type=\"text\" name=\"project_ancillary_2\"  value=\"" . $display_project_ancillary_2 . "\" size=\"90\"></td>\n      </tr><br>\n";
  $outstr .= "     </table><br><br>\n\n";



  $outstr .= "     <label><b>Coauthors:</b></label><br>\n";
  foreach ($coauthors as $coauthor) {
    $cid = $coauthor['coauthors_id'];
    $outstr .= "     <input type=\"hidden\" name=\"coauthors_id\" value=\"". $cid. "\">\n\n";

    if ($_SESSION['whichpass'] == 0){
      $display_coauthors_name = $coauthor['coauthors_displayname'];
      $display_coauthors_url = $coauthor['coauthors_url'];
      $display_coauthors_email = $coauthor['coauthors_email'];
      $display_coauthors_affiliation = $coauthor['coauthors_affiliation'];
      $display_coauthors_affiliation_2 = $coauthor['coauthors_affiliation_2'];
    }
    else{
      $display_coauthors_name = $_POST['coauthors_displayname-'.$cid];
      $display_coauthors_url = $_POST['coauthors_url-'.$cid];
      $display_coauthors_email = $_POST['coauthors_email-'.$cid];
      $display_coauthors_affiliation = $_POST['coauthors_affiliation-'.$cid];
      $display_coauthors_affiliation_2 = $_POST['coauthors_affiliation_2-'.$cid];

    }


    $outstr .= "     <table>\n";
    $outstr .= "       <tr>\n";
    $outstr .= "         <td>Name: &nbsp;</td>\n";
    $outstr .= "         <td><input type=\"text\" name=\"coauthors_displayname-$cid\"  value=\"" . $display_coauthors_name . "\" size=\"80\"></td>\n";
    $outstr .= "       </tr>\n       <tr>\n";
    $outstr .= "         <td>URL: &nbsp;</td>\n";
    $outstr .= "         <td><input type=\"text\" name=\"coauthors_url-$cid\"  value=\"" . $display_coauthors_url . "\" size=\"80\"></td>\n";
    $outstr .= "       </tr>\n       <tr>\n";
    $outstr .= "         <td>Email: &nbsp;</td>\n";
    $outstr .= "         <td><input type=\"text\" name=\"coauthors_email-$cid\"  value=\"" . $display_coauthors_email. "\" placeholder = \"private or email address\" size=\"80\"></td>\n";
    $outstr .= "       </tr>\n       <tr>\n";
    $outstr .= "         <td>Affiliation: &nbsp;</td>\n";
    $outstr .= "         <td><input type=\"text\" name=\"coauthors_affiliation-$cid\"  value=\"" . $display_coauthors_affiliation . "\" size=\"80\"></td>\n";
    $outstr .= "       </tr>\n       <tr>\n";
    $outstr .= "         <td>Alternate Affiliation: &nbsp;</td>\n";
    $outstr .= "         <td><input type=\"text\" name=\"coauthors_affiliation_2-$cid\"  value=\"" . $display_coauthors_affiliation_2 . "\" size=\"80\"></td>\n";
    $outstr .= "       </tr>\n    </table><br>\n\n";
  }

  $_SESSION['whichpass'] = 1;
  $outstr .= "    <input type=\"submit\" class = \"form-button\" name=\"submit\" value=\"Submit\"><br><br><br><br>";
  $outstr .= "</form>\n";

  echo $outstr;
}
else{  /* validated --- generate Mysql statements */

  $newbadges = $_POST['newbadges'];

  $oldbadges = array_fill(1,strlen($project['project_features']),0);
  for ($i=0; $i < count($newbadges); $i++) {
    $oldbadges[$newbadges[$i]] = 1;
  }

  $new_project_features = "";
  for ($i=1; $i <= strlen($project['project_features']); $i++) {
    $new_project_features .= $oldbadges[$i];
  }

  $date_stamp = date("Y-m-d-H:i:s");
  $filename = $xml_id."-".$date_stamp.".sql";
  $myfile = fopen("ptx_catalog_updates/".$filename, "a") or die("Unable to open file!");

  $sqlstr = "UPDATE projects SET \n";
  $sqlstr .= "project_title = '" . addslashes($_POST['project_title']) . "', \n";
  $sqlstr .= "project_subtitle = '" . addslashes($_POST['project_subtitle']) . "', \n";
  $sqlstr .= "project_subject_id = '" . $_POST['project_subject_id'] . "', \n" ;

  $sqlstr .= "project_target_level_id = '" . $_POST['project_target_level_id'] . "', \n";
  $sqlstr .= "project_phase_id = '" . $_POST['project_phase_id'] . "', \n";

  $sqlstr .= "project_features = '" . $new_project_features . "', \n";

  if (empty($_POST['project_recognition_code'])){$recognition_code= "NULL";} else{$recognition_code=$_POST['project_recognition_code'];}
  if ($recognition_code =="NULL"){
    $sqlstr .= "project_recognition_code = " . $recognition_code . ", \n";
  }
  else{
    $sqlstr .= "project_recognition_code = '" . $recognition_code . "', \n";
  }

  $sqlstr .= "project_award_id = '" . $_POST['project_award_id'] . "', \n";
  $sqlstr .= "project_license_code= '" . $_POST['project_license_code'] . "', \n";
  $sqlstr .= "project_license_variant = '" . $_POST['project_license_variant'] . "', \n";

  $sqlstr .= "project_license_version = '" . $_POST['project_license_version'] . "', \n";

  if (empty($_POST['project_publication_year'])){$publication_year= date("Y");} else{$publication_year=$_POST['project_publication_year'];}
  $sqlstr .= "project_publication_year = '" . $publication_year . "', \n";

  if (empty($_POST['project_price_amount'])){$price_amount = 0;}else{$price_amount=$_POST['project_price_amount'];}
  $sqlstr .= "project_price_amount = '" . $price_amount . "', \n";

  $sqlstr .= "project_price_currency = '" . $_POST['project_price_currency'] . "', \n";
  $sqlstr .= "project_description_onesentence = '" . addslashes($_POST['project_description_onesentence']) . "', \n";
  $sqlstr .= "project_description_full = '" . addslashes($_POST['project_description_full']) . "', \n";
  $sqlstr .= "project_landing_URL = '" . $_POST['project_landing_URL'] . "', \n";
  $sqlstr .= "project_source_URL = '" . $_POST['project_source_URL'] . "', \n";
  $sqlstr .= "project_html_URL = '" . $_POST['project_html_URL'] . "', \n";
  $sqlstr .= "project_pdf_URL = '" . $_POST['project_pdf_URL'] . "', \n";
  $sqlstr .= "project_print_URL = '" . $_POST['project_print_URL'] . "', \n";
  $sqlstr .= "project_ancillary_1 = '" . $_POST['project_ancillary_1'] . "', \n";
  $sqlstr .= "project_ancillary_2 = '" . $_POST['project_ancillary_2'] . "' \n";
  $sqlstr .= "WHERE project_xml_id = '". $xml_id . "';\n\n";


foreach ($coauthors as $coauthor) {
    $cid = $coauthor['coauthors_id'];
    $sqlstr .= "UPDATE coauthors SET \n";
    $sqlstr .= "coauthors_displayname = '" . $_POST['coauthors_displayname-'.$cid] . "', \n";
    $sqlstr .= "coauthors_url = '" . $_POST['coauthors_url-'.$cid] . "', \n";
    $sqlstr .= "coauthors_email = '" . $_POST['coauthors_email-'.$cid] . "', \n";
    $sqlstr .= "coauthors_affiliation = '" . $_POST['coauthors_affiliation-'.$cid] . "', \n";
    $sqlstr .= "coauthors_affiliation_2 = '" . $_POST['coauthors_affiliation_2-'.$cid] . "' \n";
    $sqlstr .= "WHERE coauthors_id = '". $cid . "';\n\n";
    }



  fwrite($myfile, $sqlstr);




  fclose($myfile);
  echo "Your updates have been submitted for processing.<br><br>";
}
?>


  </body>
</html>
