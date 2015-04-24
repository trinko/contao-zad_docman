<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package   zad_docman
 * @author    Antonello Dessì
 * @license   LGPL
 * @copyright Antonello Dessì 2015
 */


/**
 * Namespace
 */
namespace zad_docman;


/**
 * Class ZadDocman
 *
 * Document Manager cron functions.
 *
 * @copyright Antonello Dessì 2015
 * @author    Antonello Dessì
 * @package   zad_docman
 */
class ZadDocman extends \Backend {

	/**
	 * Template
	 *
	 * @var string $strTemplate  Template name
	 */
	protected $strTemplate = '';

	/**
	 * Database instance
	 *
	 * @var \Database $db  Database object
	 */
	protected $db = null;


	/**
	 * Send a notification.
	 */
	public function notify() {
    // get notification data
		$this->db = \Database::getInstance();
    $sql = "SELECT d.id,d.publishedTimestamp,a.notifySubject,a.notifyText,a.notifyGroups,a.waitingTime ".
           "FROM tl_zad_docman AS d,tl_zad_docman_archive AS a ".
           "WHERE d.pid=a.id AND a.active='1' AND d.notificationState='SEND' ".
           "ORDER BY d.publishedTimestamp";
    $res = $this->db->execute($sql);
    while ($res->next()) {
      $waiting_time = intval(substr($res->waitingTime, 3)) * 3600;
      if (($res->publishedTimestamp + $waiting_time) <= time()) {
        // notification subject
        $subject = $res->notifySubject;
        // notification text
        $text = $this->insertFields($res->notifyText, $res->id);
        // notification recipients
        $recipients = array();
        foreach (deserialize($res->notifyGroups) as $group) {
          $sql = "SELECT t.email ".
                 "FROM tl_member AS t ".
                 "WHERE t.groups LIKE '%:\"$group\";%' AND t.disable=''";
          $recipients_res = $this->db->execute($sql);
          $recipients = array_merge($recipients, $recipients_res->fetchEach('email'));
        }
        $recipients = array_unique($recipients);
        // send a notification
        $errors = $this->send($recipients, $subject, $text);
        // notification sent
        $sql = "UPDATE tl_zad_docman ".
               "SET notificationTimestamp=".time().",".
                   "notificationSent='".serialize(array_diff($recipients, $errors))."',".
                   "notificationError='".serialize($errors)."',".
                   "notificationState='SENT' ".
               "WHERE id=".$res->id;
        $this->db->execute($sql);
      }
    }
  }

	/**
	 * Send a collected notification.
	 */
	public function notifyCollected() {
		$this->db = \Database::getInstance();
    // read documents to notify
    $sql = "SELECT d.id,d.pid,d.publishedTimestamp,a.notifySubject,a.notifyText,a.notifyGroups,a.waitingTime ".
           "FROM tl_zad_docman AS d,tl_zad_docman_archive AS a ".
           "WHERE d.pid=a.id AND a.active='1' AND d.notificationState='COLLECT' ".
           "ORDER BY d.pid,d.publishedTimestamp";
    $res = $this->db->execute($sql);
    $archive = null;
    while ($res->next()) {
      if ($archive != $res->pid) {
        // send notification
        if ($archive != null) {
          $text = $separators[0];
          foreach ($data as $i=>$txt) {
            $text .= $txt . $separators[$i+1];
          }
          // send
          $errors = $this->send($recipients, $subject, $text);
          // notification sent
          $sql = "UPDATE tl_zad_docman ".
                 "SET notificationTimestamp=".time().",".
                     "notificationSent='".serialize(array_diff($recipients, $errors))."',".
                     "notificationError='".serialize($errors)."',".
                     "notificationState='SENT' ".
                "WHERE notificationState='COLLECTED'";
          $this->db->execute($sql);
        }
        // waiting time
        $waiting_time = intval(substr($res->waitingTime, 3)) * 3600;
        // notification subject
        $subject = $res->notifySubject;
        // notification text
        $text = str_replace('[nbsp]', ' ', $res->notifyText);
        preg_match_all('/\{\{repeat\:start\}\}(.*)\{\{repeat:end\}\}/sU', $text, $parts, PREG_SET_ORDER);
        $separators = preg_split('/\{\{repeat\:start\}\}(.*)\{\{repeat:end\}\}/sU', $text);
        $data = array();
        // notification recipients
        $recipients = array();
        foreach (deserialize($res->notifyGroups) as $group) {
          $sql = "SELECT t.email ".
                 "FROM tl_member AS t ".
                 "WHERE t.groups LIKE '%:\"$group\";%' AND t.disable=''";
          $recipients_res = $this->db->execute($sql);
          $recipients = array_merge($recipients, $recipients_res->fetchEach('email'));
        }
        $recipients = array_unique($recipients);
        // set new document archive
        $archive = $res->pid;
      }
      if (($res->publishedTimestamp + $waiting_time) <= time()) {
        foreach ($parts as $i=>$part) {
          $data[$i] .= $this->insertFields($part[1], $res->id);
        }
        // mark document as collected
        $sql = "UPDATE tl_zad_docman ".
               "SET notificationState='COLLECTED' ".
               "WHERE id=".$res->id;
        $this->db->execute($sql);
      }
    }
    if ($archive != null) {
      $text = $separators[0];
      foreach ($data as $i=>$txt) {
        $text .= $txt . $separators[$i+1];
      }
      // send
      $errors = $this->send($recipients, $subject, $text);
      // notification sent
      $sql = "UPDATE tl_zad_docman ".
             "SET notificationTimestamp=".time().",".
                 "notificationSent='".serialize(array_diff($recipients, $errors))."',".
                 "notificationError='".serialize($errors)."',".
                 "notificationState='SENT' ".
            "WHERE notificationState='COLLECTED' AND pid=".$res->pid;
      $this->db->execute($sql);
    }
  }

	/**
	 * Send a notification.
	 *
	 * @param array $recipients  List of emails.
	 * @param string $subject  Subject of email.
	 * @param string $text  Text of email.
	 *
	 * @return array  The list of recipients with errors
	 */
	private function send($recipients, $subject, $text) {
    // init error list
    $errors = array();
    // set HTML text
		$template = new \BackendTemplate('zaddm_mail');
		$template->charset = \Config::get('characterSet');
		$template->title = $subject;
		$template->css = '';
		$template->body = $text;
		$html = $template->parse();
    // set plain text
		$plaintext = $this->htmlToText($text);
    // set sender
  	list($fromName, $from) = \String::splitFriendlyEmail(\Config::get('adminEmail'));
    // send
    foreach ($recipients as $rec) {
      // create new email
  		$email = new \Email();
      $email->fromName = $fromName;
      $email->from = $from;
      $email->subject = $subject;
  		$email->html = $html;
  		$email->text = $plaintext;
      // send email
  		try {
        $email->sendTo($rec);
  		} catch (\Swift_RfcComplianceException $e) {
        $errors[] = $rec;
  		}
      if ($email->hasFailures()) {
        $errors[] = $rec;
      }
    }
    // return error list
    return $errors;
  }

  /**
	 * Insert field values in text
	 *
	 * @param string $text  The text
	 * @param int $doc  The document ID
	 *
	 * @return string  The new text
	 */
	private function insertFields($text, $doc) {
    // clean text
    $text = str_replace(array('[nbsp]','{{repeat:start}}','{{repeat:end}}'), ' ', $text);
    // get fields
    $sql = "SELECT f.name,f.type,f.list,f.listOther,f.autofield,d.value ".
           "FROM tl_zad_docman_fields AS f,tl_zad_docman_data AS d ".
           "WHERE f.name=d.field AND d.pid=".$doc;
    $res = $this->db->execute($sql);
    while ($res->next()) {
      $field = '{{field:'.$res->name.'}}';
      if (strpos($text, $field) !== FALSE) {
        // format data
        switch ($res->type) {
          case 't_date':
            $date = new \Date($res->value);
            $value = $date->date;
            break;
          case 't_time':
            $date = new \Date($res->value);
            $value = $date->time;
            break;
          case 't_datetime':
            $date = new \Date($res->value);
            $value = $date->datim;
            break;
          case 't_choice':
            if ($res->listOther && substr($res->value,0,10) == '__OTHER__:') {
              // other option
              $value = substr($res->value, 10);
            } else {
              // listed option
              $list = unserialize($res->list);
              foreach ($list as $l) {
                if ($l['value'] == $res->value) {
                  $value = $l['label'];
                  break;
                }
              }
            }
            break;
          case 't_mchoice':
            $list = unserialize($res->list);
            $vlist = unserialize($res->value);
            $value = '';
            foreach ($vlist as $vl) {
              foreach ($list as $l) {
                if ($l['value'] == $vl) {
                  $value .= (($value == '') ? '' : ', ') . $l['label'];
                  break;
                }
              }
            }
            if ($res->listOther && in_array('__OTHER__', $vlist)) {
              // other option
              $value .= (($value == '') ? '' : ', ') . substr(end($vlist), 10);
            }
            break;
          case 't_auto':
            $value = '';
            if ($res->autofield == 'af_timestamp') {
              $date = new \Date($res->value);
              $value = $date->datim;
            } elseif ($res->autofield == 'af_user') {
              $user = \MemberModel::findByPk($res->value);
              if ($user !== null) {
                $value = $user->lastname.' '.$user->firstname;
              }
            }
            break;
          default:
            $value = $res->value;
            break;
        }
        // replace field
        $text = str_replace($field, $value, $text);
      }
    }
    // return new text
    return $text;
  }

	/**
	 * Convert HTML format to plain text.
	 *
	 * @param string $html  The text in HTML format
	 *
	 * @return string  The plain text
	 */
	private function htmlToText($html) {
    // convert entities
    $text = html_entity_decode($html, ENT_QUOTES, $GLOBALS['TL_CONFIG']['characterSet']);
    // adjust newlines
    $text = str_replace(array("\r","\n","\t",'[nbsp]'), ' ', $text);
    $text = str_replace(array('<br>','<br />','</div>','</li>','</td>','</tr>','</ol>','</ul>'), "\n", $text);
    $text = str_replace(array('</p>','</h1>','</h2>','</h3>','</h4>','</h5>','</h6>'), "\n\n", $text);
    // remove HTML header
    $text = preg_replace('/<head>.*<\/head>/i', '', $text);
    // strip tags
    $text = strip_tags($text,'<hr>,<li>,<a>');
    // convert tags
    $text = preg_replace('/<hr[^>]*>/i', "\n----------------------------------------\n\n", $text);
    $text = preg_replace('/<li[^>]*>/i', "\n* ", $text);
    $text = preg_replace('/<a\s+[^>]*href\s*=\s*"([^"]+)"[^>]*>([^<]*)<\/a>/i', '$2 [ $1 ]', $text);
    // adjust spaces
    while (strstr($text, '  ') != null) {
      $text = str_replace('  ', ' ', $text);
    }
    $text = str_replace("\n ", "\n", $text);
    // return plain text
    return $text;
  }

}

