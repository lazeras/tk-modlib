<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod;

/**
 * Config
 *
 *
 *
 * @package Mod
 */
class Config extends \Tk\Config
{


    /**
     * Init the config
     *
     * @param string $sitePath
     * @param string $siteUrl
     */
    protected function init($sitePath, $siteUrl = '')
    {
        parent::init($sitePath, $siteUrl);

        $this->parseConfigFile(dirname(dirname(__FILE__)) . '/config/themes.php');
        $this->parseConfigFile(dirname(dirname(__FILE__)) . '/config/dispatch.php');
        $this->parseConfigFile(dirname(dirname(__FILE__)) . '/config/maillog.php');

    }


    /**
     * Does the current user have admin permissions
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->getUser()->hasPermission(\Tk\Auth\Auth::P_ADMIN);
    }


    /**
     * Helper function to get theme path
     *
     * @return string
     */
    public function getThemePath()
    {
        return $this->get('system.theme.path');
    }

    /**
     * Helper function to get theme url
     *
     * @return string
     */
    public function getThemeUrl()
    {
        return $this->get('system.theme.url');
    }


    /**
     * Helper function to get theme path
     *
     * @return string
     */
    public function getSelectedThemePath()
    {
        return $this->get('system.theme.selected.path');
    }

    /**
     * Helper function to get theme url
     *
     * @return string
     */
    public function getSelectedThemeUrl()
    {
        return $this->get('system.theme.selected.url');
    }


    /**
     * Use this method to create an observer object and attach it
     * to a module on its initiation.
     *
     * @param        $targetClass
     * @param string $event
     * @param        $observer
     * @return $this
     */
    public function attachModuleObserver($targetClass, $event, $observer)
    {
        $modList = array();
        if ($this->exists('__moduleObserverList')) {
            $modList = $this->get('__moduleObserverList');
        }
        $modList[$targetClass][] = array('event' => $event, 'observerClass' => $observer);
        $this->set('__moduleObserverList', $modList);
        return $this;
    }

    /**
     * See if there is any observers waiting to be attached to a module
     *
     * @param string $targetClass
     * @return array|null
     */
    public function getModuleObservers($targetClass)
    {
        if ($this->exists('__moduleObserverList') && array_key_exists($targetClass, $this['__moduleObserverList'])) {
            $modList = $this->get('__moduleObserverList');
            return $modList[$targetClass];
        }
    }

    /**
     * attachModuleObservers
     *
     * Call \Mod\Config::attachModuleObserver($targetClass, $event, $observer);
     *
     * @param string $module
     * @return \Mod\Module
     */
    private function attachModuleObservers($module)
    {

        $arr = $this->getModuleObservers(get_class($module));
        if ($arr) {
            foreach ($arr as $obsArr) {
                $event = $obsArr['event'];
                $observerClass = $obsArr['observerClass'];

                if (is_object($observerClass)) {
                    tklog('-> Attaching Module Observer Object `' . get_class($obsArr['observerClass']) . '` to `' . get_class($module) . '`');
                    $module->attach($observerClass, $event);
                } else {
                    tklog('-> Attaching Module Observer Class `' . $obsArr['observerClass'] . '` to `' . get_class($module) . '`');
                    $module->attach(new $observerClass(), $event);
                }

            }
        }
        return $module;
    }


    //-------------- FACTORY METHODS ------------------
    // List them in alphabetical order ....

    /**
     * createPage
     *
     * @param string $pageClass
     * @param array  $params
     * @return \Mod\pageClass
     * @throws \Tk\Exception
     */
    function createPage($pageClass, $params = array())
    {
        if (!$pageClass || !class_exists($pageClass)) {
            throw new \Tk\Exception('Page Class Not Found!');
        }
        $theme = $this->get('system.theme.selected');
        if (!$theme) {
            $theme = new \Mod\Theme();
            $this['system.theme.selected'] = $theme;
        }

        $page = new $pageClass($theme);

        if (isset($params['themeFile'])) {
            $page->getTheme()->setThemeFile($params['themeFile']);
        }

        // Test Url if is static page in theme folder
        $path = $this->getUri()->getPath(true);
        if (!preg_match('/\.html$/', $path)) {
            $path .= '/index.html';
        }

        if (file_exists(dirname($page->getThemePath()) . $path)) {
            $page->getTheme()->setThemeFile($path);
        }

        $this['res.page'] = $page;

        // Init DomLoader
        $this->getDomLoader();

        // Init page module
        $this->initModule($page);

        return $page;
    }


    /**
     * Create child modules from parent module
     * Using this method removes lazy loading of templates.
     * Templates a loaded on page initialisation.
     *
     * @param \Mod\Module $parent
     */
    function initModule(Module $parent)
    {
        if (!$parent->getTemplate())
            return;
        $list = $parent->getTemplate()->getCaptureList();
        $children = array();
        if (isset($list['module'])) {
            $children = $list['module'];
        }
        $this->attachModuleObservers($parent);
        $this['module.current'] = $parent;   // <---- Use this when in the observer to access the module ( $obs['module.current']; )
        $this->notify(self::toNamespace($parent->getClassName()));
        unset($this['module.current']);


        /* @var $node \DOMNode */
        foreach ($children as $node) {
            $class = self::toNamespace($node->getAttribute('class'));
            $method = 'create' . self::fromNamespace($class);
            if (method_exists($this, $method)) {
                $child = $this->$method($node);
            } else {
                $child = $this->createDefaultModule($node);
            }

            // TODO: Enable inline templates
            //            $xml = trim($node->ownerDocument->saveXML($node));
            //            $inlineXml = false;
            //            foreach ($node->childNodes as $n) {
            //                if ($n->nodeType == \XML_ELEMENT_NODE) {
            //                    $inlineXml = true;
            //                    break;
            //                }
            //            }

            $child->setInsertNode($node);
            $child->setInsertMethod(Module::INS_REPLACE);

            $parent->addChild($child);

            foreach ($node->attributes as $attr) {
                if (preg_match('/^(data|param)-(.+)/', $attr->nodeName, $regs)) {
                    $method = 'set' . ucfirst($regs[2]);
                    if (method_exists($child, $method)) {
                        $child->$method($attr->nodeValue);
                    }
                }
            }

            if ($child instanceof Module) {
                $this->initModule($child);
            }

        }
    }

    /**
     * The default component factory method.
     *
     * This method can be overridden in sub classes, or a custom factory method
     * for a specific component can be added. The custom factory method must
     * accept the same parameters as the default method and the name must
     * follow the format of the class name prefixed with the string 'create',
     * for example:
     *
     *  o createExt_Module_ImageGallery(\DOMElement $node)
     *
     * @param \DOMElement $node
     * @return \Mod\Module
     */
    function createDefaultModule(\DOMElement $node)
    {
        $class = self::toNamespace($node->getAttribute('class'));
        $child = null;
        if (class_exists($class)) {
            $child = new $class();
        } else {
            $child = new Module\Error('Module class not found: `' . $class . '`');
        }
        return $child;
    }


    // ---------------------- ACCESSORS ----------------------


    public function getCrumbs($title = 'Dashboard')
    {

        if (!$this->exists('res.crumbs') && $this->getUser()) {
            //$this->getSession()->delete('cache.crumbs.' . $title);
            $obj = $this->getSession()->get('cache.crumbs.' . $title);

            if (!$obj) {
                $obj = \Mod\Menu\Crumbs::createCrumbMenu($title, \Tk\Url::create($this->getHomePath() . '/index.html'));
                $obj->setRendererClass('\Mod\Menu\CrumbsRenderer');
                $obj->setRenderVar(\Mod\Page::VAR_PAGE_CRUMBS);
                $this->getSession()->set('cache.crumbs.' . $title, $obj);
            }
            $this->set('mod.back.url', $obj->getBackUrl());
            $this->set('res.crumbs', $obj);
        }
        return $this['res.crumbs'];

    }


    /**
     * Create an instance of Mod\DomLoader
     *
     * @return \Mod\DomLoader
     */
    public function getDomLoader()
    {
        if (!$this->exists('res.domLoader')) {
            $obj = Dom\Loader::getInstance();
            $obj->attach(new Dom\Finder\Lib());
            if ($this->exists('res.page')) {
                $obj->attach(new Dom\Finder\Theme($this->get('res.page')));
            }
            $this['res.domLoader'] = $obj;
        }
        return $this->get('res.domLoader');
    }

    /**
     * Create an instance of \Mod\DomModifier
     *
     *
     * @return \Mod\Dom\Modifier
     */
    public function getDomModifier()
    {
        if (!$this->exists('res.domModifier')) {
            $obj = new Dom\Modifier();
            $obj->add($this->getDomModifierPath());      // Url Path modifier
            $obj->add(new Dom\Filter\Browser());        // Browser show/hide conditions

            if (class_exists('Less_Parser')) {
                $obj->add(new Dom\Filter\Less());          // Add LESS CSS compiler
            } else if (class_exists('lessc')) { // @deprecated phase out `lessc` for ver 2.0
                $obj->add(new Dom\Filter\Lessc());          // Add LESS CSS compiler
            }

            //            if (class_exists('scssc')) {
            //                $obj->add(new Dom\Filter\Scssc());          // ADD SCSS CSS compiler
            //            }

            $obj->add(new Dom\Filter\JsLast());       // Move Javascript to bottom of body tag...
            if ($this->isDebug()) {
                $obj->add(new Dom\Filter\PageBytes());
            }
            $this['res.domModifier'] = $obj;
        }
        return $this->get('res.domModifier');
    }

    /**
     * Create an instance of \Mod\Dom\Filter\Path
     *
     * @return \Mod\Dom\Filter\Path
     */
    public function getDomModifierPath()
    {
        if (!$this->exists('res.domModifierPath')) {
            $obj = new Dom\Filter\Path($this->getSiteUrl(), $this->getSelectedThemeUrl());
            $this['res.domModifierPath'] = $obj;
        }
        return $this->get('res.domModifierPath');
    }


}
