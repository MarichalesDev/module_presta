<?php
/**
* 2007-2023 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2023 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Prefooter_photo extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'prefooter_photo';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Julio Marichales';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('prefooter_photo');
        $this->description = $this->l('descriptyon of my module');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('PREFOOTER_PHOTO_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('displayHome');
    }

    public function uninstall()
    {
        Configuration::deleteByName('PREFOOTER_PHOTO_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
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
        Configuration::updateValue( $name,$filename.".".$file_type );
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
    
      public function HookdisplayHome(){
        $desktop = Configuration::get('IMG_DESKTOP');
        $mobile = Configuration::get('IMG_MOBILE');
        $this->context->smarty->assign([
            'desktop' => $this->context->link->protocol_content . Tools::getMediaServer($desktop) . $this->_path . 'img/' . $desktop,
            'mobile' => $this->context->link->protocol_content . Tools::getMediaServer($mobile) . $this->_path . 'img/' . $mobile,
        ]);
        return $this->display(__FILE__ ,'templates/hook/views/pf_photo.tpl');
      }
}
