<?php
namespace Drupal\sales\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Utility\Unicode;
use Drupal\node\Entity\Node;
use Drupal\common_utilities\Utilities\commonUtil;
use Drupal\views\Views;

class SalesController extends ControllerBase {
  public static function getGraphData($type) {
    $arr_current_imp_project = array('1-AVPMS/Prerequisites', '2-AV Cabling', '3-Equipments Installation', '4-Testing Commissioning and Handover');
    $arr_complete_project = array('5-Completed', '6-DLP Phase', '7-AMC', '8-Non AMC');

    $arr_type = array();
    if ($type == "completed") {
      $arr_type = $arr_complete_project;
    } else {
      $arr_type = $arr_current_imp_project;
    }

    $arr_project_stage = commonUtil::get_term_list('project_stage', 'color');
    $computedData = "[['Count', 'Projects', { role: 'style' }, { role: 'link' } ],";

    foreach ($arr_project_stage as $project_stage_id => $project_stage) {
      if (in_array($project_stage['name'],$arr_type)) {
        $query = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'projects')
        ->condition('field_project_stage', $project_stage_id);

        $count = $query->count()->execute();

        $computedData.= "['".$project_stage['name']."',".$count.",'".$project_stage['color']."',
        '".commonUtil::getProjectListByStage($project_stage_id)."'],";
      }
    }
    $computedData.= "]";
    return $computedData;
  }

  public static function searchProjects($arg_data, $item_per_page = 5) {
    $view = Views::getView('project_list');
    $view->setDisplay('default');
    $view->get_total_rows = TRUE;

    foreach ($arg_data['query'] as $key_query => $val_query) {
      if ($val_query == '0') {
        $arg_data['query'][$key_query] = '';
      }
    }

//echo "<pre>";print_r($arg_data['query']);
//exit;

    $view->setExposedInput($arg_data['query']);
    $view->preExecute();
    //$view->setOffset(1);
    $view->setItemsPerPage($item_per_page);
    $view->execute();
    $rows = $view->total_rows;

    $search_result = array();
    foreach ($view->result as $rid => $row) {
      foreach ($view->field as $fid => $field ) {
          $search_result[$rid][$fid] = $field->getValue($row);
      }
    }
    $arr_pager = $view->pager->render(array());
    $pager = drupal_render($arr_pager);

    return array('search_result' => $search_result, 'search_pager' => $pager);
  }  

}