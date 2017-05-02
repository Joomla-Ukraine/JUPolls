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

jimport('joomla.html.parameter.element');

class JElementPoll extends JElement
{
    var $_name = 'Poll';

    function fetchElement($name, $value, &$node, $control_name)
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);
        $query->select('a.id, a.title');
        $query->from('#__mijopolls_polls AS a');
        $query->where('a.published = ' . $db->Quote('package'));
        $query->order('a.title');
        $db->setQuery($query);
        $options = $db->loadObjectList();

        if(JRequest::getCmd('option') == "com_modules")
        {
            array_unshift($options, JHTML::_('select.option', '', '- - - - - - - - - - -', 'id', 'title'));
            array_unshift($options, JHTML::_('select.option', '0', JText::_('Show random poll'), 'id', 'title'));
        }
        else
        {
            array_unshift($options, JHTML::_('select.option', '0', '- - ' . JText::_('Select Poll') . ' - -', 'id', 'title'));
        }

        return JHTML::_('select.genericlist', $options, '' . $control_name . '[' . $name . ']', 'class="inputbox"', 'id', 'title', $value, $control_name . $name);
    }
}
