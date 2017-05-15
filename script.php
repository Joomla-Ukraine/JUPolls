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

// No Permission
defined('_JEXEC') or die ('Restricted access');

jimport('joomla.installer.installer');
jimport('joomla.filesystem.folder');

class com_JUPollsInstallerScript
{
    private $_current_version = null;
    private $_is_new_installation = true;

    public function preflight($type, $parent)
    {
        $app = JFactory::getApplication();

        /*
        $folders = array(
            JPATH_SITE . '/administrator/components/com_jupolls',
            JPATH_SITE . '/components/com_jupolls',
            JPATH_SITE . '/modules/mod_jupolls',
            JPATH_SITE . '/plugins/jupolls',
            JPATH_SITE . '/media/jupolls'
        );

        foreach ($folders AS $folder)
        {
            if(is_dir($folder))
            {
                $this->unlinkRecursive($folder, 1);
                $app->enqueueMessage('Delete folder: ' . $folder, 'message');
            }
        }
        */

        if(JFolder::create(JPATH_ROOT . '/images/polls'))
        {
            $app->enqueueMessage('Create folder: polls', 'message');
        }
        else
        {
            $app->enqueueMessage('Unable to create folder: polls', 'message');
        }

        $db = JFactory::getDBO();
        $db->setQuery('SELECT params FROM #__extensions WHERE element = "com_jupolls" AND type = "component"');
        $config = $db->loadResult();

        if(!empty($config))
        {
            $this->_is_new_installation = false;
        }
    }

    public function postflight($type, $parent)
    {
        $status = new JObject();
        $app    = JFactory::getApplication();

        $db  = JFactory::getDBO();
        $src = $parent->getParent()->getPath('source');

        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__jupolls_polls` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `title` varchar(255) NOT NULL default '',
		  `alias` varchar(255) NOT NULL default '',
		  `checked_out` int(11) NOT NULL default '0',
		  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
		  `published` tinyint(1) NOT NULL default '0',
		  `publish_up` datetime NOT NULL default '0000-00-00 00:00:00',
		  `publish_down` datetime default '0000-00-00 00:00:00',
		  `params` text NOT NULL,
		  `access` int(11) NOT NULL default '0',
		  `lag` int(11) NOT NULL default '0',
		  PRIMARY KEY  (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;");
        $db->execute();

        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__jupolls_options` (
		  `id` int(11) NOT NULL auto_increment,
		  `poll_id` int(11) NOT NULL default '0',
		  `text` text NOT NULL,
		  `link` varchar(255) DEFAULT NULL,
		  `ordering` int(11) NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `poll_id` (`poll_id`,`text`(1))
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;");
        $db->execute();

        $db->setQuery("CREATE TABLE IF NOT EXISTS `#__jupolls_votes` (
		  `id` bigint(20) NOT NULL auto_increment,
		  `date` datetime NOT NULL default '0000-00-00 00:00:00',
		  `option_id` int(11) NOT NULL default '0',
		  `poll_id` int(11) NOT NULL default '0',
		  `ip` int(10) unsigned NOT NULL,
		  `browser` varchar(155) NOT NULL,
		  `user_id` int(11) DEFAULT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `poll_id` (`poll_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;");
        $db->execute();

        $installer = new JInstaller();
        $installer->install($src . '/extensions/mod_jupolls');

        $installer = new JInstaller();
        $installer->install($src . '/extensions/plg_jupollssearch');

        if($this->_is_new_installation == true)
        {
            $this->_installJUPolls();
        }
        else
        {
            $this->_updateJUPolls();

            $query = $db->getQuery(true);
            $query->select('*');
            $query->from('#__extensions');
            $query->where('`type` = "component"');
            $query->where('`element` = "com_jupolls"');
            $db->setQuery($query);
            $manifests = $db->loadObjectList();

            foreach ($manifests AS $manifest)
            {
                $array = json_decode($manifest->manifest_cache, true);
            }

            $version = $array['version'];

            if(version_compare($version, '1.5.4', '<='))
            {
                $db = JFactory::getDbo();

                $db->setQuery("ALTER TABLE `#__jupolls_votes` ENGINE=InnoDB");
                $db->execute();

                $db->setQuery("ALTER TABLE `#__jupolls_options` ENGINE=InnoDB");
                $db->execute();

                $db->setQuery("ALTER TABLE `#__jupolls_polls` ENGINE=InnoDB");
                $db->execute();
            }
        }

        $this->_installationOutput($status);
    }

    protected function _installJUPolls()
    {
        if(empty($this->_current_version)) {
            return;
        }
    }

    protected function _updateJUPolls()
    {
        if(empty($this->_current_version)) {
            return;
        }
    }

    public function uninstall($parent)
    {
        $status = new JObject();

        $db = JFactory::getDBO();

        $db->setQuery("SELECT extension_id FROM #__extensions WHERE type = 'module' AND element = 'mod_jupolls' LIMIT 1");
        $id = $db->loadResult();

        if($id)
        {
            $installer = new JInstaller();
            $installer->uninstall('module', $id);
        }

        $db->setQuery("SELECT extension_id FROM #__extensions WHERE type = 'plugin' AND element = 'jupollssearch' AND folder = 'search' LIMIT 1");
        $id = $db->loadResult();

        if($id)
        {
            $installer = new JInstaller();
            $installer->uninstall('plugin', $id);
        }

        $this->_uninstallationOutput($status);
    }

    public function unlinkRecursive($dir, $deleteRootToo)
    {
        if(!$dh = @opendir($dir))
        {
            return;
        }

        while (false !== ($obj = readdir($dh)))
        {
            if($obj == '.' || $obj == '..')
            {
                continue;
            }

            if(!@unlink($dir . '/' . $obj))
            {
                $this->unlinkRecursive($dir . '/' . $obj, true);
            }
        }
        closedir($dh);

        if($deleteRootToo == 1)
        {
            @rmdir($dir);
        }

        return;
    }

    protected function _installationOutput($status)
    {
        ?>
        <h2>JUPolls Installation</h2>
        <h2><a href="index.php?option=com_jupolls">Go to JUPolls</a></h2>
        <table class="adminlist table table-striped">
            <thead>
            <tr>
                <th class="title" colspan="2"><?php echo JText::_('Extension'); ?></th>
                <th width="30%"><?php echo JText::_('Status'); ?></th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="3"></td>
            </tr>
            </tfoot>
            <tbody>
            <tr>
                <th colspan="3"><?php echo JText::_('Core'); ?></th>
            </tr>
            <tr class="row0">
                <td class="key" colspan="2"><?php echo 'JUPolls ' . JText::_('Component'); ?></td>
                <td><strong><?php echo JText::_('Installed'); ?></strong></td>
            </tr>
            <tr class="row1">
                <td class="key" colspan="2"><?php echo 'JUPolls ' . JText::_('Module'); ?></td>
                <td><strong><?php echo JText::_('Installed'); ?></strong></td>
            </tr>
            <tr>
                <th colspan="3"><?php echo JText::_('Plugins'); ?></th>
            </tr>
            <tr class="row1">
                <td class="key" colspan="2"><?php echo 'Content - Load JUPolls'; ?></td>
                <td><strong><?php echo JText::_('Installed'); ?></strong></td>
            </tr>
            <tr class="row0">
                <td class="key" colspan="2"><?php echo 'Search - JUPolls'; ?></td>
                <td><strong><?php echo JText::_('Installed'); ?></strong></td>
            </tr>
            </tbody>
        </table>
        <?php
    }

    private function _uninstallationOutput($status)
    {
        ?>
        <h2>JUPolls Removal</h2>
        <table class="adminlist table table-striped">
            <thead>
            <tr>
                <th class="title" colspan="2"><?php echo JText::_('Extension'); ?></th>
                <th width="30%"><?php echo JText::_('Status'); ?></th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="3"></td>
            </tr>
            </tfoot>
            <tbody>
            <tr>
                <th colspan="3"><?php echo JText::_('Core'); ?></th>
            </tr>
            <tr class="row0">
                <td class="key" colspan="2"><?php echo JText::_('Component'); ?></td>
                <td><strong><?php echo JText::_('Removed'); ?></strong></td>
            </tr>
            <tr class="row1">
                <td class="key" colspan="2"><?php echo 'JUPolls ' . JText::_('Module'); ?></td>
                <td><strong><?php echo JText::_('Removed'); ?></strong></td>
            </tr>
            <tr class="row0">
                <td class="key" colspan="2"><?php echo 'Search - JUPolls'; ?></td>
                <td><strong><?php echo JText::_('Removed'); ?></strong></td>
            </tr>
            </tbody>
        </table>
        <?php
    }
}