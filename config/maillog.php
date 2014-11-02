<?php

// Attach callback function
\Tk\Mail\Gateway::getInstance()->addCallback(array('\Mod\Db\Log', 'gatewayCallback'));

