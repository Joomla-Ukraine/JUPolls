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

class MijopollsViewPolls extends MijosoftView
{
    function display($tpl = null)
    {
        $this->mainframe = JFactory::getApplication();
        $this->option    = JRequest::getCmd('option');

        $filter_order     = $this->mainframe->getUserStateFromRequest($this->option . '.polls.filter_order', 'filter_order', 'm.title', 'string');
        $filter_order_Dir = $this->mainframe->getUserStateFromRequest($this->option . '.polls.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
        $search           = $this->mainframe->getUserStateFromRequest($this->option . '.polls.search', 'search', '', 'string');

        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order']     = $filter_order;
        $lists['search']    = $search;

        $menu        = JSite::getMenu()->getActive();
        $menu_params = new JRegistry($menu->params);
        $params      = clone($this->mainframe->getParams());
        $params->merge($menu_params);

        $this->lists      = $lists;
        $this->params     = $params;
        $this->items      = $this->get('Data');
        $this->pagination = $this->get('Pagination');

        parent::display($tpl);
    }
}