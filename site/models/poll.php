<?php
/**
 * JUPolls
 *
 * @package          Joomla.Site
 * @subpackage       com_mijopolls
 *
 * @author           Denys Nosov, denys@joomla-ua.org
 * @copyright        2016-2017 (C) Joomla! Ukraine, http://joomla-ua.org. All rights reserved.
 * @license          GNU General Public License version 2 or later; see LICENSE.txt
 * @license          GNU/GPL based on AcePolls www.joomace.net
 */

/**
 * @copyright      2009-2011 Mijosoft LLC, www.mijosoft.com
 * @license        GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @license        GNU/GPL based on AcePolls www.joomace.net
 *
 * @copyright (C)  2009 - 2011 Hristo Genev All rights reserved
 * @license        http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           http://www.afactory.org
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.environment.browser');

class MijopollsModelPoll extends MijosoftModel
{
    public function vote($poll_id, $option_id)
    {
        $db        = JFactory::getDBO();
        $user      = JFactory::getUser();
        $date      = JFactory::getDate();
        $mainframe = JFactory::getApplication();
        $poll_id   = (int) $poll_id;
        $option_id = (int) $option_id;

        setcookie('_donepoll' . $poll_id, 1, time() + 60, JURI::base(true));

        $ip = ip2long($this->get_ip());

        $browser = JBrowser::getInstance();
        $agent   = $browser->getAgentString();
        $agent   = MD5($agent);

        $dt = $date->toSql();

        $query = "INSERT INTO #__mijopolls_votes (date, option_id, poll_id, ip, browser, user_id) VALUES ('{$dt}', '{$option_id}', '{$poll_id}', '{$ip}', '{$agent}', '{$user->id}')";
        $db->setQuery($query);

        if(!$db->query())
        {
            $msg = $db->stderr();
            $tom = "error";
        }

        return true;
    }

    public function getOptions()
    {
        $db      = JFactory::getDBO();
        $poll_id = JRequest::getInt('id', 0);

        $query = "SELECT o.*, COUNT(v.id) AS hits,
    	(SELECT COUNT(id) FROM #__mijopolls_votes WHERE poll_id=" . $poll_id . ") AS voters"
            . " FROM #__mijopolls_options AS o"
            . " LEFT JOIN #__mijopolls_votes AS v"
            . " ON (o.id = v.option_id AND v.poll_id = " . $poll_id . ")"
            . " WHERE o.poll_id = " . $poll_id
            . " AND o.text <> ''"
            . " GROUP BY o.id "
            . " ORDER BY o.ordering ";

        $db->setQuery($query);

        if($votes = $db->loadObjectList())
        {
            return $votes;
        }
        else
        {
            return $db->stderr();
        }
    }

    public function getPolls()
    {
        $db = JFactory::getDBO();

        $query = $db->getQuery(true);
        $query->select("id, title, CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(':', id, alias) ELSE id END AS slug");
        $query->from('#__mijopolls_polls');
        $query->where('published = 1');
        $query->order('id');
        $db->setQuery($query);

        if($pList = $db->loadObjectList())
        {
            return $pList;
        }
        else
        {
            return $db->stderr();
        }
    }

    public function ipVoted($poll, $poll_id)
    {
        $params = new JRegistry($poll->params);

        if($params->get('ip_check') == 0)
        {
            return false;
        }

        $poll_id = (int) $poll_id;
        $ip      = ip2long($this->get_ip());

        $browser = JBrowser::getInstance();
        $agent   = $browser->getAgentString();
        $agent   = MD5($agent);

        $db    = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__mijopolls_votes');
        $query->where('poll_id = ' . $db->Quote($poll_id));
        $query->where('(ip = ' . $db->Quote($ip) . ' AND browser = ' . $db->Quote($agent) . ')');
        $db->setQuery($query);
        $res = $db->loadResult();

        if(!empty($res))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function get_ip()
    {
        if(!empty($_SERVER['HTTP_CLIENT_IP']))
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        $pieceip = explode(",", $ip);
        $_ip     = trim($pieceip[0]);

        return $_ip;
    }
}