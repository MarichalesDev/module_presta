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

        $response = $this->uploadImage("IMG_DESKTOP", "desktop");
        if( $response["error"] ) {
            return $output.$this->displayError($this->l( $response["message"] )).$this->displayForm();
        }
        $response = $this->uploadImage("IMG_MOBILE", "mobile");
        if( $response["error"] ) {
            return $output.$this->displayError($this->l( $response["message"] )).$this->displayForm();
        }
        return $output.$this->displayConfirmation($this->l('Settings updated')).$this->displayForm();;
    }

    // display any message, then the form
    return $output . $this->displayForm();
  }
  /**
 * Builds the configuration form
 * @return string HTML code
 */

private function uploadImage( $name, $filename ) {
    $file = $_FILES[ $name ]["name"];
    $file_type = strtolower(pathinfo($file,PATHINFO_EXTENSION));
    $url_temp = $_FILES[ $name ]["tmp_name"];
    $url_insert = dirname(__FILE__) . "/img";
    $url_target = str_replace('\\', '/', $url_insert) . '/'.$filename.'.' . $file_type;
    if (!file_exists($url_insert)) {
        mkdir($url_insert, 0777, true);
    };
    $file_size = $_FILES[ $name ]["size"];
    if ( $file_size > 1000000) {
        return [
            "error" => true,
            "message" => "El archivo es muy pesado"
        ];
    }
    if($file_type != "jpg" && $file_type != "jpeg" && $file_type != "png" && $file_type != "gif" ) {
        return [
            "error" => true,
            "message" => "Solo se permiten imágenes tipo JPG, JPEG, PNG & GIF"
        ];
    }
    if (!move_uploaded_file($url_temp, $url_target)) {
        return [
            "error" => true,
            "message" => "Ha habido un error al cargar tu archivo."
        ];
    }
    Configuration::updateValue( $name, "/modules/mymodule/img/".$filename.".".$file_type );
    return [
        "error" => false,
        "message" => 'Settings updated'
    ];
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
                    'label' => $this->l('Establecer imagen Desktop'),
                    'name' => 'IMG_DESKTOP',
                    //'value' => Configuration::get('IMG_DESKTOP'),
                    'size' => 20,
                    'required' => true,
                ],
                [
                    'type' => 'file',
                    'label' => $this->l('Establecer imagen Mobile'),
                    'name' => 'IMG_MOBILE',
                    //'value' => Configuration::get('IMG_MOBILE'),
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
    $helper->fields_value['IMG_DESKTOP'] = Tools::getValue('IMG_DESKTOP', Configuration::get('IMG_DESKTOP'));

    return $helper->generateForm([$form]);
  }

  public function HookdisplayFooterBefore(){
    $desktop = Configuration::get('IMG_DESKTOP');
    $mobile = Configuration::get('IMG_MOBILE');
    $this->context->smarty->assign([
        'desktop' => $desktop,
        'mobile' => $mobile,
    ]);
    return $this->display(__FILE__ ,'templates/hook/views/mymodule.tpl');
  }

}
