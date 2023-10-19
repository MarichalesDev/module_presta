<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class MyModule extends Module
{
    public function __construct()
    {
        $this->name = 'mymodule';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Julio Marichales';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => _PS_VERSION_ ,
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('My module');
        $this->description = $this->l('Description of my module.');

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
        && Configuration::updateValue('MYMODULE_NAME', 'my module')
        && $this->registerHook('displayFooterBefore')
    ); 
  }

  public function uninstall()
{
    return (
        parent::uninstall() 
        && Configuration::deleteByName('MYMODULE_NAME')
    );
  }

  /**
 * This method handles the module's configuration page
 * @return string The page's HTML content 
 */
public function getContent()
{
    $output = '';

    // this part is executed only when the form is submitted
    if (Tools::isSubmit('submit' . $this->name)) {

        $file = $_FILES["IMG_DESKTOP"]["name"];

        $validator = 1;
        
        $file_type = strtolower(pathinfo($file,PATHINFO_EXTENSION));
        
        $url_temp = $_FILES["IMG_DESKTOP"]["tmp_name"]; 
        
        $url_insert = dirname(__FILE__) . "/img";
        
        $url_target = str_replace('\\', '/', $url_insert) . '/' . $file;
        
        if (!file_exists($url_insert)) {
            mkdir($url_insert, 0777, true);
        };
        
        $file_size = $_FILES["IMG_DESKTOP"]["size"];
        if ( $file_size > 1000000) {
          echo "El archivo es muy pesado";
          $validator = 0;
        }
        
        if($file_type != "jpg" && $file_type != "jpeg" && $file_type != "png" && $file_type != "gif" ) {
          echo "Solo se permiten imágenes tipo JPG, JPEG, PNG & GIF";
          $validator = 0;
        }
        
        if($validator == 1){
            if (move_uploaded_file($url_temp, $url_target)) {
                echo "El archivo " . htmlspecialchars(basename($file)) . " ha sido cargado con éxito.";
            } else {
                echo "Ha habido un error al cargar tu archivo.";
            }
        }else{
            echo "Error: el archivo no se ha cargado";
        }
        
        // retrieve the value set by the user
        $configValue = (string) Tools::getValue('IMG_DESKTOP');

        // check that the value is valid
        if (empty($configValue) || !Validate::isGenericName($configValue)) {
            // invalid value, show an error
            $output = $this->displayError($this->l('Invalid Configuration value'));
        } else {
            // value is ok, update it and display a confirmation message
            Configuration::updateValue('IMG_DESKTOP', $configValue);
            $output = $this->displayConfirmation($this->l('Settings updated'));
        }
    }

    // display any message, then the form
    return $output . $this->displayForm();
  } 
  /**
 * Builds the configuration form
 * @return string HTML code
 */
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
                    'label' => $this->l('Establecer imagen Desktop'),
                    'name' => 'IMG_DESKTOP',
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
    $helper->fields_value['IMG_DESKTOP'] = Tools::getValue('IMG_DESKTOP', Configuration::get('MYMODULE_CONFIG'));

    return $helper->generateForm([$form]);
  }

  public function HookdisplayFooterBefore(){
    $img = Configuration::get('IMG_DESKTOP');
    $this->smarty->assign([
        'Imagen' => $img,
      
    ]);
    return $this->display(__FILE__ ,'mymodule.tpl');
  }

}
