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

    public function getContent()
   {
        $output = null;
            if(Tools::isSubmit('submit')) {
            $output .= $this->displayConfirmation('Se actualizo la Configuracion del Modulo');
    }
    return $output.$this->displayForm();
  }
    
  public function displayForm()
  {
      // Init Fields form array
      $form = [
          'form' => [
              'legend' => [
                  'title' => $this->l('Settings'),
              ],
              'input' => [
                  [
                      'type' => 'file',
                      'label' => $this->l('Establecer Imagen Desktop'),
                      'name' => 'MYMODULE_CONFIG',
                      'size' => 20,
                      'required' => true,
                  ],
                  [
                    'type' => 'file',
                    'label' => $this->l('Establecer Imagen Movil '),
                    'name' => 'MYMODULE_CONFIG',
                    'size' => 20,
                    'required' => true,
                  ],
              ],
              'submit' => [
                  'title' => $this->l('Save'),
                  'class' => 'btn btn-default pull-right',
              ],
          ],
      ];
  
      $helper = new HelperForm();
  
      // Module, token and currentIndex
      $helper->table = $this->table;
      $helper->name_controller = $this->name;
      $helper->token = Tools::getAdminTokenLite('AdminModules');
      $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
      $helper->submit_action = 'submit' . $this->name;
  
      // Default language
      $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
  
      // Load current value into the form
      $helper->fields_value['MYMODULE_CONFIG'] = Tools::getValue('MYMODULE_CONFIG', Configuration::get('MYMODULE_CONFIG'));
  
      return $helper->generateForm([$form]);
  }

  public function HookDisplayFooterBefore()
  {
  return $this->display(__FILE__, 'mymodule.tpl');
  }

}

