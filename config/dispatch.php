<?php

$dispatcher = $config->getDispatcherStatic();


// Admin Utility Pages
$dispatcher->add('/admin/_dev/index.html', '\Mod\Module\Dev\Index');
$dispatcher->add('/admin/_dev/pageList.html', '\Mod\Module\Dev\StaticPageList');
$dispatcher->add('/admin/_dev/tailLog.html', '\Mod\Module\Dev\TailLog');
$dispatcher->add('/admin/_dev/migrate.html', '\Mod\Module\Dev\Migrate');
//$dispatcher->add('/admin/_dev/pluginHooks.html', '\Mod\Module\Dev\PluginHookList');

$dispatcher->add('/admin/_dev/index.html', '\Mod\Module\Dev\Index');
$dispatcher->add('/admin/_dev/type.html', '\Mod\Module\Dev\Type');
$dispatcher->add('/admin/_dev/form.html', '\Mod\Module\Dev\Form');
$dispatcher->add('/admin/_dev/formTabs.html', '\Mod\Module\Dev\FormTabs');
$dispatcher->add('/admin/_dev/formStatic.html', '\Mod\Module\Dev\FormStatic');
$dispatcher->add('/admin/_dev/table.html', '\Mod\Module\Dev\Table');
$dispatcher->add('/admin/_dev/ui.html', '\Mod\Module\Dev\Ui');

// Mail log manager
$dispatcher->add('/admin/mail/log/manager.html', '\Mod\Module\MailLog\Manager');
$dispatcher->add('/admin/mail/log/view.html', '\Mod\Module\MailLog\View');