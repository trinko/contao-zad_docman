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
	 * Send a notification.
	 */
	public function notify() {
		$db = \Database::getInstance();
    $sql = "SELECT n.*,d.publishedTimestamp,a.waitingTime ".
           "FROM tl_zad_docman_notify AS n,tl_zad_docman AS d,tl_zad_docman_archive AS a ".
           "WHERE n.pid=d.id AND d.pid=a.id AND a.active='1' AND n.state='SEND'";
    $res = $db->execute($sql);
    while ($res->next()) {
      $waiting_time = intval(substr($res->waitingTime, 3)) * 3600;
      if (($res->publishedTimestamp + $waiting_time) <= time()) {
        // send a notification
        $this->send(deserialize($res->recipients), $res->subject, $res->text);
        // notification sent
        $sql = "UPDATE tl_zad_docman_notify ".
               "SET state='SENT',tstamp=".time()." ".
               "WHERE id=".$res->id;
        $db->execute($sql);
      }
    }
  }

	/**
	 * Send a collected notification.
	 */
	public function notifyCollected() {
		$db = \Database::getInstance();
    $sql = "SELECT a.* ".
           "FROM tl_zad_docman_archive AS a ".
           "WHERE a.notify='1' AND a.notifyCollect='1' AND a.active='1'";
    $res = $db->execute($sql);
    while ($res->next()) {
      $waiting_time = intval(substr($res->waitingTime, 3)) * 3600;
      $recipients = array();
      $subject = $res->notifySubject;
      $text = str_replace('[nbsp]', ' ', $res->notifyText);
      $text_repeat = '';
      $parts = array();
      preg_match("/^(.*)\{\{repeat\:start\}\}(.*)\{\{repeat:end\}\}(.*)$/s", $text, $parts);
      if (count($parts) == 4) {
        $sql = "SELECT n.*,d.publishedTimestamp ".
               "FROM tl_zad_docman_notify AS n,tl_zad_docman AS d ".
               "WHERE n.pid=d.id AND d.pid=".$res->id." AND n.state='GROUP-".$res->id."'";
        $notify = $db->execute($sql);
        $timestamp = time();
        while ($notify->next()) {
          if (($notify->publishedTimestamp + $waiting_time) <= time()) {
            $recipients = deserialize($notify->recipients);
            // replace insert tags (ignore tag repeat)
            $txt = $parts[2];
            foreach (deserialize($notify->text) as $kfld=>$fld) {
              $txt = str_replace('{{field:'.$kfld.'}}', $fld, $txt);
            }
            $text_repeat .= $txt;
            // notification sent
            $sql = "UPDATE tl_zad_docman_notify ".
                   "SET state='SENT',tstamp=$timestamp ".
                   "WHERE id=".$notify->id;
            $db->execute($sql);
          }
        }
        $text = $parts[1].$text_repeat.$parts[3];
        // send a notification
        $this->send($recipients, $subject, $text);
      }
    }
  }

	/**
	 * Send a notification.
	 *
	 * @param array $recipients  List of emails.
	 * @param string $subject  Subject of email.
	 * @param string $text  Text of email.
	 */
	private function send($recipients, $subject, $text) {
    // create new email
		$email = new \Email();
		list($email->fromName, $email->from) = \String::splitFriendlyEmail(\Config::get('adminEmail'));
    $email->subject = $subject;
		// load the mail template
		$template = new \BackendTemplate('zaddm_mail');
		$template->charset = \Config::get('characterSet');
		$template->title = $subject;
		$template->css = '';
		$template->body = $text;
    // set HTML text
		$email->html = $template->parse();
    foreach ($recipients as $rec) {
      // send email
      $email->sendTo($rec);
    }
  }

}

