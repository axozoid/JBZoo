<?php
/**
 * JBZoo App is universal Joomla CCK, application for YooTheme Zoo component
 *
 * @package     jbzoo
 * @version     2.x Pro
 * @author      JBZoo App http://jbzoo.com
 * @copyright   Copyright (C) JBZoo.com,  All rights reserved.
 * @license     http://jbzoo.com/license-pro.php JBZoo Licence
 * @coder       Denis Smetannikov <denis@jbzoo.com>
 */

// no direct access
defined('_JEXEC') or die('Restricted access');


/**
 * Class JBToolsJBuniversalController
 * JBZoo tools controller for back-end
 */
class JBCartJBuniversalController extends JBUniversalController
{
    /**
     * @var JBCartElementHelper
     */
    protected $_element = null;

    /**
     * @var JBCartPositionHelper
     */
    protected $_position = null;

    protected $_extensions;

    /**
     * @param array $app
     * @param array $config
     */
    public function __construct($app, $config = array())
    {
        parent::__construct($app, $config);

        $this->_element  = $this->app->jbcartelement;
        $this->_position = $this->app->jbcartposition;

        // default
        $this->element  = $this->app->jbrequest->get('element');
        $this->layout   = $this->app->jbrequest->get('layout');
        $this->saveTask = 'savePositions';
    }

    /**
     * Index action
     */
    public function index()
    {
        $this->renderView();
    }

    /**
     * Cart config action
     */
    public function config()
    {
        if ($this->_jbrequest->isPost()) {
            $this->_config->setGroup('cart.config', $this->_jbrequest->getAdminForm());
            $this->setRedirect($this->app->jbrouter->admin(), JText::_('JBZOO_CONFIG_SAVED'));
        }

        $this->configData = $this->_config->getGroup('cart.config');

        $this->renderView();
    }

    /**
     * Show payment links
     */
    public function paymentLinks()
    {
        $appId = (int)$this->_jbrequest->get('app_id');

        $this->resultUrl  = $this->app->jbrouter->payment($appId, 'callback');
        $this->successUrl = $this->app->jbrouter->payment($appId, 'success');
        $this->failUrl    = $this->app->jbrouter->payment($appId, 'fail');

        $this->app->jbdoc->disableTmpl();
        $this->renderView();
    }

    /**
     * Notification config action
     */
    public function notification()
    {
        $this->groupList = $this->_element->getGroups(array(
            JBCart::ELEMENT_TYPE_NOTIFICATION,
            JBCart::ELEMENT_TYPE_MODIFIERITEM
        ));

        $this->positions = $this->_position->loadPositions(JBCart::CONFIG_NOTIFICATION, $this->app->jbeventmanager->getEventsName());
        $this->groupKey  = JBCart::CONFIG_NOTIFICATION;
        $this->renderView();
    }

    /**
     * Modifier list action
     */
    public function modifier()
    {
        $this->groupList = $this->_element->getGroups(array(
            JBCart::ELEMENT_TYPE_MODIFIERPRICE
        ));
        $this->positions = $this->_position->loadPositions(JBCart::CONFIG_MODIFIERS, array(
            JBCart::MODIFIER_ORDER,
            JBCart::MODIFIER_ITEM,
        ));

        $this->groupKey = JBCart::CONFIG_MODIFIERS;
        $this->renderView();
    }

    /**
     * Validator list action
     */
    public function validator()
    {
        $this->groupList = $this->_element->getGroups(array(JBCart::ELEMENT_TYPE_VALIDATOR));
        $this->positions = $this->_position->loadPositions(JBCart::CONFIG_VALIDATORS, array(JBCart::DEFAULT_POSITION));

        $this->groupKey = JBCart::CONFIG_VALIDATORS;
        $this->renderView();
    }

    /**
     * Payment list action
     */
    public function payment()
    {
        $this->groupList = $this->_element->getGroups(array(JBCart::ELEMENT_TYPE_PAYMENT));
        $this->positions = $this->_position->loadPositions(JBCart::CONFIG_PAYMENTS, array(JBCart::DEFAULT_POSITION));

        $this->groupKey = JBCart::CONFIG_PAYMENTS;
        $this->renderView();
    }

    /**
     * Shipping list action
     */
    public function shipping()
    {
        $this->groupList = $this->_element->getGroups(array(JBCart::ELEMENT_TYPE_SHIPPING));
        $this->positions = $this->_position->loadPositions(JBCart::CONFIG_SHIPPINGS, array(JBCart::DEFAULT_POSITION));

        $this->groupKey = JBCart::CONFIG_SHIPPINGS;
        $this->renderView();
    }

    /**
     * Price param list action
     */
    public function price()
    {
        $this->groupList  = $this->_element->getGroups(array(JBCart::ELEMENT_TYPE_PRICE));
        $this->layoutList = $this->app->jbpriceparams->getJBPriceElements();

        $element         = $this->_jbrequest->get('element', key($this->layoutList));
        $this->positions = $this->_position->loadPositions(JBCart::CONFIG_PRICE . '.' . $element, array(JBCart::DEFAULT_POSITION));

        $this->groupKey = JBCart::CONFIG_PRICE;
        $this->saveTask = 'saveElementPositions';

        $this->renderView();
    }

    /**
     * Validator list action
     */
    public function statusEvents()
    {
        $this->groupList = $this->_element->getGroups(array(
            JBCart::ELEMENT_TYPE_NOTIFICATION,
            JBCart::ELEMENT_TYPE_MODIFIERPRICE,
            JBCart::ELEMENT_TYPE_MODIFIERITEM
        ));

        $jbstatus = $this->app->jbcartstatus;

        $statusGroups = array(
            JBCart::STATUS_ORDER    => $jbstatus->getList(JBCart::STATUS_ORDER),
            JBCart::STATUS_PAYMENT  => $jbstatus->getList(JBCart::STATUS_PAYMENT),
            JBCart::STATUS_SHIPPING => $jbstatus->getList(JBCart::STATUS_SHIPPING),
        );

        $statusList = array();
        foreach ($statusGroups as $type => $statuses) {
            foreach ($statuses as $status) {
                $positionKey              = $type . '__' . $status->getCode();
                $statusList[$positionKey] = $positionKey;
            }
        }

        $this->positions = $this->_position->loadPositions(JBCart::CONFIG_STATUS_EVENTS, array_keys($statusList));

        $this->groupKey = JBCart::CONFIG_STATUS_EVENTS;
        $this->renderView();
    }

    /**
     * Currency list action
     */
    public function currency()
    {
        $this->groupList = $this->_element->getGroups(array(JBCart::ELEMENT_TYPE_CURRENCY));
        $this->positions = $this->_position->loadPositions(JBCart::CONFIG_CURRENCIES, array(JBCart::DEFAULT_POSITION));
        $this->groupKey  = JBCart::CONFIG_CURRENCIES;

        $this->renderView();
    }

    /**
     * Status list action
     */
    public function status()
    {
        $this->groupList = $this->_element->getGroups(array(JBCart::ELEMENT_TYPE_STATUS));
        $this->positions = $this->_position->loadPositions(JBCart::CONFIG_STATUSES, array(
            JBCart::STATUS_ORDER,
            JBCart::STATUS_PAYMENT,
            JBCart::STATUS_SHIPPING,
        ));

        $this->groupKey = JBCart::CONFIG_STATUSES;
        $this->renderView();
    }

    /**
     * Field list action
     */
    public function fields()
    {
        $this->groupList = $this->_element->getGroups(array(JBCart::ELEMENT_TYPE_ORDER));
        $this->positions = $this->_position->loadPositions(JBCart::CONFIG_FIELDS, array(JBCart::DEFAULT_POSITION));
        $this->groupKey  = JBCart::CONFIG_FIELDS;

        $this->renderView();
    }

    /**
     * Field list action
     */
    public function emailTmpl()
    {
        $renderer = $this->app->jbrenderer->create('email');

        $this->layoutList = $renderer->getLayouts('email');
        $this->layout     = $this->_jbrequest->get('layout', key($this->layoutList));
        $this->app->request->set('layout', $this->layout);

        $this->positionList = $renderer->getPositions(JBCart::ELEMENT_TYPE_EMAIL . '.' . $this->layout);
        $this->groupList    = $this->_element->getGroups(array(JBCart::ELEMENT_TYPE_EMAIL));
        $this->positions    = $this->_position->loadPositionsTmpl(JBCart::CONFIG_EMAIL_TMPL . '.' . $this->layout, JBCart::CONFIG_EMAIL_TMPL, $this->positionList);
        $this->ordersList   = $this->_getOrdersList();

        $this->groupKey = JBCart::CONFIG_EMAIL_TMPL;
        $this->app->jbassets->admin();
        $this->renderView();

    }

    /**
     * Field list action
     */
    public function cartTmpl()
    {
        $renderer         = $this->app->jbrenderer->create('order');
        $this->layoutList = $renderer->getLayouts('order');

        $this->layout = $this->_jbrequest->get('layout', key($this->layoutList));

        $this->positionList   = $renderer->getPositions('order.' . $this->layout);
        $this->dragElements   = $this->_position->loadElements(JBCart::CONFIG_FIELDS);
        $this->elementsParams = $this->_position->loadParams(JBCart::CONFIG_FIELDS_TMPL . '.' . $this->layout);
        $this->positions      = $this->_position->loadPositionsTmpl(JBCart::CONFIG_FIELDS_TMPL . '.' . $this->layout, JBCart::CONFIG_FIELDS, $this->positionList);

        $this->groupKey = JBCart::CONFIG_FIELDS_TMPL;

        $this->renderView();
    }

    /**
     *
     */
    public function jbpriceFilterTmpl()
    {
        $renderer = $this->app->jbrenderer->create('jbpricefilter');

        $this->elementList = $this->app->jbpriceparams->getJBPriceElements();
        $this->layoutList  = $renderer->getLayouts('jbpricefilter');

        $this->layout  = $this->_jbrequest->get('layout', key($this->layoutList));
        $this->element = $this->_jbrequest->get('element', key($this->elementList));

        $this->positionList = $renderer->getPositions('jbpricefilter.' . $this->layout);

        $this->systemElements = $this->_element->getSystemTmpl(JBCart::CONFIG_PRICE);
        $this->dragElements   = $this->_position->loadElements(JBCart::CONFIG_PRICE);

        $confName             = JBCart::CONFIG_PRICE_TMPL_FILTER . '.' . $this->element . '.' . $this->layout;
        $this->elementsParams = $this->_position->loadParams($confName);
        $this->positions      = $this->_position->loadPositionsTmpl($confName, JBCart::CONFIG_PRICE, $this->positionList);

        $this->saveTask = 'saveElementPositions';
        $this->groupKey = JBCart::CONFIG_PRICE_TMPL_FILTER;
        $this->renderView();
    }

    /**
     * Field list action
     */
    public function jbpriceTmpl()
    {
        $renderer = $this->app->jbrenderer->create('jbprice');

        $this->elementList = $this->app->jbpriceparams->getJBPriceElements();
        $this->layoutList  = $renderer->getLayouts('jbprice');

        $this->layout  = $this->_jbrequest->get('layout', key($this->layoutList));
        $this->element = $this->_jbrequest->get('element', key($this->elementList));

        $this->positionList = $renderer->getPositions('jbprice.' . $this->layout);

        $this->dragElements   = $this->_position->loadElements(JBCart::ELEMENT_TYPE_PRICE);
        $this->systemElements = $this->_element->getSystemTmpl(JBCart::CONFIG_PRICE);

        $confName             = JBCart::CONFIG_PRICE_TMPL . '.' . $this->element . '.' . $this->layout;
        $this->elementsParams = $this->_position->loadParams($confName);
        $this->positions      = $this->_position->loadPositionsTmpl($confName, JBCart::CONFIG_PRICE, $this->positionList);

        $this->saveTask = 'saveElementPositions';
        $this->groupKey = JBCart::CONFIG_PRICE_TMPL;
        $this->renderView();
    }

    /**
     * Shipping fields
     */
    public function shippingField()
    {
        $this->groupList = $this->_element->getGroups(array(JBCart::ELEMENT_TYPE_SHIPPINGFIELD));
        $this->positions = $this->_position->loadPositions(JBCart::CONFIG_SHIPPINGFIELDS, array(JBCart::DEFAULT_POSITION));
        $this->groupKey  = JBCart::CONFIG_SHIPPINGFIELDS;

        $this->renderView();
    }

    /**
     * Custom save action for any positions data
     */
    public function savePositions()
    {
        // session token
        $this->app->session->checkToken() or jexit('Invalid Token');

        $defaultRedirect = $this->app->jbrouter->admin(array('task' => 'index'));
        if (!$this->_jbrequest->isPost()) {
            $this->setRedirect($defaultRedirect);
        }

        $positions = $this->_jbrequest->getArray('positions');
        $group     = $this->_jbrequest->get('group');
        $layout    = $this->_jbrequest->get('layout');
        $redirect  = $this->_jbrequest->get('redirect', $defaultRedirect);

        $this->_position->save($group, $positions, $layout);

        $this->setRedirect($redirect, JText::_('JBZOO_ADMIN_MESSAGE_SAVED'));
    }

    /**
     * Custom save action for any positions data
     */
    public function saveElementPositions()
    {
        // session token
        $this->app->session->checkToken() or jexit('Invalid Token');

        $defaultRedirect = $this->app->jbrouter->admin(array('task' => 'index'));
        if (!$this->_jbrequest->isPost()) {
            $this->setRedirect($defaultRedirect);
        }

        $positions = $this->_jbrequest->getArray('positions');
        $group     = $this->_jbrequest->get('group');
        $layout    = $this->_jbrequest->get('layout');
        $element   = $this->_jbrequest->get('element');
        $redirect  = $this->_jbrequest->get('redirect', $defaultRedirect);

        $this->_position->savePrice($group, $positions, $layout, $element);

        $this->setRedirect($redirect, JText::_('JBZOO_ADMIN_MESSAGE_SAVED'));
    }

    /**
     * Add new element row (ajax calling)
     */
    public function addElement()
    {
        // get request vars
        $elementType  = $this->_jbrequest->getWord('elementType');
        $elementGroup = $this->_jbrequest->getWord('elementGroup');

        // load element
        $this->element             = $this->_element->create($elementType, $elementGroup);
        $this->element->identifier = $this->app->utility->generateUUID();

        if ($this->element->getMetaData('core') == 'true') {
            $this->element->identifier = '_' . strtolower($elementType);
        }

        $this->app->jbdoc->disableTmpl();
        $this->renderView();
    }

    /**
     * TODO Delete this method
     * Method using to take data from element with ajax
     */
    public function callElement()
    {
        $element = $this->app->request->getCmd('element', '');
        $method  = $this->app->request->getCmd('method', '');
        $layout  = $this->app->request->getCmd('layout', '');
        $args    = $this->app->request->getVar('args', array(), 'default', 'array');

        $positions = $this->_position->loadPositions(JBCart::ELEMENT_TYPE_NOTIFICATION);
        $positions = $this->app->data->create($positions);

        $key      = $positions->searchRecursive($element);
        $position = $this->app->data->create($positions->get($key));

        // get element and execute callback method
        if ($element = $this->getEmailElement()) {
            $element->callback($method, $args);
        }
    }

    public function getEmailElement()
    {
        $elements = $this->_position->loadElements(JBCart::ELEMENT_TYPE_NOTIFICATION);
        $element  = null;
        if (!empty($elements['_sendemail'])) {
            $element = $elements['_sendemail'];
        }

        return $element;
    }

    /**
     *
     */
    public function getPreview()
    {
        $id = $this->app->request->getCmd('id');

        $model = JBModelOrder::model();
        $order = $model->getById($id);

        $element = $this->getEmailElement();
        $element->setSubject($order);

        $html = $element->notify();

        echo $html;
    }

    /**
     * @return mixed|string
     */
    public function files()
    {
        $files = array();
        $path  = ltrim($this->app->request->get('path', 'string'), '/');
        $path  = empty($path) ? '' : $path . '/';
        foreach ($this->app->path->dirs('root:' . $path) as $dir) {
            $files[] = array('name' => basename($dir), 'path' => $path . $dir, 'type' => 'folder');
        }
        foreach ($this->app->path->files('root:' . $path, false, '/^.*(' . $this->_extensions . ')$/i') as $file) {
            $files[] = array('name' => basename($file), 'path' => $path . $file, 'type' => 'file');
        }

        echo json_encode($files);
    }

    /**
     * @return array
     */
    protected function _getOrdersList()
    {
        $result = array();
        $model  = JBModelOrder::model();
        $list   = $model->getList(array(
            'limit' => 10
        ));

        if (!empty($list)) {
            foreach ($list as $id => $order) {
                $result[$id] = JText::sprintf('JBZOO_EMAILTMPL_PREVIEW_ORDERNAME', $order->getName());
            }
        }

        return $result;
    }

}
