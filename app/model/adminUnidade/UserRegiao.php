<?php
use Adianti\Database\TRecord;

class UserRegiao extends TRecord
{
    const TABLENAME     = 'user_regiao';
    const PRIMARYKEY    = 'id';
    const IDPOLICY      = 'serial';

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('user_id');
        parent::addAttribute('regiao_id');
    }
}