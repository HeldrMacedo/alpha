<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Registry\TSession;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;

class ExtracaoList extends TPage
{
  private $form;
  private $datadrid;
  private $pageNavigation;

  use Adianti\base\AdiantiStandardListTrait;

  public function __construct()
  {
    parent::__construct();
    $this->setDatabase('permission');
    $this->setActiveRecord('Extracao');
    $this->setDefaultOrder('id', 'asc');

    $this->form = new BootstrapFormBuilder('form_search_extracaoList');
    $this->form->setFormTitle('Extração');

    $descricao = new TEntry('descricao');

    $this->form->addFields([new TLabel('Descrição')], $descricao);
    $descricao->setSize('70%');

    $this->form->setData(TSession::getValue('ExtracaoList_filter_data'));

    $btn = $this->form->addAction(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search');
    $btn->class = 'btn btn-sm btn-primary';
    $this->form->addAction(_t('New'),  new TAction(array('ExtracaoForm', 'onEdit')), 'fa:plus green');

    $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
    $this->datagrid->style = 'width: 100%';

    $column_descricao = new T

  }
}
