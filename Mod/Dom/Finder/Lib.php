<?php
/*
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */
namespace Mod\Dom\Finder;


/**
 * This DomLoader finder searches the module directory
 * for a {ClassName}.xml file to use for a template.
 *
 * For example if you called a module named Ext_Module_Page_List it would look for the
 * template at {sitePath}/lib/Ext/Module/Page/List.xml if found that would
 * then be set on the observable object using the setFile() method.
 *
 * @see \Mod\Dom\Loader
 * @package \Mod\Dom\Finder
 */
class Lib extends \Tk\Object implements Iface
{

    /**
     * Use this method to create and return the module class name
     *
     * @param \Mod\Dom\Loader $obs
     */
    public function update($obs)
    {
        if ($obs->hasFile() || !$obs->getClass()) {
            return;
        }

        // Check class is the only calss in teh file, other named classes do not have external Lib templates.
        $c = explode('\\', $obs->getClass());
        $classBase = end($c);
        $fileBase = str_replace('.php', '', basename(self::classpath($obs->getClass())));
        if ($classBase != $fileBase) {
            return;
        }

        $file =  str_replace('.php', '.xml', self::classpath($obs->getClass()));
        $file = str_replace(array('./', '../', '..'), '', $file);
        if (is_file($file)) {
            $obs->setFile($file);
        }

    }


}



