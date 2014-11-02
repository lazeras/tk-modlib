<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod;

/**
 * Module are the controller and view for the model or data
 * It is done this way for code effecientcy and the ability
 * to create a Module execution tree
 *
 *
 */
abstract class Module extends Renderer
{

    const INS_PREPEND = 'prepend';
    const INS_REPLACE = 'replace';
    const INS_APPEND = 'append';





    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var \Dom\Template
     */
    protected $template = null;

    /**
     * @var \DOMElement
     */
    protected $insertNode = null;

    /**
     * @var \Mod\Module
     */
    protected $page = null;

    /**
     * @var \Mod\Module
     */
    protected $parent = null;

    /**
     * @var \Mod\Renderer[]|\Mod\Module[]
     */
    protected $children = array();

    /**
     * @var array
     */
    private $events = array();
    
    /**
     * @var bool
     */
    private $secure = false;

    /**
     * Seconds to cache the module for.
     * @var int
     */
    protected $cacheTime = 0;


    const CRUMB_RESET = false;

    /**
     * @var string
     */
    protected $permission = \Tk\Auth\Auth::P_PUBLIC;
    
    /**
     * If false the ini/execute/render methods will not run
     * @var bool
     */
    private $enabled = true;

    /**
     * If false only the show method will be disabled
     * @var bool
     */
    private $showEnabled = true;

    /**
     * @var \DOMElement
     */
    protected $insertMethod = self::INS_APPEND;

    
    /**
     * Any Misc vars can be stored here
     * TODO: Use this for none common parameters and module node params attrs....
     *      We could use it for all the Box/Action/crumb settings, etc....
     *      Some of the above var could also be moved here, to lighten up the module object.
     * 
     * @var array
     */
    protected $paramList = array();


    
    

    /**
     * Abstract init() method
     * (optional)
     */
    function init() { }

    /**
     * Abstract doDefault() method
     * (optional)
     */
    function doDefault() { }

    /**
     * Show
     */
    public function show() { }



    /**
     * Iterate through the page's children
     * Init
     */
    public function setup()
    {
        if (!$this->enabled()) {
            return false;
        }

        // Disable Cache in debug mode for Modules only
        if ($this->getConfig()->isDebug()) {
            $this->cacheTime = 0;
        }

        $this->notify('preSetup');
        $this->notify('preInit');
        $this->init();

        foreach ($this->children as $renderList) {
            if (!is_array($renderList)) { continue; }
            foreach ($renderList as $ren) {
                if (method_exists($ren, 'setup')) {
                    $ren->setup();
                }
            }
        }
        $this->notify('postInit');
        $this->notify('postSetup');
    }

    /**
     * Execute the module events and its children
     * doDefault/{event}
     */
    public function execute()
    {

        if (!$this->enabled()) {
            return false;
        }
        $this->notify('preExecute');

        // Execute the First event found only
        $eventExecuted = false;
        foreach ($this->events as $event => $method) {
            if ($this->getRequest()->exists($event)) {
                $this->executeEvent($method);
                $eventExecuted = true;
                break;
            }
        }

        $cache = $this->getConfig()->getCache();
        if ( $this->cacheTime <= 0 || !$cache->fetch($this->getCacheHash()) ) {
            if (!$eventExecuted) {
                $this->executeEvent('doDefault');
            }
            // execute children
            foreach ($this->children as $renderList) {
                if (!is_array($renderList)) continue;
                foreach ($renderList as $ren) {
                    if (method_exists($ren, 'execute')) {
                        $ren->execute();
                    }
                }
            }
        }
        $this->notify('postExecute');
    }

    /**
     * Iterate through the page's children and
     * render their template and append them to their canvas areas
     * Show
     *
     * @return string
     */
    public function render()
    {
        if (!$this->enabled() || !$this->showEnabled()) {
            return false;
        }
        $this->notify('preRender');
        $this->notify('preShow');
        $cache = $this->getConfig()->getCache();
        if ($this->cacheTime <= 0 || !$cache->fetch($this->getCacheHash())) {

            // Show after the children have been rendered so we have access
            // to any newly rendered nodes...
            $this->show();

            foreach ($this->children as $canvasName => $renderList) {
                if (!is_array($renderList)) continue;
                foreach ($renderList as $ren) {
                    if (method_exists($ren, 'render')) {
                        $ren->render();
                    } else if (method_exists($ren, 'show')) {   // For Dom_Renderer's
                        $ren->show();
                    }
                    // Insert template into parent
                    if ($ren->getTemplate()) {
                        $var = null;
                        if ( method_exists($ren, 'getInsertNode') && $ren->getInsertNode() ) {
                            $var = $ren->getInsertNode();
                        } else if ( $this->getTemplate()->keyExists('var', $canvasName) ) {
                            $var = $canvasName;
                        }
                        if ($var) {
                            $ins = self::INS_APPEND;
                            if (method_exists($ren, 'getInsertMethod')) {
                                $ins = $ren->getInsertMethod();
                            }
                            switch ($ins) {
                                case self::INS_PREPEND:
                                    $this->getTemplate()->prependTemplate($var, $ren->getTemplate());
                                    break;
                                case self::INS_REPLACE:
                                    $this->getTemplate()->replaceTemplate($var, $ren->getTemplate());
                                    break;
                                default:
                                    $this->getTemplate()->appendTemplate($var, $ren->getTemplate());
                                    break;
                            }
                        }
                    } // End Insert Template
                }
            }

            // Save to cache if enabled
            if ($this->getCacheTimeout()) {
                $cache->store($this->getCacheHash(), $this->getTemplate(), $this->getCacheTimeout());
            }
        } else {
            $this->setTemplate($cache->fetch($this->getCacheHash()));
        }

        $this->notify('postShow');
        $this->notify('postRender');
    }




    /**
     * addChild
     * If no template is found in the child renderer this getTemplate() is used
     * that means renderers can be added with a null template and expect its
     * parent template to be returned when calling getTemplate()...
     *
     * @param \Mod\Renderer $child
     * @param string $canvasName
     * @param int $idx The array index to insert the child into
     * @return \Mod\Renderer
     */
    public function addChild($child, $canvasName = '', $idx = null)
    {
        if (!$child instanceof Renderer) {
            return $child;
        }

        if (!$canvasName) {
            $canvasName = \Tk\Object::fromNamespace(get_class($child));
        }
        if (!isset($this->children[$canvasName])) {
            $this->children[$canvasName] = array();
        }
        if ($child instanceof Module) {
            $child->setPage($this->getPage());
            $child->setParent($this);
            //if (!$child->isEmbeded() && $child->hasTemplate()) {
                $this->getConfig()->initModule($child);
            //}
        }

        if (!$child->hasTemplate()) {
            // use the parent template
            $child->setTemplate($this->getTemplate());
        }
        if ($idx !== null && $idx >= 0 && $idx < count($this->children[$canvasName])) {
            array_splice($this->children[$canvasName], $idx, 0, array($child));
        } else {
            $this->children[$canvasName][] = $child;
        }
        return $child;
    }

    /**
     * get a Child List array for the requested canvas area
     *
     * @param string $canvasName
     * @return array
     */
    public function getChildren($canvasName = '')
    {
        if (!$canvasName) {
            return $this->children;
        }
        if (isset($this->children[$canvasName])) {
            return $this->children[$canvasName];
        }
    }

    /**
     * Execute this objects event and all sub object events.
     *
     * @param string $method
     * @return mixed
     */
    private function executeEvent($method)
    {
        if (method_exists($this, $method)) {
            return $this->$method();
        }
    }

    /**
     * This function will iterate the module tree looking
     * for a module with the requested class name.
     * The skip value will skip the n number of classes
     *
     * So if $skip = 1 the first instance of the class name it finds
     * will be skipped and the second will be returned if found otherwise
     * null will be returned.
     *
     *
     * @param class $className
     * @param int $skip (optional) Default 0
     * @return \Mod\Module
     */
    public function findModule($className, $skip = 0)
    {
        $arr = $this->iterateChildren($this, $className);
        if (count($arr)) {
            if ($skip && count($arr) >= $skip) {
                return $arr[$skip-1];
            }
            if (!$skip) return $arr[0];
        }
    }

    /**
     * Used by findModule()
     * Returns an array of module instances of the supplied classname
     * in the render tree
     *
     * @param \Mod\Module $parent
     * @param string $className
     * @param array $found Not required on calling
     * @return array
     */
    public function iterateChildren($parent, $className, $found = array())
    {
        foreach ($parent->getChildren() as $caa) {
            foreach ($caa as $c) {
                if (get_class($c) == $className) {
                    $found[] = $c;
                }
                if ($c instanceof Module) {
                    $found = $this->iterateChildren($c, $className, $found);
                }
            }
        }
        return $found;
    }

    /**
     * Adds an event.
     *get
     * Where $event is a parameter for the request. Events trigger the
     * call to $method. For example, if $event = 'submit' and the $method = 'doSubmit' the
     * doSubmit() method will be called if submit is found in the request.
     *
     * When executing returns at the first event found in the request. If
     * there are no events or no events found in the request the the
     * doDefault() method is called.
     *
     * @param string $event The request parameter key/name
     * @param string $method The method to execute
     * @return \Mod\Module
     */
    public function addEvent($event, $method)
    {
        $this->events[$event] = $method;
        return $this;
    }

    /**
     * Return true if this page is SSL enabled.
     *
     * @return bool
     */
    public function isSecure()
    {
        if ($this->secure) {
            return true;
        }
        foreach ($this->children as $canvasName => $arr) {
            foreach ($arr as $child) {
                if ($child instanceof Module && $child->isSecure()) {
                    return true;
                }
            }
        }
        return false;
    }



    /**
     * Set insertNode
     *
     * @param \DOMElement $node
     * @return \Mod\Module
     */
    public function setInsertNode($node)
    {
        $this->insertNode = $node;
        return $this;
    }

    /**
     * Get insertNode
     *
     * @return \DOMElement
     */
    public function getInsertNode()
    {
        return $this->insertNode;
    }

    /**
     * Set the cache time for this module in seconds
     *
     * @param int $sec
     * @return \Mod\Module
     */
    public function setCacheTimeout($sec)
    {
        $this->cacheTime = $sec;
        return $this;
    }

    /**
     * Get the cache timeout in seconds.
     *  0 = do not cache
     * >0 = Seconds till cache timeout
     *
     * @return int
     */
    public function getCacheTimeout()
    {
        return $this->cacheTime;
    }

    /**
     * This method is setup to accept a pageTitle even if the
     * page has not be assigned yet.
     * Because at construction the page is not directly assigned we
     * need to implement this.
     *
     * @param string $title
     * @return \Mod\Module
     */
    public function setPageTitle($title)
    {
        if ($this->getPage()) {
            $this->getPage()->setTitle($title);
        }
        return $this;
    }

    /**
     * If a page has the <title> tag and optionally
     * the var named "__pageTitle"
     * They will be set with this value.
     *
     * @param string $title
     * @return \Mod\Module
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get the title of this widget
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Create a unique cache hash for this template and its children..
     *
     * @return string
     */
    protected function getCacheHash()
    {
        return md5($this->getClassName() . $this->getInstanceId() . $this->getUri()->toString());
    }


    /**
     * Create a unique event key name for this object
     *
     * @param string $key
     * @return string
     */
    public function getObjectKey($key)
    {
        return self::makeObjectKey($key, $this->getInstanceId());
    }
    
    
    /**
     * Set the SSL status of the page.
     * NOTE: It only takes one component to be secure
     * then the page will be redirected to the https://... url if not so already
     *
     * NOTE: For this to work you must have the SSL certificate installed
     * to the same directory as main website.
     *
     * @param bool $b
     * @return \Mod\Module
     */
    public function setSecure($b)
    {
        $this->secure = $b;
        return $this;
    }

    /**
     * Set
     *
     * @param \Mod\Module $page
     * @return \Mod\Module
     */
    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * Get the page module node
     *
     * @return \Mod\Page
     */
    public function getPage()
    {
        if (!$this->page) {
            $this->page = $this->getConfig()->get('res.page');
        }
        return $this->page;
    }

    /**
     * Is this the module that main page object.
     *
     * @return bool
     */
    public function isPage()
    {
        return ($this->getPage() == $this);
    }

    /**
     * Set
     *
     * @param \Mod\Module $parent
     * @return \Mod\Module
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get
     *
     * @return \Mod\Module
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Test to se if this module is this
     *  page`s content module.
     *
     * @return boolean
     */
    public function isContentModule()
    {
        return (bool)($this == $this->getConfig()->get('res.pageContentModule'));
    }



    /**
     * Create a default template
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        return Dom\Loader::loadFile('', $this->getClassName());
    }

    
    
    
    
    
    
    /**
     * Get a module param
     * 
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        if (isset($this->paramList[$name])) {
            return $this->paramList[$name];
        }
    }
    
    /**
     * Set a module param
     * 
     * @param string $name
     * @param mixed $value
     * @return \Mod\Module
     */
    public function set($name, $value)
    {
        $this->paramList[$name] = $value;
        return $this;
    }

    /**
     * Add a value to a moduile param,
     * This assumes the named param is an array
     * if not exists an array will be created if
     * a non array value exists an exception will be thrown
     *
     * @param string $name
     * @param mixed $value
     * @throws Exception
     * @return \Mod\Module
     */
    public function add($name, $value)
    {
        if ($this->exists($name)) {
            if (!is_array($this->paramList[$name])) {
                throw new Exception('Trying to add a parameter to a non-array value.');
            }
        } else {
            $this->paramList[$name] = array();
        }
        $this->paramList[$name][] = $value;
        return $this;
    }
    
    /**
     * Test if a param exists
     * 
     * @param string $name
     * @return bool
     */
    public function exists($name)
    {
        return isset($this->paramList[$name]);
    }
    

    
    
    
    
    
    
    // TODO Use the paramsList for these methods/vars

    /**
     * Set the module permission value if any
     *
     * @param $permission
     * @return $this
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;
        return $this;
    }

    /**
     * Get the module Permission value if any.
     *
     * First check if the content module has permission value
     * If not check Page module for permission
     * If not check this instance for any permissions.
     * 
     * TODO: Maybe we can iterate through all module looking for a permission 
     *       heirachy, return the highest permission.
     *
     * @return string
     */
    public function getPermission()
    {
        // First check content
        if ($this->getPage() && $this->getPage()->getContentChild() && $this->getPage()->getContentChild()->permission) {
            return $this->getPage()->getContentChild()->permission;
        }
        // Next Check Page
        if ($this->getPage() && $this->getPage()->permission) {
            return $this->getPage()->permission;
        }
        return $this->permission;
    }
    


    /**
     * If enabled is set to false then the widget does not execute/render
     * Call with no parameters to get current state
     *
     * @param bool $b
     * @return bool
     */
    public function enabled($b = null)
    {
        if ($b === true || $b === false) {
            $this->enabled = $b;
        }
        return $this->enabled;
    }
    

    /**
     * Enable/dissable the show method
     * Call with no parameters to get current state
     *
     * @param bool $b
     * @return bool
     */
    public function showEnabled($b = null)
    {
        if ($b === true || $b === false) {
            $this->showEnabled = $b;
        }
        return $this->showEnabled;
    }
    

    /**
     * Set the insert method for this module
     * See Constants:
     *  o INS_PREPEND
     *  o INS_REPLACE
     *  o INS_APPEND
     *
     * @param string $insMethod
     * @return \Mod\Module
     */
    public function setInsertMethod($insMethod)
    {
        $this->insertMethod = $insMethod;
        return $this;
    }

    /**
     * Get insertMethod
     *
     * @return string
     */
    public function getInsertMethod()
    {
        return $this->insertMethod;
    }
    
    

    /**
     * getActions
     *
     * This method is used for content modules that
     * wish to add menu items to an action/icon pane
     * For example a top icon pane with actions to related
     * functions from the current page.
     * Back, Edit, AddGoups, AddCategories etc....
     *
     * Most non content widgets will not use this unless
     * you have a custom page setup to call it.
     *
     * @return array
     */
    public function getActions()
    {
        return array();
    }

    /**
     * Get the back url from the config
     *
     * @return \Tk\Url
     */
    public function getBackUrl()
    {
        return $this->getConfig()->getBackUrl();
    }
    
    
    

}
