<?php

namespace Backend\Modules\ContentBlocks\Installer;

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

use Backend\Core\Engine\Model;
use Backend\Core\Installer\ModuleInstaller;
use Backend\Modules\ContentBlocks\Entity\ContentBlock;

/**
 * Installer for the content blocks module
 */
class Installer extends ModuleInstaller
{
    /**
     * Install the module
     */
    public function install()
    {
        // add the schema of the entity to the database
        Model::get('entity.create_schema')->forEntityClass(ContentBlock::class);

        // add 'content_blocks' as a module
        $this->addModule('ContentBlocks');

        // import locale
        $this->importLocale(dirname(__FILE__) . '/Data/locale.xml');

        // general settings
        $this->setSetting('ContentBlocks', 'max_num_revisions', 20);

        // module rights
        $this->setModuleRights(1, 'ContentBlocks');

        // action rights
        $this->setActionRights(1, 'ContentBlocks', 'Add');
        $this->setActionRights(1, 'ContentBlocks', 'Delete');
        $this->setActionRights(1, 'ContentBlocks', 'Edit');
        $this->setActionRights(1, 'ContentBlocks', 'Index');

        // set navigation
        $navigationModulesId = $this->setNavigation(null, 'Modules');
        $this->setNavigation(
            $navigationModulesId,
            'ContentBlocks',
            'content_blocks/index',
            array('content_blocks/add', 'content_blocks/edit')
        );
    }
}
