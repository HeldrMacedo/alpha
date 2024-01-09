<?php

use Adianti\Control\TAction;
use Adianti\Control\TPage;
use Adianti\Database\TCriteria;
use Adianti\Database\TFilter;
use Adianti\Database\TTransaction;
use Adianti\Registry\TSession;
use Adianti\Validator\TEmailValidator;
use Adianti\Validator\TRequiredValidator;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Form\TCombo;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TPassword;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Widget\Wrapper\TDBUniqueSearch;
use Adianti\Wrapper\BootstrapFormBuilder;

class CambistaForm extends TPage
{
    protected $form;

    public function __construct()
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder('form_Cambista');
        $this->form->setFormTitle('Cambista');

        TTransaction::open('permission');
        $userId = TSession::getValue('userid');
        $unit = (object) SystemUser::find($userId)->get_unit();
        TTransaction::close();

        $regiaoCriteria = new TCriteria;
        $regiaoCriteria->add(new TFilter('unit_id', '=', $unit->id));

        $id                     = new TEntry('id');
        $nome                   = new TEntry('nome');
        $login                  = new TEntry('login');
        $password               = new TPassword('password');
        $repassword             = new TPassword('repassword');
        $email                  = new TEntry('email');
        $regiao_id              = new TDBUniqueSearch('regiao_id', 'permission', 'Região', 'id', 'nome', 'nome', $regiaoCriteria);
        $gerente                = new TDBUniqueSearch('gerente_id', 'permission', 'Gerente', 'id', 'nome');
        $unit_id                = new TCombo('system_unit_id');
        $frontpage_id           = new TDBUniqueSearch('frontpage_id', 'permission', 'SystemProgram', 'id', 'name', 'name');
        $phone                  = new TEntry('phone');
        $address                = new TEntry('address');
        $function_name          = new TEntry('function_name');
        $comissao               = new TEntry('comissao');
        $pode_cancelar          = new TCombo('pode_cancelar');
        $pode_cancelar_tempo    = new TEntry('pode_cancelar_tempo');
        $limite_venda           = new TEntry('limite_venda');
        $exibe_comissao         = new TCombo('exibe_comissao');
        $pode_reimprimir        = new TCombo('pode_reimprimir');
        


        $password->disableAutoComplete();
        $repassword->disableAutoComplete();

        $combo_items = [];
        $combo_items[$unit->id] = $unit->name;
        
        $unit_id->addItems($combo_items);
        $unit_id->setDefaultOption(false);
        $unit_id->setValue($unit->id);
        $unit_id->setEditable(false);

        $btn = $this->form->addAction( _t('Save'), new TAction(array($this, 'onSave')), 'far:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink( _t('Clear'), new TAction(array($this, 'onEdit')), 'fa:eraser red');
        $this->form->addActionLink( _t('Back'), new TAction(array('GerenteList','onReload')), 'far:arrow-alt-circle-left blue');

        $id->setSize('50%');
        $nome->setSize('100%');
        $login->setSize('100%');
        $password->setSize('100%');
        $repassword->setSize('100%');
        $email->setSize('100%');
        $regiao_id->setSize('100%');
        $gerente->setSize('100%');
        $unit_id->setSize('100%');
        $frontpage_id->setSize('100%');
        $phone->setSize('100%');
        $address->setSize('100%');
        $function_name->setSize('100%');
        $comissao->setSize('100%');
        $pode_cancelar->setSize('100%');
        $pode_cancelar_tempo->setSize('100%');
        $limite_venda->setSize('100%');
        $exibe_comissao->setSize('100%');
        $pode_reimprimir->setSize('100%');

        $id->setEditable(false);

        $nome->addValidation('Nome', new TRequiredValidator);
        $login->addValidation('Login', new TRequiredValidator);
        $email->addValidation('Email', new TEmailValidator);
        $regiao_id->addValidation('Região', new TRequiredValidator);
        $gerente->addValidation('Gerente', new TRequiredValidator);
        $comissao->addValidation('Comissão', new TRequiredValidator);
        $pode_cancelar->addValidation('Pode cancelar', new TRequiredValidator);
        $pode_cancelar_tempo->addValidation('Pode cancelar tempo', new TRequiredValidator);
        $limite_venda->addValidation('Limite venda', new TRequiredValidator);
        $exibe_comissao->addValidation('Exibe comissão', new TRequiredValidator);
        $pode_reimprimir->addValidation('Pode reimprimir', new TRequiredValidator);

        $this->form->addFields([new TLabel('ID')], [$id], [new TLabel('Nome')], [$nome]);
        
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', 'GerenteList'));
        $container->add($this->form);

        // add the container to the page
        parent::add($container);
        
    }
    public function onEdit()
    {
        
    }

    public function onSave()
    {
        
    }
}

