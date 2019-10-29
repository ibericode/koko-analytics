<?php

namespace ZP;

class Plugin
{
    public function init()
    {
        add_filter( 'pre_update_option_active_plugins',               array( $this, 'filter_active_plugins' ) );

        // TODO: Symlink collect file
    }

    public function filter_active_plugins($plugins)
    {
        if (empty( $plugins)) {
            return $plugins;
        }

        $f = preg_quote( basename( plugin_basename(ZP_PLUGIN_FILE) ) );

        return array_merge(
            preg_grep( '/' . $f . '$/', $plugins ),
            preg_grep( '/' . $f . '$/', $plugins, PREG_GREP_INVERT )
        );
    }
}