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

JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');

require_once(JPATH_COMPONENT_ADMINISTRATOR . '/mvc/model.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR . '/mvc/view.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR . '/mvc/controller.php');

if($controller = JRequest::getWord('view'))
{
    $path = JPATH_COMPONENT . '/controllers/' . $controller . '.php';
    if(file_exists($path))
    {
        require_once $path;
    }
    else
    {
        $controller = '';
    }
}

$classname  = 'MijopollsController' . ucfirst($controller);
$controller = new $classname();

$controller->registerTask('results', 'display');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();