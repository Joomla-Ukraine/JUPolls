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

$iclr = '1';
foreach ($this->options as $row)
{
    if($iclr == 1):
        $color = '';
    elseif($iclr == 2):
        $color = ' progress-bar-info bar-info';
    elseif($iclr == 3):
        $color = ' progress-bar-success bar-success';
    elseif($iclr == 4):
        $color = ' progress-bar-warning bar-warning';
    elseif($iclr == 5):
        $color = ' progress-bar-danger bar-danger';
    endif;

    $percent = $row->percent;
    $width   = ($percent < 3 ? 3 : $percent);

    ?>
    <div class="form-group">
        <?php
        $poll = explode("===", $row->text);
        if($poll[1])
        {
            echo '<a href="' . trim($poll[1]) . '">' . html_entity_decode(trim($poll[0])) . '</a>';
        }
        else
        {
            echo html_entity_decode(trim($poll[0]));
        }
        ?>
        <?php if($this->params->get('show_hits')) : ?>
            <span class="pull-right text-grey">
                <i class="icon-list fa fa-chart-bar"></i> <?php echo $row->hits; ?>
            </span>
        <?php endif; ?>
        <div class="progress progress-striped">
            <div
                    class="progress-bar bar <?php echo $color; ?>"
                    role="progressbar"
                    aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100"
                    style="width: <?php echo $width; ?>%"
            >
                <?php echo $percent; ?>%
            </div>
        </div>
    </div>
    <?php

    $iclr++;
    if($iclr == 6)
    {
        $iclr = 1;
    }
}