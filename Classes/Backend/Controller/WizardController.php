<?php
namespace Sci\SciApi\Backend\Controller;

/***
 *
 * This file is part of the "Content Block Registration API" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *
 ***/
/**
 * Wizard
 */
class WizardController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * action new
     * 
     * @return void
     */
    public function newAction()
    {
        // Nothing to do
    }

    /**
     * action create
     * 
     * @return void
     */
    public function createAction()
    {
        $this->addFlashMessage('The object was created. Please be aware that this action is publicly accessible unless you implement an access check. See https://docs.typo3.org/typo3cms/extensions/extension_builder/User/Index.html', '', \TYPO3\CMS\Core\Messaging\AbstractMessage::WARNING);
        
        // TODO: create the content block
        
        $this->redirect('new');
    }

}
