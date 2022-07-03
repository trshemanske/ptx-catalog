
<!DOCTYPE HTML>
<html lang="en-US">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <style>
  .error {color: #FF0000;}

  body {
    font-family: Arial, Helvetica, sans-serif;
    position: relative;
    max-width: 904px;  /* To match PreTeXt */
    margin: 0 auto 0 auto;  /* centered content */
    padding: 0;
    box-sizing: border-box;
  }

  .header {
    background-color: hsl(192, 98%, 23%);
    padding: 1px;
    text-align: center;
    font-size: 20px;
    color: white;
  }

  .main {
    margin-top: 30px;
  }

  .green {
    color: green;
  }


  @media (max-width: 600px) {
    section {
      -webkit-flex-direction: column;
      flex-direction: column;
    }
  }

  .form-button {
    height: 24px;
  }
  </style>

  <script>
  function toggleDiv( id ){
    var div = document.getElementById(id);
    div.style.display = (div.style.display == "none") ? "block" : "none";
  }
  </script>

</head>



<body>
  <!-- https://www.w3schools.com/php/php_form_complete.asp -->
  <?php
  session_start();
  $_SESSION["whichpass"] = 0;
  ?>

  <?php

  function trim_safehtml($raw_text) {
    $new_text = trim($raw_text);
    $new_text = stripslashes($new_text);
    $new_text = htmlspecialchars($new_text, ENT_QUOTES, 'UTF-8');
    return $new_text;
  }

  function safehtml($text) {
    $text = trim($text);
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
  }

  $xml_id = "";
  $xml_id_partial = "";
  $xml_idErr = "";
  ?>

  <div class="header">
    <h2>PreTeXt Catalog Update:  start page</h2>
  </div>


  <div class="main">
    <h3>PreTeXt projects are uniquely identified by their XML	id.</h3>

    <p>To update an existing project, you will need to select
      the associated XML id; to insert a new project, you will have to
      generate a new id.
    </p>


    <p>
      Toggle the <a href="javascript:toggleDiv('xml_id_guidelines')" title="XML id guidelines">guidelines</a> for the suggested format of an XML id.
      <div id="xml_id_guidelines" style="display:none";>
        <p>The PreTeXt guidelines specify that:
          <ul>
            <li>The xml_id should match the docinfo/document-id element of
              your project.</li>
              <li>The preferred format is a single lower-case name of the
                primary author (e.g., beezer), then a single dash, then an upper-case
                initialism, resembling the title (e.g., FCLA), for a First Course in
                Linear Algebra.</li>
                <li> So the end result should look like 'beezer-FCLA'</li>
              </ul>
            </p>

            <p>
              Note that the XML id is mostly an internal identifier and that all
              coauthor information is captured in this registration/update
              process.
            </p>
          </div> <!-- xml_id_guidelines -->
        </p>

        <p>  <!-- Connect to the ptx_catalog database -->
          <?php
          $servername = "localhost";
          $username = "ptx";
          $password = "ptx_db_pw";
          $dbname = "ptx_catalog";

          // Create connection
          $connx = new mysqli($servername, $username, $password, $dbname);

          // Check connection
          if ($connx->connect_error) {
            echo "<span class=\"error\">Catalog database connection failed: <br>" .$connx->connect_error. "</span>";
            exit(1);
          }
          else{
//            echo "<span class=\"green\">Catalog database connection successful</span><br><br>";
          }
          ?>
        </p>

        <p>
          <?php
          /* Search for partial xml id  */
/*          if ($_POST['xml_id_partial'] == "" and $_POST['xml_id'] == "") {  */
          if (empty($_POST)) {
            /* First pass through page looking for input to search */

            $outstr =  "<br><br><h3>Search for an XML id of an existing project:</h3>";
            $outstr .= "Your search criterion can be any contiguous subset of the actual id,<br>";
            $outstr .= "like 'beez' or 'FCL' for 'beezer-FCLA' (do not include quotes)<br>";
            $outstr .= "<form method=\"post\" action=\"". htmlspecialchars($_SERVER["PHP_SELF"]) . "\"><br>";
            $outstr .= "<label for=\"xml_id_partial\">Search for:&nbsp;&nbsp;</label>";
            $outstr .= "<input type=\"text\"  id=\"xml_id_partial\" placeholder=\"xml_id fragment\" name=\"xml_id_partial\" size=\"50\"><br>";
            $outstr .= "<br><br>";
            $outstr .= "<button type=\"submit\" class=\"form-button\">Search</button><br></form>";
            echo $outstr;
          }
/*          elseif ($_POST["xml_id_partial"] != "" and $_POST['xml_id'] == "")  */
            elseif (!empty($_POST['xml_id_partial']) and empty($_POST['xml_id']))
          {
            /* Second pass through page; performing the search */


            $xml_id_partial = $_POST['xml_id_partial'];
            $sql = "SELECT * FROM projects WHERE project_xml_id LIKE '%$xml_id_partial%'";
            $result = $connx->query($sql);

            if ($result->num_rows > 0) {
              /* At least one hit on search criteria */

              /* Output xml ids and titles of all hits */

              while($row = $result->fetch_assoc()) {
                echo "xml id: <b>" . $row["project_xml_id"]. "</b>&nbsp;&nbsp;&nbsp;Project title: " . $row["project_title"]. "<br><br>";
              }
            }
            else {
              echo "<span class=\"error\">No records found ... </span>";
              echo "<a href=\"./ptx-catalog-identify.php\">Retry?</a>";
              echo "&nbsp;&nbsp;[... or this confirms availability of new XML id]";
            }
          }
          /* End of search for xml id */
          ?>
        </p>

        <p>
          <?php

          if (empty($_POST['xml_id'])) {

//            echo "session id is ".session_id();

            $outstr =  "<br><br><h3>To record a new project or modify an existing one:</h3>";
            $outstr .= "<form method=\"post\" action=\"". htmlspecialchars($_SERVER["PHP_SELF"]) . "\"><br>";
            $outstr .= "<label for=\"xml_id\">Enter the (existing or proposed) XML id:&nbsp;&nbsp;</label>";
            $outstr .= "<input type=\"text\"  id=\"xml_id\" placeholder=\"lastname-INITIALISM\" name=\"xml_id\" size=\"50\"><br><br>";
//            $outstr .= "<span class=\"error\">" .$xml_idErr . "</span><br><br>";
            $outstr .= "<button type=\"submit\" class=\"form-button\">Submit</button><br></form>";
            echo $outstr;
          }
          else
          {
            $_SESSION['xml_id'] = $_POST['xml_id'];
            $xml_id = $_POST["xml_id"];
//            echo "Searching for XML id = ".$xml_id."<br><br>";

            $sql = "SELECT * FROM projects  WHERE project_xml_id = '$xml_id'";
            $result = $connx->query($sql);

            if ($result->num_rows > 0) {
              echo "<br><br>Confirming that project xml id exists";
              $outstr = "<form method=\"post\" action=\"./ptx-catalog-update.php\"><br>";
              $outstr .= "<button type=\"submit\" class=\"form-button\">Update Project ".$xml_id. "</button><br></form>";
              echo $outstr;
//              $_SESSION["xml_id"] = $xml_id;
//              echo $_SESSION["xml_id"];
            }
            else
            {
              echo "<br><br>New project xml id entered";
              $outstr = "<form method=\"post\" action=\"./ptx-catalog-insert.php\">";
              $outstr .= "<label>Enter the total number of (co)authors for this new project:&nbsp;</label>";
              $outstr .= "<input type=\"number\" name=\"coauthors_count\" min=\"1\" max=\"99\"><br><br>\n";
              $outstr .= "<button type=\"submit\" class=\"form-button\">Insert new project ".$xml_id. "</button><br></form>";
              echo $outstr;
//              $_SESSION["xml_id"] = $xml_id;

            }
          }
          ?>
        </p>


      </div>  <!-- main -->
    </body>
    </html>
