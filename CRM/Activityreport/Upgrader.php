<?php

/**
 * Collection of upgrade steps.
 */
class CRM_PivotReport_Upgrader extends CRM_PivotReport_Upgrader_Base {

  /**
   * Installation logic.
   * 
   * @return boolean
   */
  public function install() {
    $this->upgrade_0001();
    $this->upgrade_0002();

    return TRUE;
  }

  /**
   * Uninstallation logic.
   * 
   * @return boolean
   */
  public function uninstall()
  {
    CRM_Core_DAO::executeQuery("DELETE FROM `civicrm_navigation` WHERE name = 'pivotreport'");
    CRM_Core_BAO_Navigation::resetNavigation();

    return TRUE;
  }

  /**
   * Install Pivot Report link under Reports menu.
   * 
   * @return boolean
   */
  public function upgrade_0001() {
    CRM_Core_DAO::executeQuery("DELETE FROM `civicrm_navigation` WHERE name = 'pivotreport' and parent_id IS NULL");
    $reportsNavId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', 'Reports', 'id', 'name');
    $navigation = new CRM_Core_DAO_Navigation();
    $params = array (
        'domain_id'  => CRM_Core_Config::domainID(),
        'label'      => ts('Pivot Report'),
        'name'       => 'pivotreport',
        'url'        => 'civicrm/pivot-report',
        'parent_id'  => $reportsNavId,
        'weight'     => 0,
        'permission' => 'access CiviCRM pivot table reports',
        'separator'  => 1,
        'is_active'  => 1
    );
    $navigation->copyValues($params);
    $navigation->save();
    CRM_Core_BAO_Navigation::resetNavigation();

    return TRUE;
  }

  /**
   * Creates scheduled job to build pivot report cache, checking if it exists
   * first.
   */
  public function upgrade_0002() {
    $existsResult = civicrm_api3('Job', 'getcount', array(
      'sequential' => 1,
      'api_entity' => 'PivotReport',
      'api_action' => 'rebuildcache',
    ));

    if (intval($existsResult['result']) == 0) {
      civicrm_api3('Job', 'create', array(
        'run_frequency' => 'Daily',
        'name' => 'Pivot Report Cache Build',
        'description' => 'Job to rebuild the cache that is used to build pivot tble reports.',
        'api_entity' => 'PivotReport',
        'api_action' => 'rebuildcache',
      ));
    }

    return TRUE;
  }

  /**
   * Installs Pivot Report config db table (if does not exist).
   *
   * @return TRUE
   */
  public function upgrade_0003() {
    $this->executeSqlFile('sql/civicrm_pivotreport_config_install.sql');

    return TRUE;
  }

  /**
   * Logic which is executing when enabling extension.
   * 
   * @return boolean
   */
  public function onEnable() {
    CRM_Core_DAO::executeQuery("UPDATE civicrm_navigation SET is_active = 1 WHERE name = 'pivotreport'");
    CRM_Core_BAO_Navigation::resetNavigation();

    return TRUE;
  }

  /**
   * Logic which is executing when disabling extension.
   * 
   * @return boolean
   */
  public function onDisable() {
    CRM_Core_DAO::executeQuery("UPDATE civicrm_navigation SET is_active = 0 WHERE name = 'pivotreport'");
    CRM_Core_BAO_Navigation::resetNavigation();

    return TRUE;
  }
}
