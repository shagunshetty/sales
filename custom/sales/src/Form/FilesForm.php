<?php
namespace Drupal\sales\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\file\Entity\File;
use Drupal\common_utilities\Utilities\commonUtil;

class FilesForm extends FormBase {
  var $path_file_tmp = 'public://attachment/tmp/files/';
  var $path_file_final = 'public://attachment/final/files/';

  public function getFormId() {
    return 'files_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $user = \Drupal::currentUser();
    $user_roles = $user->getRoles();
    $roles_permissions = user_role_permissions($user_roles);

    $arr_permission = array();
    foreach ($roles_permissions as $role_key => $permissions) {
      foreach ($permissions as $permission) {
        $arr_permission[] = $permission;
      }
    }
    $form = array(
      '#attributes' => array('enctype' => 'multipart/form-data'),
    );
    if (in_array('sales admin', $arr_permission))  {
      $form['file_upload'] = array(
        '#type' => 'fieldset',
        '#title' => $this
            ->t('File Upload'),
        '#attributes' => array(
            'class' => array('SectionContainer'), 
        )            
      );
      
      $form['file_upload']['document'] = array(
        '#type' => 'managed_file',
        '#title' => $this->t('Document'),
        '#multiple' => true,
        '#progress_message' => $this->t('Please wait...'),
        '#size' => 20,
        '#description' => t('Allowed file type : pdf xls xlsx doc docx'),
        '#upload_validators' => array('file_validate_extensions' => 'pdf xls xlsx doc docx'),
        '#upload_location' => $this->path_file_tmp,
      );
    }


    $final_realpath = \Drupal::service('file_system')->realpath($this->path_file_final);
    $arr_scanned_directory = array_diff(scandir($final_realpath), array('..', '.'));
    if (is_array($arr_scanned_directory) && count($arr_scanned_directory) > 0) {
      $file_url = file_create_url($this->path_file_final);

      foreach ($arr_scanned_directory as $filename) {
        if (file_exists($final_realpath."/".$filename)) {
          $val_file_name_with_time = $this->_getFileNameWithTime($filename);
          $arr_file_list[$filename] = Link::fromTextAndUrl($val_file_name_with_time, Url::fromUri($file_url."/".$filename,
            array('attributes' => array('target' => '_blank'))))->toString();
        }
      }
      $form['file_list'] = array(
        '#type' => 'fieldset',
        '#title' => $this
            ->t('File List'),
        '#attributes' => array(
            'class' => array('SectionContainer'), 
        )            
      );

      $form['file_list']['chk_document'] = array(
        '#type' => 'checkboxes',
        '#options' => $arr_file_list,
      );
      
      if (in_array('sales admin', $arr_permission))  {
        $form['file_list']['delete_document'] = array(
          '#type' => 'submit',
          '#value' => t('Delete file'),
          '#name' => 'btn_delete_document',
          '#attributes' => array('onclick' => 'if(!confirm("Do you really want to delete the selected file?")){return false;}'),
          '#submit' => array('::submitDeleteDocument'),
        );
      }
    }

    $form['file_upload']['actions'] = [
        '#type' => 'actions',
    ];
    
    if (in_array('sales admin', $arr_permission))  {
      $form['file_upload']['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Upload'),
      ];
    }
     // Adding js and css
    $form['#attached']['library'][] = 'sales/sales_lib';

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $arr_file = $this->_uploadFile($file_field, $form_state);
    if (count($arr_file) > 0) {
      \Drupal::messenger()->addStatus("File uploaded successfully!\n");
    } else {
      \Drupal::messenger()->addWarning("There was an error uploading the file.\n");
    }
  }



  private function _uploadFile($file_field, FormStateInterface $form_state) {
    $arr_result = array();
    $arr_fid = $form_state->getValue(['document']);

    if (is_array($arr_fid) && count($arr_fid)) {
      foreach($arr_fid as $fid) {
        $oNewFile = File::load($fid);
        $oNewFile->save();
        $og_file_uri = $oNewFile->getFileUri();

        # Get file name & extension.
        $path_parts = pathinfo($og_file_uri);
        $file_name = $path_parts['filename'];
        $file_extension = $path_parts['extension'];

        # Get tmp path
        $tmp_full_path = \Drupal::service('file_system')->realpath($og_file_uri);

        # Set final  path
        $final_full_path = \Drupal::service('file_system')->realpath($this->path_file_final);

        # Create directory if not exist.
        if (!is_dir($final_full_path)) {
          mkdir($final_full_path, 0777, true);
        }

        $final_file_name = commonUtil::clean_string($file_name)."_".time().".".$file_extension;

        $final_full_path = $final_full_path.'/'.$final_file_name;
        if (copy($tmp_full_path, $final_full_path)){
          $arr_result[] = $final_file_name;
        }
      }
    }
    return $arr_result;
  }

  private function _getFileNameWithTime($file_name) {
    $datetime = '';
    if (trim($file_name) != '') {
      $arr_file_name = explode("_", $file_name);
      $arr_file_name = explode(".", $arr_file_name[(count($arr_file_name)-1)]);
      $datetime = date('m/d/Y H:i:s', $arr_file_name[0]);
    }
    return $file_name . " - (".$datetime.")";
  }

  public function submitDeleteDocument(array &$form, FormStateInterface $form_state) {

    // Get the clicked form element to set the field name.
    $clickedElement = $form_state->getTriggeringElement()['#name'];
    $field_name = str_replace('btn_delete_', '', $clickedElement);

    $arr_chk_list = $form_state->getValue('chk_'.$field_name);

    foreach($arr_chk_list as $val_file_name) {
      if ($arr_chk_list[$val_file_name]) {
        $final_full_path = \Drupal::service('file_system')->realpath($this->path_file_final);
        $file_path = $final_full_path."/".$val_file_name;
        if ($this->_deleteFile($file_path)) {
          $str_success_message = "Successfully deleted ".$val_file_name ." ";
          \Drupal::messenger()->addStatus($str_success_message."\n");
        } else {
          $str_success_message = "Could not deleted ".$val_file_name ." ";
          \Drupal::messenger()->addWarning($str_success_message."\n");
        }       
      }
    }
  }

  private function _deleteFile($file_pointer) {
    if(file_exists($file_pointer)) {
      if (!unlink($file_pointer)) { 
        return false;
      } else { 
        return true;
      }
    } else {
      return false;
    }
  }
}
