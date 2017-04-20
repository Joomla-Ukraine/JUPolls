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

jimport('joomla.application.component.modeladmin');

class MijopollsModelPoll extends JModelAdmin
{
    var $_id = null;
    var $_data = null;

    function __construct()
    {
        parent::__construct();

        $array = JRequest::getVar('cid', array(0), '', 'array');
        $edit  = JRequest::getVar('edit', true);

        if($edit)
        {
            $this->setId((int) $array[0]);
        }
    }

    function setId($id)
    {
        $this->_id   = $id;
        $this->_data = null;
    }

    function isCheckedOut($uid = 0)
    {
        if((boolean) $this->_data)
        {
            if($uid)
            {
                return ($this->_data->checked_out && $this->_data->checked_out != $uid);
            }
            else
            {
                return $this->_data->checked_out;
            }
        }
    }

    public function checkin($pks = array())
    {
        if($this->_id)
        {
            $poll = $this->getTable();

            if(!$poll->checkin($this->_id))
            {
                $this->setError($this->_db->getErrorMsg());

                return false;
            }
        }

        return false;
    }

    public function checkout($uid = null)
    {
        if($this->_id)
        {
            if(is_null($uid))
            {
                $user = JFactory::getUser();
                $uid  = $user->get('id');
            }

            // Lets get to it and checkout the thing...
            $poll = $this->getTable();

            if(!$poll->checkout($uid, $this->_id))
            {
                $this->setError($this->_db->getErrorMsg());

                return false;
            }

            return true;
        }

        return false;
    }

    function getItemData()
    {
        static $row;

        if(!isset($row))
        {
            $row = JTable::getInstance('Poll', 'Table');
            $row->load($this->_id);
        }

        return $row;
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_mijopolls.poll', 'poll', array('control' => 'params', 'load_data' => $loadData));

        if(empty($form))
        {
            return false;
        }

        return $form;
    }

    protected function loadFormData()
    {
        $data = JFactory::getApplication()->getUserState('com_mijopolls.edit.poll.data', array());

        if(empty($data))
        {
            $row = self::getItemData();

            if($row->id != 0)
            {
                $registry = new JRegistry($row->params);

                $data = $registry->toArray();
            }
        }

        return $data;
    }

    function store($data)
    {
        $row = $this->getTable();

        // Bind the form fields to the web link table
        if(!$row->bind($data))
        {
            $this->setError($this->_db->getErrorMsg());

            return false;
        }

        // Create the timestamp for the date
        $row->date = gmdate('Y-m-d H:i:s');

        // Make sure the web link table is valid
        if(!$row->check())
        {
            $this->setError($this->_db->getErrorMsg());

            return false;
        }

        // Store the web link table to the database
        if(!$row->store())
        {
            $this->setError($this->_db->getErrorMsg());

            return false;
        }

        return true;
    }

    //delete empty options to clear the db
    function deleteOption($id)
    {
        $query = "DELETE FROM #__mijopolls_options WHERE id = " . $this->_db->Quote((int) $id) . " LIMIT 1";
        $this->_db->setQuery($query);

        if(!$this->_db->query())
        {
            $this->setError($this->_db->getErrorMsg());

            return false;
        }

        return true;
    }
}