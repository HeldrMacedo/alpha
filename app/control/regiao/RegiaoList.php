<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TDropDown;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

class RegiaoList extends TPage
{
    private $form;
    private $datagrid;
    private $pageNavigation;

    use Adianti\base\AdiantiStandardListTrait;

    public function __construct()
    {
        parent::__construct();
        $this->setDatabase('permission');
        $this->setActiveRecord('Regiao');
        $this->setDefaultOrder('id', 'asc');
       
        $this->form = new BootstrapFormBuilder('form_search_regiaoList');
        $this->form->setFormTitle('Região');

        $nome = new TEntry('nome');

        $this->form->addFields([new TLabel('Nome')], [$nome]);
        $nome->setSize('70%');

        $this->form->setData(TSession::getValue('RegiaoList_filter_data'));

        $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction(array('RegiaoForm', 'onEdit')), 'fa:plus green');

        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $column_id   = new TDataGridColumn('id', 'Id', 'center', 50);
        $column_nome = new TDataGridColumn('nome', 'Nome', 'left');

        $column_nome->enableAutoHide(500);

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_nome);

        //adiciona  ação de ordenar ao clicar no header
        $order_id = new TAction(array($this, 'onReload'));
        $order_id->setParameter('order', 'id');
        $column_id->setAction($order_id);

        $order_nome = new TAction(array($this, 'onReload'));
        $order_nome->setParameter('order', 'nome');
        $column_nome->setAction($order_nome);

        // cria ação de editar
        $action_edit = new TDataGridAction(array('RegiaoForm', 'onEdit'));
        $action_edit->setButtonClass('btn btn-default');
        $action_edit->setLabel(_t('Edit'));
        $action_edit->setImage('far:edit blue ');
        $action_edit->setField('id');
        $this->datagrid->addAction($action_edit);

        // create DELETE action
        $action_del = new TDataGridAction(array($this, 'onDelete'));
        $action_del->setButtonClass('btn btn-default');
        $action_del->setLabel(_t('Delete'));
        $action_del->setImage('far:trash-alt red ');
        $action_del->setField('id');
        $this->datagrid->addAction($action_del);

        // create the datagrid model
        $this->datagrid->createModel();

        // create the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->enableCounters();
        $this->pageNavigation->setAction(new TAction(array($this, 'onReload')));
        $this->pageNavigation->setWidth($this->datagrid->getWidth());

        $panel = new TPanelGroup();
        $panel->add($this->datagrid)->style='overflow-x:auto;';
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
        try 
        {   
            TTransaction::open('permission');
            $data = (array) $this->form->getData();
            $userId = TSession::getValue('userid');
            $unit = (object) SystemUser::find($userId)->get_unit();

            $repository = new TRepository('Regiao');
            $limit = 10;
            
            $criteria = new TCriteria;
                                    
            if (empty($param['order']))
            {
                $param['order'] = 'id';
                $param['direction'] = 'asc';
            }
            
            $criteria->setProperties($param); // order, offset
            $criteria->setProperty('limit', $limit);

            $criteria->add(new TFilter('unit_id', '=', $unit->id));

            if (!empty($data['nome'])) {
                $criteria->add(new TFilter('nome', '=', $data['nome']));
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
        }catch(Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}