<?php
/* SVN FILE: $Id$ */

/**
 * Dispatcher takes the URL information, parses it for paramters and 
 * tells the involved controllers what to do.
 * 
 * This is the heart of Cake's operation. 
 *
 * PHP versions 4 and 5
 *
 * CakePHP :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright (c) 2005, Cake Software Foundation, Inc. 
 *                     1785 E. Sahara Avenue, Suite 490-204
 *                     Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource 
 * @copyright    Copyright (c) 2005, Cake Software Foundation, Inc.
 * @link         http://www.cakefoundation.org/projects/info/cakephp CakePHP Project
 * @package      cake
 * @subpackage   cake.cake
 * @since        CakePHP v 0.2.9
 * @version      $Revision$
 * @modifiedby   $LastChangedBy$
 * @lastmodified $Date$
 * @license      http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * List of helpers to include
 */
uses('error_messages', 'object', 'router', DS.'controller'.DS.'controller', DS.'controller'.DS.'scaffold');

/**
 * Dispatcher translates URLs to controller-action-paramter triads.
 *
 * Dispatches the request, creating appropriate models and controllers.
 *
 * @package    cake
 * @subpackage   cake.cake
 * @since      CakePHP v 0.2.9
 */
class Dispatcher extends Object
{
/**
 * Base URL
 * @var string
 */
   var $base = false;

/**
 * Base URL
 * @var string
 */
   var $admin = false;

/**
 * Base URL
 * @var string
 */
   var $webservices = null;

/**
 * Constructor.
 */
   function __construct()
   {
      parent::__construct();
   }

/**
 * Dispatches and invokes given URL, handing over control to the involved controllers, and then renders the results (if autoRender is set).
 *
 * If no controller of given name can be found, invoke() shows error messages in
 * the form of Missing Controllers information. It does the same with Actions (methods of Controllers are called
 * Actions).
 *
 * @param string $url	URL information to work on.
 * @return boolean		Success
 */
   function dispatch($url, $additionalParams=array())
   {
      $params = array_merge($this->parseParams($url), $additionalParams);
      $missingController = false;
      $missingAction     = false;
      $missingView       = false;
      $privateAction     = false;

      if(defined('CAKE_ADMIN'))
      {
          if(isset($params[CAKE_ADMIN]))
          {
              $this->admin = '/'.CAKE_ADMIN ;
              $url = preg_replace('/'.CAKE_ADMIN.'\//', '', $url);
              if (empty($params['action']))
              {
                  $params['action'] = CAKE_ADMIN.'_'.'index';
              }
              else
              {
                  $params['action'] = CAKE_ADMIN.'_'.$params['action'];
              }
          }
      }

      $this->base = $this->baseUrl();

      if(!in_array('render', array_keys($params)))
      {
          $params['render'] = 0;
      }

      if (empty($params['controller']))
      {
         $missingController = true;
      }
      else
      {
         $ctrlName  = Inflector::camelize($params['controller']);
         $ctrlClass = $ctrlName.'Controller';

         if (!loadController($params['controller']) || !class_exists($ctrlClass))
         {
             if(preg_match('/([\\.]+)/',$ctrlName))
             {
                 $this->error404(strtolower($ctrlName),'Was not found on this server');
                 exit();
             }
             else
             {
                 $missingController = true;
             }
         }
      }

      if ($missingController)
      {
         $controller       =& new Controller();
         $params['action'] = 'missingController';
         if (empty($params['controller']))
         {
             $params['controller'] = "Controller";
         }
         else
         {
             $params['controller'] = Inflector::camelize($params['controller']."Controller");
         }
         $controller->missingController = $params['controller'];
      }
      else
      {
         $controller =& new $ctrlClass($this);
      }

      $classMethods = get_class_methods($controller);
      $classVars = get_object_vars($controller);

      if (empty($params['action']))
      {
          $params['action'] = 'index';
      }

      if(in_array($params['action'], $classMethods) && strpos($params['action'], '_', 0) === 0)
      {
          $privateAction = true;
      }

      if(!in_array($params['action'], $classMethods))
      {
          $missingAction = true;
      }

      $controller->base        = $this->base;
      $controller->here        = $this->base.'/'.$url;
      $controller->webroot     = $this->webroot;
      $controller->params      = $params;
      $controller->action      = $params['action'];
      $controller->data        = empty($params['data'])? null: $params['data'];
      $controller->passed_args = empty($params['pass'])? null: $params['pass'];
      $controller->autoLayout = !$params['bare'];
      $controller->autoRender = !$params['render'];
      $controller->webservices = $params['webservices'];

      if(!is_null($controller->webservices))
      {
          array_push($controller->components, $controller->webservices);
          array_push($controller->helpers, $controller->webservices);
      }

      if((in_array('scaffold', array_keys($classVars))) && ($missingAction === true))
      {
          $scaffolding = new Scaffold($controller, $params);
          exit;
      }

      $controller->constructClasses();

      if ($missingAction)
      {
          $controller->missingAction = $params['action'];
          $params['action'] = 'missingAction';
      }

      if ($privateAction)
      {
          $controller->privateAction = $params['action'];
          $params['action'] = 'privateAction';
      }

      return $this->_invoke($controller, $params );
   }

/**
 * Invokes given controller's render action if autoRender option is set. Otherwise the contents of the operation are returned as a string.
 *
 * @param object $controller
 * @param array $params
 * @return string
 */
   function _invoke (&$controller, $params )
   {
       $output = call_user_func_array(array(&$controller, $params['action']), empty($params['pass'])? null: $params['pass']);
       if ($controller->autoRender)
       {
           $controller->render();
           exit;
       }
       return $output;
   }

/**
 * Returns array of GET and POST parameters. GET parameters are taken from given URL.
 *
 * @param string $from_url	URL to mine for parameter information.
 * @return array Parameters found in POST and GET.
 */
   function parseParams($from_url)
   {
       // load routes config
       $Route = new Router();
       include CONFIGS.'routes.php';
       $params = $Route->parse ($from_url);

       // add submitted form data
       $params['form'] = $_POST;
       if (isset($_POST['data']))
       {
           $params['data'] = (ini_get('magic_quotes_gpc') == 1)?
           $this->stripslashes_deep($_POST['data']) : $_POST['data'];
       }
       if (isset($_GET))
       {
           $params['url'] = $this->urldecode_deep($_GET);
           $params['url'] = (ini_get('magic_quotes_gpc') == 1)?
           $this->stripslashes_deep($params['url']) : $params['url'];
       }

       foreach ($_FILES as $name => $data)
       {
           $params['form'][$name] = $data;
       }
       $params['bare'] = empty($params['ajax'])? (empty($params['bare'])? 0: 1): 1;

       $params['webservices'] = empty($params['webservices']) ? null : $params['webservices'];

       return $params;
   }

/**
 * Recursively strips slashes from given array.
 *
 */
   function stripslashes_deep($val)
   {
      return (is_array($val)) ?
        array_map(array('Dispatcher','stripslashes_deep'), $val) : stripslashes($val);
   }

/**
 * Recursively performs urldecode on given array.
 *
 */
   function urldecode_deep($val)
   {
      return (is_array($val)) ?
        array_map(array('Dispatcher','urldecode_deep'), $val) : urldecode($val);
   }

/**
 * Returns a base URL.
 *
 * @return string	Base URL
 */
    function baseUrl()
    {
       $htaccess = null;
       $base = $this->admin;
       $this->webroot = '';
       if (defined('BASE_URL'))
       {
           $base = BASE_URL.$this->admin;
       }

       $docRoot = $_SERVER['DOCUMENT_ROOT'];
       $scriptName = $_SERVER['PHP_SELF'];

      // If document root ends with 'webroot', it's probably correctly set
      $r = null;
      if (preg_match('/'.APP_DIR.'\\'.DS.WEBROOT_DIR.'/', $docRoot))
      {
          $this->webroot = '/';
          if (preg_match('/^(.*)\/index\.php$/', $scriptName, $r))
          {
              if(!empty($r[1]))
              {
                  return  $base.$r[1];
              }
          }
      }
      else
      {
          if (defined('BASE_URL'))
          {
              $webroot =setUri();
              $htaccess =  preg_replace('/(?:'.APP_DIR.'(.*)|index\\.php(.*))/i', '', $webroot).APP_DIR.'/'.WEBROOT_DIR.'/';
          }
          if(APP_DIR === 'app')
          {
              if (preg_match('/^(.*)\\/'.APP_DIR.'\\/'.WEBROOT_DIR.'\\/index\\.php$/', $scriptName, $regs))
              {
                  !empty($htaccess)? $this->webroot = $htaccess : $this->webroot = $regs[1].'/';
                  return  $regs[1];
              }
              else
              {
                  !empty($htaccess)? $this->webroot = $htaccess : $this->webroot = '/';
                  return $base;
              }
          }
          else
          {
              if (preg_match('/^(.*)\\/'.WEBROOT_DIR.'\\/index\\.php$/', $scriptName, $regs))
              {
                  !empty($htaccess)? $this->webroot = $htaccess : $this->webroot = $regs[1].'/';
                  return  $regs[1];
              }
              else
              {
                  !empty($htaccess)? $this->webroot = $htaccess : $this->webroot = '/';
                  return $base;
              }
          }
      }
      return $base;
   }

/**
 * Displays an error page (e.g. 404 Not found).
 *
 * @param int $code 	Error code (e.g. 404)
 * @param string $name 	Name of the error message (e.g. Not found)
 * @param string $message
 * @return unknown
 */
   function error ($code, $name, $message)
	{
        $controller =& new Controller ($this);
        $controller->base = $this->base;
        $controller->autoLayout = true;
        $controller->set(array('code'=>$code, 'name'=>$name, 'message'=>$message));
		$controller->pageTitle = $code.' '. $name;
        return $controller->render('errors/error404');
	}


/**
 * Convenience method to display a 404 page.
 *
 * @param string $url 		URL that spawned this message, to be included in the output.
 * @param string $message 	Message text for the 404 page.
 */
   function error404 ($url, $message)
   {
      $this->error('404', 'Not found', sprintf(ERROR_404, $url, $message));
   }
}
?>