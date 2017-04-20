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

class MijopollsModelPolls extends MijopollsModel
{

    var $_query = null;
    var $_data = null;
    var $_total = null;
    var $_pagination = null;

    function __construct()
    {
        parent::__construct();

        $this->mainframe = JFactory::getApplication();
        $this->option    = JRequest::getWord('option');

        // Get the pagination request variables
        $limit      = $this->mainframe->getUserStateFromRequest($this->option . '.polls.limit', 'limit', $this->mainframe->getCfg('list_limit'), 'int');
        $limitstart = $this->mainframe->getUserStateFromRequest($this->option . '.polls.limitstart', 'limitstart', 0, 'int');

        // In case limit has been changed, adjust limitstart accordingly
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState($this->option . '.polls.limit', $limit);
        $this->setState($this->option . '.polls.limitstart', $limitstart);

        $this->_buildViewQuery();
    }

    function getData()
    {
        if(empty($this->_data))
        {
            $this->_data = $this->_getList($this->_query, $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->_data;
    }

    function getTotal()
    {
        if(empty($this->_total))
        {
            $this->_total = $this->_getListCount($this->_query);
        }

        return $this->_total;
    }

    function getPagination()
    {
        if(empty($this->_pagination))
        {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($this->getTotal(), $this->getState($this->option . '.polls.limitstart'), $this->getState($this->option . '.polls.limit'));
        }

        return $this->_pagination;
    }

    function _buildViewQuery()
    {
        if(empty($this->_query))
        {
            $db = JFactory::getDBO();

            $where   = $this->_buildViewWhere();
            $orderby = $this->_buildViewOrderBy();

            $this->_query = "SELECT m.*, u.name AS editor, COUNT(o.id) AS options, (SELECT count(v.id) FROM #__mijopolls_votes AS v
			WHERE v.poll_id = m.id) AS votes FROM #__mijopolls_polls AS m 
			LEFT JOIN #__users AS u ON u.id = m.checked_out 
			LEFT JOIN #__mijopolls_options AS o ON o.poll_id = m.id AND o.text <> ''"
                . $where
                . " GROUP BY m.id"
                . $orderby;
        }

        return $this->_query;
    }

    function _buildViewOrderBy()
    {
        $filter_order     = $this->mainframe->getUserStateFromRequest($this->option . '.polls.filter_order', 'filter_order', 'm.publish_down', 'cmd');
        $filter_order_Dir = $this->mainframe->getUserStateFromRequest($this->option . '.polls.filter_order_Dir', 'filter_order_Dir', 'DESC', 'word');

        $orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir;

        return $orderby;
    }

    function _buildViewWhere()
    {
        $db = JFactory::getDBO();

        $filter_order     = $this->mainframe->getUserStateFromRequest($this->option . '.polls.filter_order', 'filter_order', 'm.title', 'string');
        $filter_order_Dir = $this->mainframe->getUserStateFromRequest($this->option . '.polls.filter_order_Dir', 'filter_order_Dir', '', 'word');
        $filter_state     = $this->mainframe->getUserStateFromRequest($this->option . '.polls.filter_state', 'filter_state', '', 'word');
        $search           = $this->mainframe->getUserStateFromRequest($this->option . '.polls.search', 'search', '', 'string');
        $search           = JString::strtolower($search);

        $where = array();

        if($search)
        {
            $where[] = 'LOWER(m.title) LIKE ' . $db->Quote('%' . $db->getEscaped($search, true) . '%', false);
        }

        if($filter_state)
        {
            if($filter_state == 'P')
            {
                $where[] = 'm.published = 1';
            }
            elseif($filter_state == 'U')
            {
                $where[] = 'm.published = 0';
            }
        }

        $where = (count($where) ? ' WHERE ' . implode(' AND ', $where) : '');

        return $where;
    }

    function resetVotes($cid = null)
    {
        $db = JFactory::getDBO();

        $cid = JRequest::getVar('cid', array(), '', 'array');
        JArrayHelper::toInteger($cid);
        $cids = implode(',', $cid);

        $query = "DELETE FROM #__mijopolls_votes WHERE poll_id IN (" . $cids . ")";
        $db->setQuery($query);

        if($db->query())
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}