<?php
/**
 * JUPolls
 *
 * @version 1.x
 * @package JUPolls
 * @author Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2016-2017 by Denys D. Nosov (http://joomla-ua.org)
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @license	GNU/GPL based on AcePolls www.joomace.net
 *
 * @copyright	2009-2011 Mijosoft LLC, www.mijosoft.com
 * @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @license		GNU/GPL based on AcePolls www.joomace.net
 *
 * @copyright (C) 2009 - 2011 Hristo Genev All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.afactory.org
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

?>
<div id="polls">
    <?php if ($params->get('show_poll_title')) : ?>
    <p class="top">
        <strong><a href="<?php echo JRoute::_('index.php?option=com_jupolls&view=poll&id='.$slug.$itemid); ?>"><?php echo $poll->title; ?></a></strong>
    </p>
    <?php endif; ?>

    <form action="<?php echo JRoute::_('index.php?option=com_jupolls&view=poll&id='.$slug.$itemid); ?>" method="post" name="poll_vote_<?php echo $poll->id;?>" id="poll_vote_<?php echo $poll->id;?>"  class="bottom">
        <?php if ($display_poll == 1) : ?>
        <div id="polldiv_<?php echo $poll->id;?>" class="poll">
            <?php
            $iclr = '1';
        	$i = 0;
        	foreach ($results as $row) :
                if ($iclr == 1):
                    $color = '';
                elseif($iclr == 2):
                    $color = ' progress-bar-info';
                elseif($iclr == 3):
                    $color = ' progress-bar-success';
                elseif($iclr == 4):
                    $color = ' progress-bar-warning';
                elseif($iclr == 5):
                    $color = ' progress-bar-danger';
                endif;

        		$percent = ($row->votes)? round((100*$row->hits)/$row->votes, 1):0;
        		$width = ($percent)? $percent:3;
            ?>
            <div class="form-group">
            	<label for="mod_voteid<?php echo $row->id;?>" class="<?php echo $tabclass_arr[$tabcnt].$params->get('moduleclass_sfx'); ?>  ">
            		<input type="radio" name="voteid" id="mod_voteid<?php echo $row->id;?>" value="<?php echo $row->id;?>"<?php echo ($i == 0 ? ' required' : ''); ?> />
        		    <?php
        			$poll = explode("===", $row->text);
        			echo strip_tags( html_entity_decode( trim($poll[0]) ) );
        			?>
            	</label>
                <div class="progress progress-striped">
                    <div class="progress-bar <?php echo $color; ?>" role="progressbar" style="width: <?php echo $percent; ?>%">
                        <?php echo $percent; ?>%
                    </div>
                </div>
        	</div>
            <?php
        		$i++;
                $iclr++;
                if($iclr==6) $iclr=1;
            endforeach;
            ?>
        </div>

	    <div class="form-group<?php echo (count($results) > 5 ? ' buttonscr' : ''); ?>" id="poll_buttons_<?php echo $poll->id;?>" >
	        <input type="submit" id="submit_vote_<?php echo $poll->id; ?>" name="task_button" class="btn btn-primary btn-sm" value="<?php echo JText::_('MOD_JUPOLLS_VOTE'); ?>" <?php echo $disabled; ?> />
	        <span class="right text-muted"><i class="fa fa-bar-chart"></i> <?php echo JText::_('MOD_JUPOLLS_TOTAL_VOTES').": ".$row->votes; ?></span>
	    </div>
    	<input type="hidden" name="option" value="com_jupolls" />
    	<input type="hidden" name="id" value="<?php echo $poll->id;?>" />
    	<input type="hidden" name="task" value="vote" />
        <?php echo JHTML::_('form.token');  ?>
    </form>

    <?php else : ?>

    <div id="polldiv_<?php echo $poll->id;?>" class="poll <?php echo (count($results) > 5 ? 'pollscr' : ''); ?>">
        <?php
        $iclr = '1';
		foreach ($results as $row) :
        if ($iclr == 1):
            $color = '';
        elseif($iclr == 2):
            $color = ' progress-bar-info';
        elseif($iclr == 3):
            $color = ' progress-bar-success';
        elseif($iclr == 4):
            $color = ' progress-bar-warning';
        elseif($iclr == 5):
            $color = ' progress-bar-danger';
        endif;

			$percent = ($row->votes) ? round((100*$row->hits)/$row->votes, 1) : 0;
			$width = ($percent) ? $percent : 3;

			if($params->get('only_one_color'))
				$background_color = $params->get('poll_bars_color');
			else
				$background_color = $row->color; ?>
            <div class="form-group">
			    <?php
				$poll = explode("===", $row->text);
				echo strip_tags( html_entity_decode( trim($poll[0]) ) );
				?>
                <div class="progress progress-striped">
                    <div class="progress-bar <?php echo $color; ?>" role="progressbar" style="width: <?php echo $percent; ?>%">
                        <?php echo $percent; ?>%
                    </div>
                </div>
        	</div>
        <?php
        $iclr++;
        if($iclr==6) $iclr=1;
        endforeach;
        ?>
    </div>

	<div class="clearfix text-muted">
	    <span class="pull-right"><i class="fa fa-bar-chart"></i> <?php echo JText::_('MOD_JUPOLLS_TOTAL_VOTES').": ".$row->votes; ?></span>
	</div>
    
<?php endif;?>

</div>