<?php

/**
* @author Frank Karlitschek
* @copyright 2012 Frank Karlitschek frank@karlitschek.de
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

// include phpmailer which has to be in the same folder
require('class.phpmailer.php');
require('config.php');

/**
 * a simple script to collect supporters of a website or statement
 */
class CAMPAIGNER {

  /**
   * show the campaigner box
   *
   * html code for the supporter box including current supporters
   */
  public static function show() {
    echo('<br /><br /><span class="related">Supporter:</span><br />');
    echo('<ul class="list">');
    $result =CAMPAIGNER:: DBselect('id,name,timestamp from supporter where status=1 ');
    $itemscount = CAMPAIGNER::DBnumrows($result);
    for ($i=0; $i < $itemscount; $i++) {
      $data = CAMPAIGNER::DBfetch_assoc($result);
      echo('<li>'.$data['name'].'</li>');
    }
    CAMPAIGNER::DBfree_result($result);

    echo('<form method="post" >');
    echo('<li>');
    echo('<input type="text" id="name" name="name" size="20" class="forminput" maxlength="80" placeholder="Real Name" value="">');
    echo('<input type="text" id="email" name="email" size="20" class="forminput" maxlength="80" placeholder="Email to verify" value="">');
    echo('<input type="submit" class="formbutton" name="send" alt="Add me to the list" value="Add me to the list" />');
    echo('</li>');
    echo('</form>');

    echo('</ul>');
  }

  /**
   * listener that responds to subscribe and confirm events
   *
   * Returns confirmation messages
   */
  public static function listener() {
    $output='';
    if(isset($_POST['send']) and (trim($_POST['name'])<>'') and (trim($_POST['email'])<>'') ) {
      $output.=CAMPAIGNER::add(htmlspecialchars(strip_tags($_POST['name'])),htmlspecialchars(strip_tags($_POST['email'])));
    }

    if(isset($_GET['confirm'])) {
      $output.=CAMPAIGNER::confirm(htmlspecialchars(strip_tags($_GET['confirm'])));
    }
    return($output);
  }

  /**
   * add a supporter to the list. unconfirmed for now
   * @param string $name
   * @param string $email
   *
   * Returns an confirmation message
   */
  private static function add($name,$email) {

    $output=CAMPAIGNER_ADDTEXT;

    $result = CAMPAIGNER::DBselect('id from supporter where status=1 and email="'.mysql_real_escape_string($email).'"');
    $itemscount = CAMPAIGNER::DBnumrows($result);
    if($itemscount==0) {

      $hash=rand(111111,999999).rand(111111,999999).rand(111111,999999).rand(111111,999999);
      $result = CAMPAIGNER::DBinsert('into supporter (name,email,timestamp,status,confirmhash) values ("'.mysql_real_escape_string($name).'","'.mysql_real_escape_string($email).'","'.time().'",0,"'.$hash.'") ');
      CAMPAIGNER::DBfree_result($result);
    

      if(!isset($_SERVER['HTTPS'])) {
        $proto='http://';
      }else{
        $proto='https://';
      }
      $mailtext=CAMPAIGNER_SMTPMAILTEXT."\n\n".$proto.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'].'?confirm='.$hash;

      $mailo = new PHPMailer();
      if(CAMPAIGNER_SMTPMODE=='sendmail') {
        $mailo->IsSendmail();
      }else{
        $mailo->IsSMTP();
      }
      $mailo->Host = CAMPAIGNER_SMTPHOST;
      $mailo->SMTPAuth = CAMPAIGNER_SMTPAUTH;
      $mailo->Username = CAMPAIGNER_SMTPUSERNAME;
      $mailo->Password = CAMPAIGNER_SMTPPASSWORD;

      $mailo->From =CAMPAIGNER_SMTPFROMEMAIL;
      $mailo->FromName = CAMPAIGNER_SMTPFROMNAME;
      $mailo->AddAddress(mysql_real_escape_string($email),mysql_real_escape_string($name));

      $mailo->IsHTML(false);

      $mailo->Subject = CAMPAIGNER_SMTPFROMSUBJECT;
      $mailo->Body    = $mailtext;
      $mailo->CharSet = 'UTF-8';

      $mailo->Send();
      unset($mailo);

    }
    return($output);
  }

  /**
   * confirm a supporter
   * @param string $hash
   *
   * returns an confirmation message
   */
  private static function confirm($hash) {
    $result = CAMPAIGNER::DBupdate('supporter set status=1 where confirmhash="'.mysql_real_escape_string($hash).'" ');
    $output=CAMPAIGNER_CONFIRMTEXT;
    return($output);
  }


  /**
   * executes a query on the database
   *
   * @param string $cmd
   * @return result-set
   */
  private static function DBquery($cmd) {
    global $DBConnection;

    if(!isset($DBConnection)) {
      $DBConnection = @new mysqli(CAMPAIGNER_DB_HOST, CAMPAIGNER_DB_LOGIN, CAMPAIGNER_DB_PASSWD,CAMPAIGNER_DB_NAME, CAMPAIGNER_DB_PORT, CAMPAIGNER_DB_SOCKET);
      if (mysqli_connect_errno()) {
          @ob_end_clean();
          echo('<html><head></head><body bgcolor="#F0F0F0"><br /><br /><center>Can\´t connect to the database.</center></body></html>');
          exit();
      }
    }
    $result = @$DBConnection->query($cmd);
    if (!$result) {
      $entry='DB Error: "'.$DBConnection->error.'"<br />';
      $entry.='Offending command was: '.$cmd.'<br />';
      echo($entry);
    }
    return $result;
  }


  private static function DBselect($cmd) {
    return(CAMPAIGNER::DBquery('select '.$cmd));
  }

  private static function DBupdate($cmd) {
    return(CAMPAIGNER::DBquery('update '.$cmd));
  }

  private static function DBinsert($cmd) {
    return(CAMPAIGNER::DBquery('insert '.$cmd));
  }

  private static function DBdelete($cmd) {
    return(CAMPAIGNER::DBquery('delete '.$cmd));
  }


  /**
   * closing a db connection
   *
   * @return bool
   */
  private static function DBclose() {
    global $DBConnection;
    if(isset($DBConnection)) {
      return $DBConnection->close();
    } else {
      return(false);
    }
  }


  /**
   * Returning number of rows in a result
   *
   * @param resultset $result
   * @return int
   */
  private static function DBnumrows($result) {
    if(!isset($result) or ($result == false)) return 0;
    $num= mysqli_num_rows($result);
    return($num);
  }

  /**
   * get a field from the resultset
   *
   * @param resultset $result
   * @param int $i
   * @param int $field
   * @return unknown
   */
  private static function DBresult($result, $i, $field) {
    //return @mysqli_result($result, $i, $field);

    mysqli_data_seek($result,$i);
    if (is_string($field))
    $tmp=mysqli_fetch_array($result,MYSQLI_BOTH);
    else
    $tmp=mysqli_fetch_array($result,MYSQLI_NUM);
    $tmp=$tmp[$field];
    return($tmp);

  }

  /**
   * get data-array from resultset
   *
   * @param resultset $result
   * @return data
   */
  private static function DBfetch_assoc($result) {
    return mysqli_fetch_assoc($result);
  }


  /**
   * Freeing resultset (performance)
   *
   * @param unknown_type $result
   * @return bool
   */
  private static function DBfree_result($result) {
    return @mysqli_free_result($result);
  }


}




?>
