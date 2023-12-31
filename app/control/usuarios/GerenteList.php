<?php

use Adianti\Base\TStandardList;
use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Core\AdiantiApplicationConfig;
use Adianti\Core\AdiantiCoreApplication;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TDateTime;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TDropDown;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Widget\Wrapper\TDBCombo;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

class GerenteList extends TStandardList
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    protected $transformCallback;

    public function __construct()
    {
        parent::__construct();
        
        $ini  = AdiantiApplicationConfig::get();
        
        parent::setDatabase('permission');            // defines the database
        parent::setActiveRecord('SystemUser');   // defines the active record
        parent::setDefaultOrder('name', 'asc');         // defines the default order
        parent::addFilterField('name', 'like', 'name'); // filterField, operator, formField
        parent::addFilterField('active', '=', 'active'); // filterField, operator, formField
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_gerente');
        $this->form->setFormTitle('Gerentes');
        

        // create the form fields
        $name = new TEntry('name');
        $active = new TCombo('active');
        
        $active->addItems( [ 'Y' => _t('Yes'), 'N' => _t('No') ] );
        
        // add the fields
        $this->form->addFields( [new TLabel(_t('Name'))], [$name] );
        $this->form->addFields( [new TLabel(_t('Active'))], [$active] );
        
        $name->setSize('70%');
        $active->setSize('70%');
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue('Gerente_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction(array('GerenteForm', 'onEdit')), 'fa:plus green');
        
        // creates a DataGrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        //$this->datagrid->datatable = 'true';
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);
        

        // creates the datagrid columns
        $column_name = new TDataGridColumn('name', _t('Name'), 'left');
        $column_login = new TDataGridColumn('login', _t('Login'), 'left');
        //$column_email = new TDataGridColumn('email', _t('Email'), 'left');
        $column_regiao = new TDataGridColumn('regiaoGerente->nome', 'Regiao', 'left');
        $column_active = new TDataGridColumn('active', _t('Active'), 'center');
        $column_term_policy = new TDataGridColumn('accepted_term_policy', _t('Terms of use and privacy policy'), 'center');
        
        $column_login->enableAutoHide(500);
        //$column_email->enableAutoHide(500);
        $column_active->enableAutoHide(500);
        $column_term_policy->enableAutoHide(500);
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_name);
        $this->datagrid->addColumn($column_login);
        //$this->datagrid->addColumn($column_email);
        $this->datagrid->addColumn($column_regiao);
        $this->datagrid->addColumn($column_active);
        
        if (!empty($ini['general']['require_terms']) && $ini['general']['require_terms'] == '1')
        {
            $this->datagrid->addColumn($column_term_policy);
        }
        
        $column_active->setTransformer( function($value, $object, $row) {
            $class = ($value=='N') ? 'danger' : 'success';
            $label = ($value=='N') ? _t('No') : _t('Yes');
            $div = new TElement('span');
            $div->class="label label-{$class}";
            $div->style="text-shadow:none; font-size:10pt;";
            $div->add($label);
            return $div;
        });
        
        $column_term_policy->setTransformer( function($value, $object, $row) {
            $class = (empty($value) || $value=='N') ? 'danger' : 'success';
            $label = (empty($value) || $value=='N') ? _t('No') : _t('Yes');
            $div = new TElement('span');
            $div->class="label label-{$class}";
            $div->style="text-shadow:none; font-size:12px; font-weight:lighter";
            $div->add($label);

            if ($value == 'Y')
            {
                $contents = [];
                $contents[] = TElement::tag('b',  _t('Date') . ':' ) . TElement::tag('p', TDateTime::convertToMask($object->accepted_term_policy_at, 'yyyy-mm-dd hh:ii:ss', 'dd/mm/yyyy hh:ii'));

                if ($object->accepted_term_policy_data)
                {
                    $data = json_decode($object->accepted_term_policy_data, true);

                    foreach($data as $key => $value)
                    {
                        $contents[] = TElement::tag('b', "{$key}:") . TElement::tag('p', $value);
                    }
                }
                $content = TElement::tag('div', implode('', $contents), ["style"=>"max-height: 200px;overflow-y:auto;"]);
                $div->{'poptitle'} = _t('Terms of use and privacy policy');
                $div->{'popcontent'} = $content->getContents();
                $div->{'popover'} = "true";
                $div->{'poptrigger'} = "click";
                $div->{'popside'} = "left";

                $div = TElement::tag('div', $div, ['title' => _t('Click here for more information')]);
            }

            return $div;
        });
        
        $order_name = new TAction(array($this, 'onReload'));
        $order_name->setParameter('order', 'name');
        $column_name->setAction($order_name);
        
        $order_login = new TAction(array($this, 'onReload'));
        $order_login->setParameter('order', 'login');
        $column_login->setAction($order_login);
        
        $order_email = new TAction(array($this, 'onReload'));
        $order_email->setParameter('order', 'email');
        //$column_email->setAction($order_email);
        
        // create EDIT action
        $action_edit = new TDataGridAction(array('GerenteForm', 'onEdit'));
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('far:edit blue');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);
        
        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        $action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('far:trash-alt red');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);
        
        // create CLONE action
        // $action_clone = new TDataGridAction(array($this, 'onClone'));
        // $action_clone->setButtonClass('btn btn-default');
        // $action_clone->setLabel(_t('Clone'));
        // $action_clone->setImage('far:clone green');
        // $action_clone->setField('id');
        // $this->datagrid->addAction($action_clone);
        
        // create ONOFF action
        $action_onoff = new TDataGridAction(array($this, 'onTurnOnOff'));
        $action_onoff->setButtonClass('btn btn-default');
        $action_onoff->setLabel(_t('Activate/Deactivate'));
        $action_onoff->setImage('fa:power-off orange');
        $action_onoff->setField('id');
        $this->datagrid->addAction($action_onoff);
        
        // create ONOFF action
        // $action_person = new TDataGridAction(array($this, 'onImpersonation'));
        // $action_person->setButtonClass('btn btn-default');
        // $action_person->setLabel(_t('Impersonation'));
        // $action_person->setImage('far:user-circle gray');
        // $action_person->setFields(['id','login']);
        // $this->datagrid->addAction($action_person);
        
        // create the datagrid model
        $this->datagrid->createModel();
        $this->datagrid->disableDefaultClick();

        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());
        
        $panel = new TPanelGroup();
        $panel->add($this->datagrid)->style = 'overflow-x:auto';
        $panel->addFooter($this->pageNavigation);
        
        // header actions
        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table fa-fw blue' );
        $dropdown->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf fa-fw red' );
        $dropdown->addAction( _t('Save as XML'), new TAction([$this, 'onExportXML'], ['register_state' => 'false', 'static'=>'1']), 'fa:code fa-fw green' );
        $panel->addHeaderWidget( $dropdown );
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }

    public function onReload($param = NULL)
    {
        try {
            TTransaction::open('permission');
            $data = (array) $this->form->getData();
            $userId = TSession::getValue('userid');
            $unit = (object) SystemUser::find($userId)->get_unit();

            $repository = new TRepository('SystemUser');
            $limit = 10;
            
            $criteria = new TCriteria;
                                    
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);

            $criteria->add(new TFilter('system_unit_id', '=', $unit->id));
            $criteria->add(new TFilter('id', 'IN','(SELECT system_user_id FROM system_user_group WHERE system_group_id = 3)'));

            if (!empty($data['id'])) {
                $criteria->add(new TFilter('id', '=', $data['id']));
            }

            if (!empty($data['name'])) {
                $criteria->add(new TFilter('name', '=', $data['name']));
            }

            if (!empty($data['email'])) {
                $criteria->add(new TFilter('email', '=', $data['email']));
            }

            if (!empty($data['active'])) {
                $criteria->add(new TFilter('active', '=', $data['active']));
            }

            $objects = $repository->load( $criteria );

            $this->datagrid->clear();
            
            if ($objects)
            {
                foreach($objects as $object)
                {
                    $this->datagrid->addItem($object);
                }
            }
            
            $criteria->resetProperties();
            $count = $repository->count($criteria);
            
            $this->pageNavigation->setCount($count); // count of records
            $this->pageNavigation->setProperties($param); // order, page
            $this->pageNavigation->setLimit($limit); // limit
                        
            TTransaction::close();
            $this->loaded = true;
      
            TTransaction::close();
        } catch (Exception $e) {

        }
        
    }

    /**
     * Turn on/off an user
     */
    public function onTurnOnOff($param)
    {
        try
        {
            TTransaction::open('permission');
            $user = SystemUser::find($param['id']);
            if ($user instanceof SystemUser)
            {
                $user->active = $user->active == 'Y' ? 'N' : 'Y';
                $user->store();
            }
            
            TTransaction::close();
            
            $this->onReload($param);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Clone group
     */
    public function onClone($param)
    {
        try
        {
            TTransaction::open('permission');
            $user = new SystemUser($param['id']);
            $user->cloneUser();
            TTransaction::close();
            
            $this->onReload();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
    
    /**
     * Impersonation user
     */
    public function onImpersonation($param)
    {
        try
        {
            $login_impersonated = TSession::getValue('login');

            TTransaction::open('permission');
            TSession::regenerate();
            $user = SystemUser::validate( $param['login'] );
            ApplicationAuthenticationService::loadSessionVars($user);
            SystemAccessLogService::registerLogin(true, $login_impersonated);
            AdiantiCoreApplication::gotoPage('EmptyPage');
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}