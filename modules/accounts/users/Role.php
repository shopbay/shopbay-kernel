<?php
/**
 * This file is part of Shopbay.org (http://shopbay.org)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code. 
 */

/**
 * Helper class for Rights.Role
 * @see developer-guide.md
 * @author kwlok
 */
class Role 
{
    /**
     * This is the default user role; Each sign up user will have this role assigned
     * @see SWebUser::isRegistered
     */
    const USER = 'User';
    /*
     * This role is assigned when user is activated (via the activation link)
     * Technically it is a Task
     * @see CAuthItem::TYPE_TASK
     * @see SWebUser::isActivated
     */
    const ACTIVATED = 'Activated';
    /*
     * This is the merchant role when user start setting first his/her first shop
     */
    const MERCHANT  = 'Merchant';
    /**
     * This is the shop customer role; Each sign up customer account at a particular shop will have this role assigned
     * @see SWebUser::isRegistered
     */
    const CUSTOMER = 'Customer';
    /*
     * This is the default admin role; Each admin user will have this role assigned
     * This is the Administrator role for back-office use
     */
    const ADMINISTRATOR = 'Administrator';
    /*
     * Below are all different authorization roles
     */
    const TAGS_MANAGER  = 'Tags Manager';
    const TICKETS_MANAGER = 'Tickets Manager';
    const TUTORIALS_CREATOR = 'Tutorials Creator';
    const TUTORIALS_PUBLISHER = 'Tutorials Publisher';
    const TUTORIAL_SERIES_CREATOR = 'Tutorial Series Creator';
    const TUTORIAL_SERIES_PUBLISHER = 'Tutorial Series Publisher';
    const PLANS_CREATOR = 'Plans Creator';
    const PLANS_APPROVER = 'Plans Approver';
    const PACKAGES_CREATOR = 'Packages Creator';
    const PACKAGES_APPROVER = 'Packages Approver';
    const SHOPS_MANAGER = 'Shops Manager';
    const WCM_MANAGER = 'WCM Manager';
    const NOTIFICATION_TEMPLATES_MANAGER = 'Notification Templates Manager';//for notification template management
    const CONFIGS_MANAGER = 'Configs Manager';
    const SHOP_PLANS_MANAGER = 'Shop Plans Manager';
    const THEMES_ADMIN  = 'Themes Admin';
    /**
     * A helper method to check which role needs susbcrption check
     * 
     * @see SubscriptionFilter::preFilter()
     * @param type $role
     * @return type
     */
    public static function requiresSubscriptionCheck($role)
    {
        return $role==Role::MERCHANT;
    }

}