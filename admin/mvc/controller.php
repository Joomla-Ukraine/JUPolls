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

jimport('joomla.application.component.controller');

class MijopollsController extends JControllerLegacy
{
    public function __construct()
    {
        parent::__construct();

        $this->registerTask('add', 'edit');
        $this->registerTask('apply', 'save');
        $this->registerTask('unpublish', 'publish');
        $this->registerTask('deleteVotes', 'deleteVotes');
        $this->registerTask('importPolls', 'importPolls');
    }

    public function display($cachable = false, $urlparams = false)
    {
        if(JFactory::getApplication()->isAdmin())
        {
            $controller = JRequest::getVar('controller', 'polls');
        }
        else
        {
            $controller = JRequest::getVar('view', 'polls');
        }

        JRequest::setVar('view', $controller);

        parent::display($cachable, $urlparams);
    }

    public function edit()
    {
        JRequest::setVar('view', 'poll');
        JRequest::setVar('edit', true);
        JRequest::setVar('hidemainmenu', 1);

        parent::display();
    }

    public function save()
    {
        // Check for request forgeries
        JRequest::checkToken() or jexit('Invalid Token');

        $db = JFactory::getDBO();

        // save the apoll parent information
        $row = JTable::getInstance('Poll', 'Table');

        $post = JRequest::get('post');
        if(!$row->bind($post))
        {
            JError::raiseError(500, $row->getError());
        }

        $isNew = ($row->id == 0);

        //reset the poll, erases hits and voters
        if($optionReset = JRequest::getVar('reset'))
        {
            $model = $this->getModel('polls');
            $model->resetVotes((int) $row->id);
        }

        if(!$row->check())
        {
            JError::raiseError(500, $row->getError());
        }

        if(!$row->store())
        {
            JError::raiseError(500, $row->getError());
        }
        $row->checkin();

        // put all poll options and their colors and ordering in arrays
        $options   = JArrayHelper::getValue($post, 'polloption', array(), 'array');
        $orderings = JArrayHelper::getValue($post, 'ordering', array(), 'array');

        //options represented by id=>text
        foreach ($options as $i => $text)
        {
            // turns ' into &#039;
            $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

            if($isNew)
            {
                if($text != '')
                {
                    $obj           = new stdClass();
                    $obj->poll_id  = (int) $row->id;
                    $obj->text     = $text;
                    $obj->ordering = $orderings[$i];
                    $db->insertObject('#__mijopolls_options', $obj);
                }
            }
            else
            {
                if($text != '')
                {
                    $obj           = new stdClass();
                    $obj->id       = (int) $i;
                    $obj->text     = $text;
                    $obj->ordering = $orderings[$i];
                    $db->updateObject('#__mijopolls_options', $obj, 'id');
                }
                else
                {
                    //If there are empty options delete them so we don't waste database space
                    $model = $this->getModel('poll');
                    if(!$model->deleteOption($i))
                    {
                        JError::raiseError(500, $model->getError());
                    }
                }
            }
        }

        // Are there any new options that are added
        if(JRequest::getVar('is_there_extra'))
        {
            $extra_options  = JArrayHelper::getValue($post, 'polloptionextra', array(), 'array');
            $extra_ordering = JArrayHelper::getValue($post, 'extra_ordering', array(), 'array');
            $extra_colors   = JArrayHelper::getValue($post, 'extra_colors', array(), 'array');

            //Insert in the database the newly created options
            foreach ($extra_options as $k => $text)
            {
                if($text != '')
                {
                    $obj           = new stdClass();
                    $obj->poll_id  = (int) $row->id;
                    $obj->text     = $text;
                    $obj->ordering = $extra_ordering[$k];
                    $db->insertObject('#__mijopolls_options', $obj);
                }
            }
        }

        switch (JRequest::getCmd('task'))
        {
            case 'apply':
                $msg  = JText::_('COM_MIJOPOLLS_POLL_SAVED');
                $link = 'index.php?option=com_mijopolls&controller=poll&task=edit&cid[]=' . $row->id;
                break;
            case 'save':
            default:
                $msg  = JText::_('COM_MIJOPOLLS_POLL_SAVED');
                $link = 'index.php?option=com_mijopolls';
                break;
        }

        $this->setRedirect($link, $msg);
    }

    public function remove()
    {
        // Check for request forgeries
        JRequest::checkToken() or jexit('Invalid Token');

        $db  = JFactory::getDBO();
        $cid = JRequest::getVar('cid', array(), '', 'array');

        JArrayHelper::toInteger($cid);
        $msg = '';

        for ($i = 0, $n = count($cid); $i < $n; $i++)
        {
            $apoll = JTable::getInstance('poll', 'Table');
            if(!$apoll->delete($cid[$i]))
            {
                $msg .= $apoll->getError();
                $tom = "error";
            }
            else
            {
                $msg = JTEXT::_('COM_MIJOPOLLS_POLL_DELETED');
                $tom = "";
            }
        }

        $this->setRedirect('index.php?option=com_mijopolls', $msg, $tom);
    }

    public function deleteVotes()
    {
        // Check for request forgeries
        JRequest::checkToken() or jexit('Invalid Token');

        $poll_id = JRequest::getVar('poll_id', 0, 'POST', 'INT');
        $model   = $this->getModel('votes');

        if($model->deleteVotes())
        {
            $msg = Jtext::_("COM_MIJOPOLLS_DELETED_VOTES_YES");
            $tom = "";
        }
        else
        {
            $msg = Jtext::_("COM_MIJOPOLLS_DELETED_VOTES_NO");
            $tom = "error";
        }

        $this->setRedirect('index.php?option=com_mijopolls&controller=votes&task=view&id=' . $poll_id, $msg, $tom);
    }

    public function publish()
    {
        $mainframe = JFactory::getApplication();

        // Check for request forgeries
        JRequest::checkToken() or jexit('Invalid Token');

        $user = JFactory::getUser();

        $cid     = JRequest::getVar('cid', array(), '', 'array');
        $publish = (JRequest::getCmd('task') == 'publish' ? 1 : 0);

        $table = JTable::getInstance('poll', 'Table');
        JArrayHelper::toInteger($cid);

        if(!$table->publish($cid, $publish, $user->get('id')))
        {
            $table->getError();
        }

        if(count($cid) < 1)
        {
            $action = $publish ? 'publish' : 'unpublish';
            JError::raiseError(500, JText::_('Select an item to' . $action, true));
        }

        $mainframe->redirect('index.php?option=com_mijopolls');
    }

    public function cancel()
    {
        // Check for request forgeries
        JRequest::checkToken() or jexit('Invalid Token');

        $id  = JRequest::getVar('id', 0, '', 'int');
        $row = JTable::getInstance('poll', 'Table');

        $row->checkin($id);

        $this->setRedirect('index.php?option=com_mijopolls');
    }

    public function resetVotes()
    {
        // Check for request forgeries
        JRequest::checkToken() or jexit('Invalid Token');

        $model = $this->getModel('polls');

        if($model->resetVotes())
        {
            $msg = Jtext::_("COM_MIJOPOLLS_DELETED_POLL_VOTES_YES");
            $tom = "";
        }
        else
        {
            $msg = Jtext::_("VCOM_MIJOPOLLS_DELETED_POLL_VOTES_NO");
            $tom = "error";
        }

        $this->setRedirect('index.php?option=com_mijopolls&controller=polls', $msg, $tom);
    }

    public function vote()
    {
        // Check for request forgeries
        JRequest::checkToken() or jexit('Invalid Token');

        $mainframe = JFactory::getApplication();
        $poll_id   = JRequest::getInt('id', 0);
        $option_id = JRequest::getInt('voteid', 0);
        $poll      = JTable::getInstance('Poll', 'Table');

        if(!$poll->load($poll_id) || $poll->published != 1)
        {
            JError::raiseWarning(404, JText::_('ALERTNOTAUTH'));

            return;
        }

        $model = $this->getModel('Poll');

        $params     = new JRegistry($poll->params);
        $cookieName = JApplication::getHash($mainframe->getName() . 'poll' . $poll_id);

        $voted_cookie = JRequest::getVar($cookieName, '0', 'COOKIE', 'INT');
        $voted_ip     = $model->ipVoted($poll, $poll_id);

        if($params->get('ip_check') and ($voted_cookie or $voted_ip or !$option_id))
        {
            if($voted_cookie || $voted_ip)
            {
                $msg = JText::_('COM_MIJOPOLLS_ALREADY_VOTED');
                $tom = "error";
            }

            if(!$option_id)
            {
                $msg = JText::_('COM_MIJOPOLLS_NO_SELECTED');
                $tom = "error";
            }
        }
        else
        {
            if($model->vote($poll_id, $option_id))
            {
                //Set cookie showing that user has voted
                setcookie($cookieName, '1', time() + 60 * $poll->lag);
            }

            $msg = JText::_('COM_MIJOPOLLS_THANK_YOU');
            $tom = "";

            if(JFactory::getUser()->id != 0)
            {
                JPluginHelper::importPlugin('mijopolls');
                $dispatcher = JDispatcher::getInstance();
                $dispatcher->trigger('onAfterVote', array($poll, $option_id));
            }
        }

        // set Itemid id for links
        $menu  = JSite::getMenu();
        $items = $menu->getItems('link', 'index.php?option=com_mijopolls');

        $itemid = isset($items[0]) ? '&Itemid=' . $items[0]->id : '';

        $this->setRedirect(JRoute::_('index.php?option=com_mijopolls&view=poll&id=' . $poll_id . ':' . $poll->alias . $itemid, false));
    }
}
