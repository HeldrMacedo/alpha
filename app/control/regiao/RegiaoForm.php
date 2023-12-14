<?php

use Adianti\Base\TStandardForm;
use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TRepository;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Wrapper\BootstrapFormBuilder;

class RegiaoForm extends TStandardForm
{
    protected $form;

    public function __construct()
    {
        parent::__construct();

        $this->setDatabase('permission');
        $this->setActiveRecord('Regiao');

        $this->form = new BootstrapFormBuilder('form_RegiaoForm');
        $this->form->setFormTitle('Região');

        $id     = new TEntry('id');
        $nome   = new TEntry('nome');

        $this->form->addFields( [new TLabel('Id')], [$id] );
        $this->form->addFields([new TLabel('Nome')], [$nome]);

        $id->setEditable(FALSE);
        $id->setSize('30%');
        $nome->setSize('70%');
        $nome->addValidation( _t('Name'), new TRequiredValidator );

        $btn = $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('Clear'),  new TAction(array($this, 'onEdit')), 'fa:eraser red');
        $this->form->addActionLink(_t('Back'),new TAction(array('RegiaoList','onReload')),'far:arrow-alt-circle-left blue');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', 'RegiaoList'));
        $container->add($this->form);
        
        parent::add($container);
    }

    public function onSave()
    {
        try 
        {
            TTransaction::open('permission');

            $data = $this->form->getData();
            $this->form->setData($data);

            $userId = TSession::getValue('userid');
            $unit = (object) SystemUser::find($userId)->get_unit();

            $data->unit_id = $unit->id;
            
            $object = new Regiao();
            $object->fromArray( (array) $data );
            $object->store();            

            $data = new stdClass;
            $data->id = $object->id;
            TForm::sendData('form_RegiaoForm', $data);            

            TTransaction::close(); 
            new TMessage('info', _t('Record saved')); 
        }catch (Exception $e) 
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

}