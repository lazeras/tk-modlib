<?php
/*       -- TkLib Auto Class Builder --
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2005 Tropotek Development
 */
namespace Mod\Db;

/**
 *
 *
 * @package Mail\Db
 */
class MailLogMap extends \Tk\Db\Mapper
{

    /**
     * Create the data map
     *
     * @return \Tk\Model\DataMap
     */
    protected function makeDataMap()
    {
        $dataMap = new \Tk\Model\DataMap(__CLASS__);
        $this->setTable('mailLog');
        $this->setMarkDeleted('del');
        
        $dataMap->addIdProperty(\Tk\Model\Map\Integer::create('id'));

        $dataMap->addProperty(\Tk\Model\Map\Boolean::create('sent'));
        $dataMap->addProperty(\Tk\Model\Map\String::create('to'));
        $dataMap->addProperty(\Tk\Model\Map\String::create('from'));
        $dataMap->addProperty(\Tk\Model\Map\String::create('cc'));
        $dataMap->addProperty(\Tk\Model\Map\String::create('bcc'));
        $dataMap->addProperty(\Tk\Model\Map\String::create('subject'));
        $dataMap->addProperty(\Tk\Model\Map\String::create('body'));
        $dataMap->addProperty(\Tk\Model\Map\String::create('notes'));
        $dataMap->addProperty(\Tk\Model\Map\Date::create('modified'));
        $dataMap->addProperty(\Tk\Model\Map\Date::create('created'));
        
        return $dataMap;
    }

    // ------- Add custom methods below. -------
    


    /**
     * Find filtered records
     *
     * @param array $filter
     * @param \Tk\Db\Tool $tool
     * @return \Tk\Db\Array
     */
    public function findFiltered($filter = array(), $tool = null)
    {
        $where = '';
        if (!empty($filter['keywords'])) {
            $kw = '%' . $this->getDb()->escapeString($filter['keywords']) . '%';
            $w = '';
            $w .= sprintf('`body` LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('`subject` LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('`to` LIKE %s OR ', $this->getDb()->quote($kw));
            $w .= sprintf('`from` LIKE %s OR ', $this->getDb()->quote($kw));
            if (is_numeric($filter['keywords'])) {
                $id = (int)$filter['keywords'];
                $w .= sprintf('`id` = %d OR ', $id);
            }
            if ($w) {
                $where .= '(' . substr($w, 0, -3) . ') AND ';
            }
        }

        if (!empty($filter['dateFrom'])) {
            $dte = $filter['dateFrom'];
            $where .= sprintf('`created` >= %s AND ', $this->getDb()->quote($dte->floor()->toString()) );
        }

        if (!empty($filter['dateTo'])) {
            $dte = $filter['dateTo'];
            $where .= sprintf('`created` <= %s AND ', $this->getDb()->quote($dte->ceil()->toString()) );
        }
        
        //if (!empty($filter['someId'])) {
        //    $where .= sprintf('`someId` = %s AND ', (int)$filter['someId']);
        //}
        
        if ($where) {
            $where = substr($where, 0, -4);
        }
        return $this->selectMany($where, $tool);
    }
    
}