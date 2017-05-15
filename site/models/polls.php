<?php
/**
 * JUPolls
 *
 * @package          Joomla.Site
 * @subpackage       com_jupolls
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

class JUPollsModelPolls extends JModelLegacy
{
    var $_query = null;
    var $_data = null;
    var $_total = null;
    var $_pagination = null;

    function __construct()
    {
        parent::__construct();

        $this->app    = JFactory::getApplication();
        $this->option = $this->app->input->getWord('option');

        $config            = JFactory::getConfig();
        $config_list_limit = $config->get('config.list_limit');

        // Get the pagination request variables
        $this->setState('limit', $this->app->getUserStateFromRequest('limit', 'limit', $config_list_limit, 'int'));
        $this->setState('limitstart', $this->app->input->get('limitstart', 0, '', 'int'));

        // In case limit has been changed, adjust limitstart accordingly
        $this->setState('limitstart', ($this->getState('limit') != 0 ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) : 0));

        // Get the filter request variables
        $this->setState('filter_order', $this->app->input->get('filter_order', 'ordering'));
        $this->setState('filter_order_dir', $this->app->input->get('filter_order_Dir', 'ASC'));

    }

    function getData()
    {
        if(empty($this->_data))
        {
            $query       = $this->_buildViewQuery();
            $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->_data;
    }

    function getTotal()
    {
        if(empty($this->_total))
        {
            $query        = $this->_buildViewQuery();
            $this->_total = $this->_getListCount($query);
        }

        return $this->_total;
    }

    function getPagination()
    {
        if(empty($this->_pagination))
        {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->_pagination;
    }

    function _buildViewQuery()
    {
        if(empty($this->_query))
        {
            $where   = $this->_buildViewWhere();
            $orderby = $this->_buildViewOrderBy();
            $db      = JFactory::getDBO();

            $this->_query = "SELECT m.*,
			CASE WHEN CHAR_LENGTH(m.alias) THEN CONCAT_WS(':', m.id, m.alias) ELSE m.id END as slug, 
			CASE WHEN publish_down>NOW() THEN 'active' ELSE 'ended' END AS status,"
                . " (SELECT COUNT(v.id) FROM #__jupolls_votes AS v WHERE v.poll_id = m.id) AS voters, COUNT(o.id) AS numoptions "
                . " FROM #__jupolls_polls AS m "
                . " LEFT JOIN #__jupolls_options AS o "
                . " ON o.poll_id = m.id "
                . " WHERE m.published=1 AND o.text <> ''"
                . $where
                . " GROUP BY m.id"
                . $orderby;
        }

        return $this->_query;
    }

    function _buildViewOrderBy()
    {
        $filter_order     = $this->app->getUserStateFromRequest($this->option . '.polls.filter_order', 'filter_order', 'm.publish_down DESC', 'cmd');
        $filter_order_Dir = $this->app->getUserStateFromRequest($this->option . '.polls.filter_order_Dir', 'filter_order_Dir', '', 'cmd');

        $orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir;

        return $orderby;
    }

    function _buildViewWhere()
    {
        $db               = JFactory::getDBO();
        $filter_order     = $this->app->getUserStateFromRequest($this->option . '.polls.filter_order', 'filter_order', 'm.id', 'cmd');
        $filter_order_Dir = $this->app->getUserStateFromRequest($this->option . '.polls.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
        $search           = $this->app->getUserStateFromRequest($this->option . '.polls.search', 'search', '', 'string');
        $search           = JString::strtolower($search);

        $where = array();

        if($search)
        {
            $where[] = 'LOWER(m.title) LIKE ' . $db->Quote('%' . $db->getEscaped($search, true) . '%', false);
        }

        $where = (count($where) ? ' AND ' . implode(' AND ', $where) : '');

        return $where;
    }
}