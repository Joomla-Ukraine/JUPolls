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

defined('_JEXEC') or die ('Restricted access');

jimport('joomla.application.component.view');

if(!class_exists('MijosoftView'))
{
    if(interface_exists('JView'))
    {
        abstract class MijosoftView extends JViewLegacy
        {
        }
    }
    else
    {
        class MijosoftView extends JView
        {
        }
    }
}

class MijopollsView extends MijosoftView
{

    public function __construct()
    {
        parent::__construct();
    }
}