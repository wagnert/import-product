<?php

/**
 * TechDivision\Import\Product\Actions\Processors\StockItemUpdateProcessor
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Product\Actions\Processors;

use TechDivision\Import\Utils\EntityStatus;
use TechDivision\Import\Product\Utils\MemberNames;
use TechDivision\Import\Product\Utils\SqlStatementKeys;
use TechDivision\Import\Actions\Processors\AbstractCreateProcessor;

/**
 * The stock item update processor implementation.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-product
 * @link      http://www.techdivision.com
 */
class StockItemUpdateProcessor extends AbstractCreateProcessor
{

    /**
     * Implements the CRUD functionality the processor is responsible for,
     * can be one of CREATE, READ, UPDATE or DELETE a entity.
     *
     * @param array       $row  The data to handle
     * @param string|null $name The name of the prepared statement to execute
     *
     * @return void
     */
    public function execute($row, $name = null)
    {

        // load the field names
        $keys = array_keys($row);

        // create a unique name for the prepared statement
        $name = sprintf('%s-%s', $name, md5(implode('-', $keys)));

        // query whether or not the statement has been prepared
        if (!$this->hasPreparedStatement($name)) {
            // initialize the array for the primary key fields
            $pks = array();
            // load the last value as PK from the array with the keys
            $pks[] = $keys[array_search(MemberNames::ITEM_ID, $keys, true)];

            // remove the entity status and the primary key from the keys
            unset($keys[array_search(MemberNames::ITEM_ID, $keys, true)]);
            unset($keys[array_search(EntityStatus::MEMBER_NAME, $keys, true)]);

            // prepare the SET part of the SQL statement
            array_walk($keys, function (&$value, $key) {
                $value = sprintf('%s=:%s', $value, $value);
            });

            // prepare the SET part of the SQL statement
            array_walk($pks, function (&$value, $key) {
                $value = sprintf('%s=:%s', $value, $value);
            });

            // create the prepared UPDATE statement
            $statement = sprintf($this->loadStatement(SqlStatementKeys::UPDATE_STOCK_ITEM), implode(',', $keys), implode(',', $pks));

            // prepare the statement
            $this->addPreparedStatement($name, $this->getConnection()->prepare($statement));
        }

        // pass the call to the parent method
        return parent::execute($row, $name);
    }
}
