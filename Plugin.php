<?php namespace Pikanji\DbQueueManager;

use Backend;
use Backend\Models\UserRole;
use System\Classes\PluginBase;

/**
 * DbQueueManager Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @return array[]
     * @noinspection PhpMissingReturnTypeInspection to match the signature of overridden method
     */
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
                'keywords'    => 'queue job',
                'permissions' => ['pikanji.dbqueuemanager.access_background_jobs'],
            ]
        ];
    }

    /**
     * @return array[]
     * @noinspection PhpMissingReturnTypeInspection to match the signature of overridden method
     */
    public function registerPermissions()
    {
        return [
            'pikanji.dbqueuemanager.access_background_jobs' => [
                'label' => 'pikanji.dbqueuemanager::lang.settings.jobs.permission_label',
                'tab' => 'system::lang.permissions.name',
                'order' => 1000,
                'roles' => UserRole::CODE_DEVELOPER,
            ],
        ];
    }
}
