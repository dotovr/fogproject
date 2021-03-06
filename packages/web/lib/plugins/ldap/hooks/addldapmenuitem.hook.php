<?php
/**
 * Adds the menu item for this plugin
 *
 * PHP version 5
 *
 * @category AddLDAPMenuItem
 * @package  FOGProject
 * @author   Fernando Gietz <nah@nah.com>
 * @author   george1421 <nah@nah.com>
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
/**
 * Adds the menu item for this plugin
 *
 * @category AddLDAPMenuItem
 * @package  FOGProject
 * @author   Fernando Gietz <nah@nah.com>
 * @author   george1421 <nah@nah.com>
 * @author   Tom Elliott <tommygunsster@gmail.com>
 * @license  http://opensource.org/licenses/gpl-3.0 GPLv3
 * @link     https://fogproject.org
 */
class AddLDAPMenuItem extends Hook
{
    public $name = 'AddLDAPMenuItem';
    public $description = 'Add menu item for LDAP';
    public $author = 'Fernando Gietz';
    public $active = true;
    public $node = 'ldap';
    /**
     * Sets the menu item into the menu
     *
     * @param mixed $arguments the item to adjust
     *
     * @return void
     */
    public function menuData($arguments)
    {
        if (!in_array($this->node, (array)$_SESSION['PluginsInstalled'])) {
            return;
        }
        $this->arrayInsertAfter(
            'storage',
            $arguments['main'],
            $this->node,
            array(
                _('LDAP Management'),
                'fa fa-key fa-2x'
            )
        );
    }
    /**
     * Adds the plugin page to the search page lists
     *
     * @param mixed $arguments the item to adjust
     *
     * @return void
     */
    public function addSearch($arguments)
    {
        if (!in_array($this->node, (array)$_SESSION['PluginsInstalled'])) {
            return;
        }
        array_push($arguments['searchPages'], $this->node);
    }
    /**
     * Adds the plugin page to use internalized objects
     *
     * @param mixed $arguments the item to adjust
     *
     * @return void
     */
    public function addPageWithObject($arguments)
    {
        if (!in_array($this->node, (array)$_SESSION['PluginsInstalled'])) {
            return;
        }
        array_push($arguments['PagesWithObjects'], $this->node);
    }
}
$AddLDAPMenuItem = new AddLDAPMenuItem();
$HookManager->register('MAIN_MENU_DATA', array($AddLDAPMenuItem, 'menuData'));
$HookManager->register('SEARCH_PAGES', array($AddLDAPMenuItem, 'addSearch'));
$HookManager->register('PAGES_WITH_OBJECTS', array($AddLDAPMenuItem, 'addPageWithObject'));
