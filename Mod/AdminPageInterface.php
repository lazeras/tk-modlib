<?php
/**
 * Created by PhpStorm.
 * User: mifsudm
 * Date: 2/27/14
 * Time: 12:18 PM
 */

namespace Mod;


abstract class AdminPageInterface extends \Mod\Page
{

    const MENU_CRUMBS = 'Crumbs';
    const MENU_ACTIONS = 'Actions';

    const CRUMBS_ENABLE = 'crumbs.enable';
    const CRUMBS_RESET = 'crumbs.reset';

    const PANEL_ACTIONS_ENABLE = 'panel.actions.enable';
    const PANEL_ACTIONS_LINKS = 'panel.actions.links';

    const PANEL_CONTENT_ENABLE = 'panel.content.enable';
    const PANEL_CONTENT_ICON = 'panel.content.icon';
    const PANEL_CONTENT_SPAN = 'panel.content.span';
    const PANEL_CONTENT_PADDING = 'panel.content.padding';
    const PANEL_CONTENT_TAB_LINKS = 'panel.content.tab.links';

    /**
     * @var string
     */
    protected $actionPanelClass = '';

    /**
     * @var \Mod\Menu\Menu
     */
    protected $actionPanel = null;

    /**
     * @var \Mod\Menu\Menu
     */
    protected $crumbs= null;

    /**
     * @var string
     */
    protected $contentPanelClass = '';




    /**
     * __construct
     *
     * @param \Mod\Theme $theme
     * @param string $actionPanelClass
     * @param string $contentPanelClass
     */
    public function __construct(\Mod\Theme $theme = null, $actionPanelClass = '', $contentPanelClass = '')
    {
        // Crumbs
        $this->crumbs = $this->getConfig()->getCrumbs();

        parent::__construct($theme);

        $this->setSecure(true);
        $adminRole_id = $this->getConfig()->getRbac()->Roles->returnId('admin');
        $this->setPermission($adminRole_id);

        // Setup Notice messages.
        $this->addChild(\Mod\Notice::getInstance(), self::VAR_PAGE_CONTENT_HEAD);

        $this->setContentPanelClass($contentPanelClass);
        $this->setActionPanelClass($actionPanelClass);


    }

    /**
     *
     * @param $class
     * @return $this
     */
    public function setActionPanelClass($class)
    {
        $this->actionPanelClass = $class;
        return $this;
    }

    /**
     *
     *
     * @return Menu\Menu
     */
    public function getActionPanel()
    {
        if (!$this->actionPanel && $this->actionPanelClass) {
            $this->actionPanel = \Mod\Menu\Menu::createMenu(self::MENU_ACTIONS);
            $this->actionPanel->setRendererClass($this->actionPanelClass);
            $this->actionPanel->setRenderVar(self::VAR_PAGE_CONTENT_HEAD);
        }
        return $this->actionPanel;
    }

    /**
     * @param $class
     * @return $this
     */
    public function setContentPanelClass($class)
    {
        $this->contentPanelClass = $class;
        return $this;
    }



    /**
     *
     * @param \Mod\Module $child
     */
    private function initPanels($child)
    {

        // BORDER BOX
        // Create an independent border Box
        $icon = 'fa fa-th-list';
        $span = '';
        $padding = true;
        if (!$child->exists(self::PANEL_CONTENT_ENABLE) || $child->get(self::PANEL_CONTENT_ENABLE)) {
            if ($child->exists(self::PANEL_CONTENT_ICON)) {
                $icon = $child->get(self::PANEL_CONTENT_ICON);
            }
            if ($child->exists(self::PANEL_CONTENT_PADDING)) {
                $padding = $child->get(self::PANEL_CONTENT_PADDING);
            }
            $contentPanelClass = $this->contentPanelClass;
            $contentPanel = $contentPanelClass::create($child, $span, $icon);
            if ($child->get(self::PANEL_CONTENT_TAB_LINKS)) {
                $contentPanel->addTabList($child->get(self::PANEL_CONTENT_TAB_LINKS));
            }
            $contentPanel->setPadding($padding);
            $child->addChild($contentPanel);
        }

        if (!$this->getConfig()->getUser()) {
            return;
        }

        // CRUMBS
        if (!$this->getContentChild()->exists(self::CRUMBS_ENABLE) || $this->getContentChild()->get(self::CRUMBS_ENABLE)) {
            $this->addChild($this->crumbs->getRenderer(), $this->crumbs->getRenderVar());
        }

        // ACTIONS
        if (!$child->exists(self::PANEL_ACTIONS_ENABLE) || $child->get(self::PANEL_ACTIONS_ENABLE)) {
            $links = $child->get(self::PANEL_ACTIONS_LINKS);
            if (!$links) $links = array();
            if (!count($links) || $links[0]->text != 'Back' ) {
                $back = \Mod\Menu\Item::create('Back', $this->getBackUrl(), 'fa fa-arrow-left');
                $a1 = array($back);
                $links = array_merge($a1, $links);
            }
            if (count($links)) {
                $this->getActionPanel()->addItem($links);
            }
            $this->addChild($this->getActionPanel()->getRenderer(), $this->getActionPanel()->getRenderVar());
        }
    }


    /**
     *
     */
    public function setup()
    {
        if ($this->getContentChild()) {
            $this->initPanels($this->getContentChild());
        }


        $r = parent::setup();
        if (!$this->getConfig()->getUser()) {
            return $r;
        }

        // CRUMBS
        if (!$this->getContentChild()->exists(self::CRUMBS_ENABLE) || $this->getContentChild()->get(self::CRUMBS_ENABLE)) {
            /* @var $crumbs \Mod\Menu\Crumbs */
            if ($this->getContentChild()->get(self::CRUMBS_RESET)) {
                $this->crumbs->reset();
            }
            $this->crumbs->init($this->getContentChild());
        }

        return $r;
    }


} 