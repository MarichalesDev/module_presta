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
        && $this->installDB()
    ); 
  }
  public function installDB(){
    Db::getInstance()->Execute(
        'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'item` (
        `id_item` int(10) NOT NULL AUTO_INCREMENT ,
        `img_path` varchar(128) NOT NULL,
        PRIMARY KEY (`id_item`)
        ) ENGINE = InnoDB DEFAULT CHARSET=utf8;'
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
    $output = '';

    // this part is executed only when the form is submitted
    if (Tools::isSubmit('submit' . $this->name)) {
     
        // retrieve the value set by the user
        $configValue = (string) Tools::getValue('MYMODULE_CONFIG');

        // check that the value is valid
        if (empty($configValue) || !Validate::isGenericName($configValue)) {
            // invalid value, show an error
            $output = $this->displayError($this->l('Invalid Configuration value'));
        } else {
            // value is ok, update it and display a confirmation message
            Configuration::updateValue('MYMODULE_CONFIG', $configValue);
            $output = $this->displayConfirmation($this->l('Settings updated'));
        
        }
          //Si se quiere subir una imagen
if (isset($_POST['submit'])) {
    //Recogemos el archivo enviado por el formulario
    $archivo = $_FILES['MYMODULE_CONFIG']['name'];
    //Si el archivo contiene algo y es diferente de vacio
    if (isset($archivo) && $archivo != "") {
       //Obtenemos algunos datos necesarios sobre el archivo
       $tipo = $_FILES['MYMODULE_CONFIG']['type'];
       $tamano = $_FILES['MYMODULE_CONFIG']['size'];
       $temp = $_FILES['MYMODULE_CONFIG']['tmp_name'];
       //Se comprueba si el archivo a cargar es correcto observando su extensión y tamaño
      if (!((strpos($tipo, "gif") || strpos($tipo, "jpeg") || strpos($tipo, "jpg") || strpos($tipo, "png")) && ($tamano < 2000000))) {
         echo '<div><b>Error. La extensión o el tamaño de los archivos no es correcta.<br/>
         - Se permiten archivos .gif, .jpg, .png. y de 200 kb como máximo.</b></div>';
      }
      else {
         //Si la imagen es correcta en tamaño y tipo
         //Se intenta subir al servidor
         if (move_uploaded_file($temp, 'images/'.$archivo)) {
             //Cambiamos los permisos del archivo a 777 para poder modificarlo posteriormente
             chmod('img/'.$archivo, 0777);
             //Mostramos el mensaje de que se ha subido co éxito
             echo '<div><b>Se ha subido correctamente la imagen.</b></div>';
             //Mostramos la imagen subida
             echo '<p><img src="images/'.$archivo.'"></p>';
         }
         else {
            //Si no se ha podido subir la imagen, mostramos un mensaje de error
            echo '<div><b>Ocurrió algún error al subir el fichero. No pudo guardarse.</b></div>';
         }
       }
       Db::getInstance()->Execute(
        "INSERT INTO '._DB_PREFIX_.'item (id_item, img_path) VALUES ('null', '$archivo')"
           );
    }
 }
    }

    // display any message, then the form
    return $output . $this->displayForm();
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
              ],
              'submit' => [
                  'title' => $this->l('Save'),
                  'class' => 'btn btn-default pull-right',
                  'name' => 'submit',
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

