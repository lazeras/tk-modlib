<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Controller;

/**
 * Checks the module tree and config to see if the page should be in
 * SSL mode and redirects accordingly if required.
 *
 */
class Ssl extends \Tk\Object implements \Tk\Controller\Iface
{

    /**
     * execute
     *
     * @param \Tk\FrontController $obs
     */
    public function update($obs)
    {
        tklog($this->getClassName() . '::update()');
        $page = $this->getConfig()->get('res.page');
        if (!$this->getConfig()->get('system.enableSsl') || !$page instanceof \Mod\Page) {
            return;
        }
        // Ensure SSL security
        if ($this->getConfig()->get('res.page') ) { // <-- Fixed this line...
            $this->secureRedirect($page->isSecure(), $this->getUri());
        }
    }

    /**
     * Check the page and redirect to secure/un-secure as required
     *
     * @param bool $isSecure Is this a secure page
     * @param \Tk\Url $requestUri
     */
    public function secureRedirect($isSecure, $requestUri)
    {
        if ($requestUri->getScheme() == 'http' && $isSecure) {
            $requestUri->setScheme('https');
            $requestUri->redirect();
        }
    }

}
