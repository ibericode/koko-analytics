<?php

namespace KokoAnalytics;

class Dashboard_Standalone extends Dashboard
{
    protected function get_base_url()
    {
        return Router::url('dashboard-standalone');
    }

    public function show()
    {
        require KOKO_ANALYTICS_PLUGIN_DIR . '/src/Resources/views/standalone.php';
    }
}
