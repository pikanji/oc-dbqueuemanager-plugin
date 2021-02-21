<?php namespace Pikanji\DbQueueManager;

use Backend;
use System\Classes\PluginBase;

/**
 * DbQueueManager Plugin Information File
 */
class Plugin extends PluginBase
{
    public function registerSettings()
    {
        return [
            'jobs' => [
                'label'       => 'pikanji.dbqueuemanager::lang.settings.jobs.label',
                'description' => 'pikanji.dbqueuemanager::lang.settings.jobs.description',
                'category'    => 'pikanji.dbqueuemanager::lang.settings.jobs.category',
                'icon'        => 'icon-tasks',
                'url'         => Backend::url('pikanji/dbqueuemanager/jobs'),
                'order'       => 500,
                'keywords'    => 'queue job'
            ]
        ];
    }
}
