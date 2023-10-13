<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
class MyModule extends Module
{
    public function __construct()
    {
        $this->name = 'mymodule';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Julio Marichales';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.6.0',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Modulo de prueba');
        $this->description = $this->l('Imagen en Prefooter');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('MYMODULE_NAME')) {
            $this->warning = $this->l('No name provided');
        }
    }
    public function install()
{
    if (Shop::isFeatureActive()) {
        Shop::setContext(Shop::CONTEXT_ALL);
    }

   return (
        parent::install() 
        && $this->registerHook('displayFooterBefore')
        && Configuration::updateValue('MYMODULE_NAME', 'my friend')
    ); 
  }
  public function uninstall()
{
    return (
        parent::uninstall() 
        && Configuration::deleteByName('MYMODULE_NAME')
    );
  }

 public function getContent(){

  }
  
  public function getForm(){


  }

  public function HookDisplayFooterBefore()
  {
  return $this->display(__FILE__, 'mymodule.tpl');
  }

}

