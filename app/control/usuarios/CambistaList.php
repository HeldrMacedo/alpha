<?php

use Adianti\Base\TStandardList;
use Adianti\Control\TAction;
use Adianti\Registry\TSession;
use Adianti\Widget\Base\TElement;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TPageNavigation;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TDropDown;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

class CambistaList extends TStandardList
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
        
        parent::setDatabase('permission');
        parent::setActiveRecord('Cambista');
        parent::setDefaultOrder('nome', 'ASC');
        parent::addFilterField('nome', 'like', 'nome');

        $this->form = new BootstrapFormBuilder('form_seach_cambista');
        $this->form->setFormTitle('Cambistas');

        $nome = new TEntry('nome');

        $this->form->addFields([new TLabel('Nome')], [$nome]);

        $nome->setSize('70%');

        $this->form->setData(TSession::getValue('Cambistas_filter_data'));

        $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addAction(_t('New'),  new TAction(array('CambistaForm', 'onEdit')), 'fa:plus green');

        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->setHeight(320);

        $column_regiao          = new TDataGridColumn('regiao->nome', 'Região', 'left');
        $column_nome            = new TDataGridColumn('nome', 'Nome', 'left');
        $column_login           = new TDataGridColumn('usuario->login', 'Login', 'left');
        $column_comissao        = new TDataGridColumn('comissao', 'Comissão', 'left');
        $column_exibe           = new TDataGridColumn('exibe_comissao', 'Exibe Comissão', 'left');
        $column_limite          = new TDataGridColumn('limite_venda', 'Limite Venda', 'left');
        $column_exibe_premiacao = new TDataGridColumn('exibe_premiacao', 'Exibe Premiação', 'left');
        $column_ativo           = new TDataGridColumn('usuario->active', 'Ativo', 'center');

        $this->datagrid->addColumn($column_regiao);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_login);
        $this->datagrid->addColumn($column_comissao);
        $this->datagrid->addColumn($column_exibe);
        $this->datagrid->addColumn($column_limite);
        $this->datagrid->addColumn($column_exibe_premiacao);
        $this->datagrid->addColumn($column_ativo);

        $column_ativo->setTransformer( function($value, $object, $row) {
            $class = ($value=='N') ? 'danger' : 'success';
            $label = ($value=='N') ? _t('No') : _t('Yes');
            $div = new TElement('span');
            $div->class="label label-{$class}";
            $div->style="text-shadow:none; font-size:10pt;";
            $div->add($label);
            return $div;
        });

        $this->datagrid->createModel();
        $this->datagrid->disableDefaultClick();

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
        
    }
}