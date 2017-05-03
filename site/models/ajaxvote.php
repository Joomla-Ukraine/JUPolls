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

class MijopollsModelAjaxvote extends MijosoftModel
{

    var $_query = null;
    var $_data = null;
    var $_total = null;
    var $_voted = null;

    function getVoted()
    {
        // Check for request forgeries
        JRequest::checkToken() or jexit('Invalid Token');

        $app = JFactory::getApplication();
        $poll_id   = JRequest::getInt('id', 0);
        $option_id = JRequest::getInt('voteid', 0);
        $poll      = JTable::getInstance('Poll', 'Table');

        if(!$poll->load($poll_id) || $poll->published != 1)
        {
            $app->redirect('index.php', JText::_('ALERTNOTAUTH 1'));

            return true;
        }

        require_once(JPATH_COMPONENT . '/models/poll.php');
        $model      = new MijopollsModelPoll();
        $params     = new JRegistry($poll->params);
        $cookieName = JApplicationHelper::getHash($app->getName() . 'poll' . $poll_id);


        $voted_cookie = JRequest::getVar($cookieName, '0', 'COOKIE', 'INT');
        $voted_ip     = $model->ipVoted($poll, $poll_id);

        if($params->get('ip_check') and ($voted_cookie or $voted_ip or !$option_id))
        {
            /*if($voted_cookie || $voted_ip)
            {
                $msg = JText::_('COM_MIJOPOLLS_ALREADY_VOTED');
                $tom = "error";
            }

            if(!$option_id)
            {
                $msg = JText::_('COM_MIJOPOLLS_NO_SELECTED');
                $tom = "error";
            }
            */

            $this->_voted = 0;
        }
        else
        {
            if($model->vote($poll_id, $option_id))
            {
                $this->_voted = 1;

                setcookie($cookieName, '1', time() + 60 * $poll->lag);
            }
            else
            {
                $this->_voted = 0;
            }
        }

        return $this->_voted = 1;
    }

    function getData()
    {
        if(empty($this->_data))
        {
            $query       = $this->_buildQuery();
            $this->_data = $this->_getList($query);
        }

        return $this->_data;
    }

    function getTotal()
    {
        if(empty($this->_total))
        {
            $query        = $this->_buildQuery();
            $this->_total = $this->_getListCount($query);
        }

        return $this->_total;
    }

    function _buildQuery()
    {
        if(empty($this->_query))
        {
            $db      = JFactory::getDBO();
            $poll_id = JRequest::getVar('id', 0, 'POST', 'int');

            $this->_query = "SELECT o.id, o.text, COUNT(v.id) AS votes"
                . " FROM #__mijopolls_options AS o "
                . " LEFT JOIN #__mijopolls_votes AS v "
                . " ON o.id = v.option_id "
                . " WHERE o.poll_id = " . (int) $poll_id
                . " GROUP BY o.id ";
        }

        return $this->_query;
    }
}