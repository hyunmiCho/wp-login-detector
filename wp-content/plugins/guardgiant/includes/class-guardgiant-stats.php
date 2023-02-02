<?php
/**
 * The class related to guardgiant performance 'stats'
 *
 * @since	2.2.3
 *
 * @package    Guardgiant
 * @subpackage Guardgiant/includes
 */
class Guardgiant_Stats {

    public static function increment_stat_count($stat_name)
    {
        $guardgiant_stats = get_option('guardgiant-stats');

        if (!$guardgiant_stats) {
            $guardgiant_stats = array();
            add_option('guardgiant-stats',$guardgiant_stats);
        }

        if (!isset($guardgiant_stats[$stat_name]))
        {
            $guardgiant_stats[$stat_name] = 0;
        }
        
        $guardgiant_stats[$stat_name] = $guardgiant_stats[$stat_name] + 1;

        update_option('guardgiant-stats',$guardgiant_stats);
    }

}
