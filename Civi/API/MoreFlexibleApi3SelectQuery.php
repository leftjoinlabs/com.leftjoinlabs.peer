<?php

namespace Civi\API;

/**
 * With this class we can create a Api3SelectQuery and then change the query
 * property after instantiating. This gives a bit more control over creating
 * queries, and we use it for the PeerCampaign.get API in order to select from
 * a custom temporary table instead of from the standard table
 *
 * @package Civi\API
 */
class MoreFlexibleApi3SelectQuery extends Api3SelectQuery {

  public function __construct(string $entity, bool $checkPermissions) {
    parent::__construct($entity, $checkPermissions);

    // Override entityFieldNames so that it picks up on the extra fields
    // we've added.
    $this->entityFieldNames = array_keys($this->apiFieldSpec);
  }

  /**
   * Set $this->query based on a table name
   *
   * @param string $tableName
   */
  public function setQueryFrom($tableName) {
    $this->query = \CRM_Utils_SQL_Select::from($tableName . ' ' . self::MAIN_TABLE_ALIAS);
  }

}
