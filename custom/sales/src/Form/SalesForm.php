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

class SalesForm extends FormBase {
  var $path_file_tmp = 'public://attachment/tmp/boq/';
  var $path_file_final = 'public://attachment/final/boq/';

  var $form_field = array(
    'date',
    'location',
    'deal_hotness',
    'company_name',
    'project_class',
    'project_type',
    'source_of_lead',
    'source_of_lead_comment',
    'project',
    'sales_person',
    'currency',
    'estimated_value',
    'inr_usd',
    'key_decision_maker',
    'engineer',
    'boq_person',
    'deal_stage',
    'result_status',
    'deal_status_updates',
    'likely_close_date'
  );

  public function getFormId() {
    return 'sales_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, 
    $node_id = 0) {

    $flag_enable_edit = false;
    $current_user = \Drupal::currentUser();
    $current_roles = $current_user->getRoles();
    $form_field = $this->form_field;
    
    $arr_engineer_list = commonUtil::getUsersByRole('sales_engineer');

    if ($node_id > 0) {
      // Edit Mode
      $node = Node::load($node_id);
      if ($node != NULL && $node->isPublished()) {
        $form['#title'] = 'Modify Sales';
        $flag_enable_edit = true;

        $form_data = array();
        foreach ($form_field as $field_name) {
          $form_data[$field_name] = $node->get('field_'.$field_name)->getString();
        }

        $arr_client_name = $node->get('field_client_name')->getValue();
        $arr_client_designation = $node->get('field_client_designation')->getValue();
        $arr_client_phone = $node->get('field_client_phone')->getValue();
        $arr_client_email = $node->get('field_client_email')->getValue();

        for($i=1; $i<=4; $i++) {
          $form_data['client_name_' . $i] = $arr_client_name[($i-1)]['value'];
          $form_data['client_designation_' . $i] = $arr_client_designation[($i-1)]['value'];
          $form_data['client_phone_' . $i] = $arr_client_phone[($i-1)]['value'];
          $form_data['client_email_' . $i] = $arr_client_email[($i-1)]['value'];
        }

        $form_data['boq_document'] = $node->get('field_boq_document')->getString();
        //  echo "<pre>";print_r($form_data);exit;
        // get created timestamp
        $created_timestamp = 'Created on ' . date("d F Y H:i", $node->getCreatedTime());
      } else {
          my_goto_error();
          return;
      }
    } else {
      $form['#title'] = 'Sales';

      // New Mode
      $form_data = array();
      foreach ($form_field as $field_name) {
        $form_data[$field_name] = '';
      }
      $form_data['boq_document'] = '';

      for($i=1; $i<=4; $i++) {
        $form_data['client_name_' . $i] = '';
        $form_data['client_designation_' . $i] = '';
        $form_data['client_phone_' . $i] = '';
        $form_data['client_email_' . $i] = '';
      }
    }

    $form = array(
      '#attributes' => array('enctype' => 'multipart/form-data'),
    );

    $form['sales_info'] = array(
      '#type' => 'fieldset',
      '#title' => $this
          ->t('Sales Information'),
      '#attributes' => array(
          'class' => array('SectionContainer'), 
      )            
    );

    $form['sales_info']['node_id'] = [
      '#type' => 'hidden',
      '#required' => TRUE,
      '#default_value' => $node_id,
    ];

    $form['sales_info']['date'] = [
      '#type' => 'date',
      '#title' => $this->t('Date of Enquiry'),
      '#date_date_format' => 'd-m-Y',
      '#default_value' => $form_data['date'],
    ];

    $arr_location = array(
      'mumbai' => 'Mumbai',
      'new_delhi' => 'New Delhi',
      'bangalore' => 'Bangalore',
      'hyderabad' => 'Hyderabad',
      'ahmedabad' => 'Ahmedabad',
      'chennai' => 'Chennai',
      'kolkata' => 'Kolkata',
      'surat' => 'Surat',
      'pune' => 'Pune',
      'jaipur' => 'Jaipur',
      'lucknow' => 'Lucknow',
      'kanpur' => 'Kanpur',
      'nagpur' => 'Nagpur',
      'indore' => 'Indore',
      'thane' => 'Thane',
      'bhopal' => 'Bhopal',
      'visakhapatnam' => 'Visakhapatnam',
      'pimpri_chinchwad' => 'Pimpri & Chinchwad',
      'patna' => 'Patna',
      'vadodara' => 'Vadodara',
      'ghaziabad' => 'Ghaziabad',
      'ludhiana' => 'Ludhiana',
      'agra' => 'Agra',
      'nashik' => 'Nashik',
      'faridabad' => 'Faridabad',
      'meerut' => 'Meerut',
      'rajkot' => 'Rajkot',
      'kalyan_dombivali' => 'Kalyan & Dombivali',
      'vasai_virar' => 'Vasai Virar',
      'varanasi' => 'Varanasi',
      'srinagar' => 'Srinagar',
      'aurangabad' => 'Aurangabad',
      'dhanbad' => 'Dhanbad',
      'amritsar' => 'Amritsar',
      'navi_mumbai' => 'Navi Mumbai',
      'allahabad' => 'Allahabad',
      'ranchi' => 'Ranchi',
      'haora' => 'Haora',
      'coimbatore' => 'Coimbatore',
      'jabalpur' => 'Jabalpur',
      'gwalior' => 'Gwalior',
      'vijayawada' => 'Vijayawada',
      'jodhpur' => 'Jodhpur',
      'madurai' => 'Madurai',
      'raipur' => 'Raipur',
      'kota' => 'Kota',
      'guwahati' => 'Guwahati',
      'chandigarh' => 'Chandigarh',
      'solapur' => 'Solapur',
      'hubli_and_dharwad' => 'Hubli and Dharwad',
      'bareilly' => 'Bareilly',
      'moradabad' => 'Moradabad',
      'gurgaon' => 'Gurgaon',
      'aligarh' => 'Aligarh',
      'jalandhar' => 'Jalandhar',
      'tiruchirappalli' => 'Tiruchirappalli',
      'bhubaneswar' => 'Bhubaneswar',
      'salem' => 'Salem',
      'mira_and_bhayander' => 'Mira and Bhayander',
      'thiruvananthapuram' => 'Thiruvananthapuram',
      'bhiwandi' => 'Bhiwandi',
      'saharanpur' => 'Saharanpur',
      'gorakhpur' => 'Gorakhpur',
      'guntur' => 'Guntur',
      'bikaner' => 'Bikaner',
      'amravati' => 'Amravati',
      'noida' => 'Noida',
      'jamshedpur' => 'Jamshedpur',
      'bhilai_nagar' => 'Bhilai Nagar',
      'warangal' => 'Warangal',
      'cuttack' => 'Cuttack',
      'firozabad' => 'Firozabad',
      'kochi' => 'Kochi',
      'bhavnagar' => 'Bhavnagar',
      'dehradun' => 'Dehradun',
      'durgapur' => 'Durgapur',
      'asansol' => 'Asansol',
      'nanded_waghala' => 'Nanded Waghala',
      'kolapur' => 'Kolapur',
      'ajmer' => 'Ajmer',
      'gulbarga' => 'Gulbarga',
      'jamnagar' => 'Jamnagar',
      'ujjain' => 'Ujjain',
      'loni' => 'Loni',
      'siliguri' => 'Siliguri',
      'jhansi' => 'Jhansi',
      'ulhasnagar' => 'Ulhasnagar',
      'nellore' => 'Nellore',
      'jammu' => 'Jammu',
      'sangli_miraj_kupwad' => 'Sangli Miraj Kupwad',
      'belgaum' => 'Belgaum',
      'mangalore' => 'Mangalore',
      'ambattur' => 'Ambattur',
      'tirunelveli' => 'Tirunelveli',
      'malegoan' => 'Malegoan',
      'gaya' => 'Gaya',
      'jalgaon' => 'Jalgaon',
      'udaipur' => 'Udaipur',
      'maheshtala' => 'Maheshtala',
      'others' => 'Others'
  );

    $form['sales_info']['location'] = [
      '#type' => 'select',
      '#options' => $arr_location, 
      '#title' => $this->t('Location'),
      '#default_value' => $form_data['location'],
    ];

    $arr_deal_hotness = array(
    'hot' => 'Hot',
    'warm' => 'Warm',
    'cold' => 'Cold'
    );

    $form['sales_info']['deal_hotness'] = [
      '#type' => 'select',
      '#options' => $arr_deal_hotness, 
      '#title' => $this->t('Deal Hotness'),
      '#default_value' => $form_data['deal_hotness'],
    ];

    $form['sales_info']['company_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Company Name'),
      '#required' => TRUE,        
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['company_name'],
    ];

    $arr_project_class = array(
    'a_class' => 'A Class (Services / Software / AMC)',
    'b_class' => 'B Class( Design Build)',
    'c_class' =>'C Class (Build Bid with alternates allowed)',
    'd_class' => 'D Class (Strict RFP)',
    'upgrade' => 'Upgrade',
    'upsales' => 'Upsales',
    );

    $form['sales_info']['project_class'] = [
      '#type' => 'select',
      '#options' => $arr_project_class, 
      '#title' => $this->t('Project Class'),
      '#default_value' => $form_data['project_class'],
    ];

    $arr_project_type = array(
    'auditorium' => 'Auditorium',
    'corporate_facility' => 'Corporate Facility',
    'command_center' => 'Command Center',
    'training_facility' => 'Training Facility',
    'event_space' => 'Event Space',
    'single_room' => 'Single Room',
    'multiple' => 'Multiple',
    'residential' => 'Residential',
    'professional_services' => 'Professional Services',
    'amc' => 'AMC',
    'government' => 'Government',
    'townhall_event_spaces' => 'Townhall / Event Spaces',
    'boardrooms_and_conference_rooms' => 'Boardrooms and Conference Rooms',
    'experience_center' => 'Experience Center',
    'others' => 'Others',
    );

    $form['sales_info']['project_type'] = [
      '#type' => 'select',
      '#options' => $arr_project_type, 
      '#title' => $this->t('Project Type'),
      '#default_value' => $form_data['project_type'],
    ];
    
    $arr_source_of_lead = array(
      'lead_from_allwave_team'  => 'Lead from allwave team',
      'website'  => 'Website',
      'reference'  => 'Reference',
      'cold_calling_self_generated'  => 'Cold Calling / Self Generated',
      'existing_client'  => 'Existing Client',
      'other'  => 'Other',
    );

    $form['sales_info']['source_of_lead'] = [
      '#type' => 'select',
      '#options' => $arr_source_of_lead, 
      '#title' => $this->t('Source of Lead'),
      '#default_value' => $form_data['source_of_lead'],
    ];
    
    $form['sales_info']['source_of_lead_comment'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Source of Lead Comment	'),
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['source_of_lead_comment'],
    ];

    $form['sales_info']['client_info'] = array(
      '#type' => 'fieldset',
      '#title' => $this
          ->t('Client Info'),
      '#attributes' => array(
          'class' => array('SectionContainer'), 
      )            
    );

    for ($i=1; $i<=4; $i++) {
      $form['sales_info']['client_info']['client_'.$i] = array(
        '#type' => 'fieldset',
        '#title' => $this
            ->t('Client ('.($i+1).')'),
        '#attributes' => array(
            'class' => array('SectionContainer'), 
        )            
      );

      $form['sales_info']['client_info']['client_'.$i]['client_name_'.$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Client Name'),
        '#attributes' => array(
            'autocomplete' => 'nope',
        ),
        '#default_value' => $form_data['client_name_'.$i],
      ];

      $form['sales_info']['client_info']['client_'.$i]['client_designation_'.$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Client Designation'),
        '#attributes' => array(
            'autocomplete' => 'nope',
        ),
        '#default_value' => $form_data['client_designation_'.$i],
      ];

      $form['sales_info']['client_info']['client_'.$i]['client_phone_'.$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Client Phone'),
        '#attributes' => array(
            'autocomplete' => 'nope',
        ),
        '#default_value' => $form_data['client_phone_'.$i],
      ];

      $form['sales_info']['client_info']['client_'.$i]['client_email_'.$i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Client Email'),
        '#attributes' => array(
            'autocomplete' => 'nope',
        ),
        '#default_value' => $form_data['client_email_'.$i],
      ];
    }
    
    $form['sales_info']['project'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Project'),
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['project'],
    ];      

    $form['sales_info']['sales_person'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Sales Person'),
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['sales_person'],
    ];

    $arr_currency = array(
    'inr' => 'INR',
    'usd' => 'USD'
    );

    $form['sales_info']['currency'] = [
      '#type' => 'select',
      '#options' => $arr_currency, 
      '#title' => $this->t('Currency'),
      '#default_value' => $form_data['currency'],
    ];

    $form['sales_info']['estimated_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Estimated Value (excl VAT)'),
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['estimated_value'],
    ];
    
    $form['sales_info']['inr_usd'] = [
      '#type' => 'textfield',
      '#title' => $this->t('INR+USD	'),
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['inr_usd'],
    ];

    $form['sales_info']['key_decision_maker'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Key decision makers'),
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['key_decision_maker'],
    ];

    $form['sales_info']['engineer'] = [
      '#type' => 'select',
      '#options' => $arr_engineer_list, 
      '#title' => $this->t('Design & Estimation Engineer'),
      '#default_value' => $form_data['engineer'],
    ];  

    $form['sales_info']['boq_person'] = [
      '#type' => 'textarea',
      '#title' => $this->t('BoQ Person'),
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['boq_person'],
    ];
    
    $form['sales_info']['boq_document'] = array(
      '#type' => 'managed_file',
      '#title' => $this->t('BOQ Document'),
      '#multiple' => true,
      '#progress_message' => $this->t('Please wait...'),
      '#size' => 20,
      '#description' => t('Allowed file type : pdf xls xlsx doc docx'),
      '#upload_validators' => array('file_validate_extensions' => 'pdf xls xlsx doc docx'),
      '#upload_location' => $this->path_file_tmp,
    );

    // List files
    $arr_delete_file_list = array();
    if ($form_data['boq_document'] != '') {
      $arr_file_list = unserialize($form_data['boq_document']);
      if (is_array($arr_file_list) && count($arr_file_list) > 0) {
        foreach ($arr_file_list as $val_file_name) {
          $file_url = file_create_url($this->path_file_final);
          //Check if file exist
          $final_realpath = \Drupal::service('file_system')->realpath($this->path_file_final);

          $final_realpath = $final_realpath . "/".$node_id."/".$key_file_upload.$val_file_name;

          if (file_exists($final_realpath)) {
            $file_url = $file_url."/".$node_id."/".$key_file_upload."/".$val_file_name;
            $val_file_name_with_time = $this->_getFileNameWithTime($val_file_name);
            $arr_delete_file_list[$val_file_name] = Link::fromTextAndUrl($val_file_name_with_time, Url::fromUri($file_url,
              array('attributes' => array('target' => '_blank'))))->toString();
          }
        }
        $form['sales_info']['chk_boq_document'] = array(
          '#title' => t('File List'),
          '#type' => 'checkboxes',
          '#options' => $arr_delete_file_list,
        );

        $form['sales_info']['delete_boq_document'] = array(
          '#type' => 'submit',
          '#value' => t('Delete file'),
          '#name' => 'btn_delete_boq_document',
          '#attributes' => array('onclick' => 'if(!confirm("Do you really want to delete the selected file?")){return false;}'),
          '#submit' => array('::submitDeleteFile'),
        );
      }
    }


    $arr_deal_stage = array(
    'suspect' => 'Suspect',
    'prospect' => 'Prospect',
    'qualified_lead' => 'Qualified Lead',
    'preliminary_design' => 'Preliminary Design',
    'proposal_sent' => 'Proposal Sent',
    'discussion_or_proposals' => 'Discussion or proposals',
    'negotiation' => 'Negotiation',
    'results_received' => 'Results Received'
    );

    $form['sales_info']['deal_stage'] = [
      '#type' => 'select',
      '#options' => $arr_deal_stage, 
      '#title' => $this->t('Deal Stage'),
      '#default_value' => $form_data['deal_stage'],
    ];

    $arr_result_status = array(
      'won' => 'Won',
      'lost' => 'Lost',
      'dropped' => 'Dropped',
      'hold' => 'Hold',
    );

    $form['sales_info']['result_status'] = [
      '#type' => 'select',
      '#options' => $arr_result_status, 
      '#title' => $this->t('Result Status'),
      '#default_value' => $form_data['result_status'],
    ];

    $form['sales_info']['deal_status_updates'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Deal Status Updates'),
      '#attributes' => array(
          'autocomplete' => 'nope',
      ),
      '#default_value' => $form_data['deal_status_updates'],
    ];
    
    $form['sales_info']['likely_close_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Likely Close Date'),
      '#date_date_format' => 'd-m-Y',
      '#default_value' => $form_data['likely_close_date'],
    ];

    $form['actions'] = [
        '#type' => 'actions',
    ];
    
    $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
    ];
    

     // Adding js and css
    $form['#attached']['library'][] = 'sales/sales_lib';

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file_field = 'boq_document';
    if ($form_state->getValue('node_id') > 0) {
      // Update Record
      $node_id = $form_state->getValue('node_id');
      $node = Node::load($node_id);
      if ($node == null) {
        commonUtil::my_goto_error();
        return;
      }

      $form_data = $this->_getFormData($form_state);
     // echo "<pre>";
//print_r($form_data);exit;
      foreach ($form_data as $field_name => $field_value) {
        $node->set($field_name, $field_value);
      }

      // Save files
      $arr_file_list = $this->_uploadFile($file_field, $node_id, $form_state);
      if (is_array($arr_file_list) && count($arr_file_list) > 0) {
        // Load the existing files
        $str_stored_file_list = trim($node->get('field_'.$file_field)->getString());
        if ($str_stored_file_list != '') {
          $arr_stored_file_list = unserialize($str_stored_file_list);
          // Append to existing list.
          $arr_file_list = array_merge($arr_file_list, $arr_stored_file_list);
        }

        $node->set('field_'.$file_field, serialize($arr_file_list));
      }

      $node->save();
      \Drupal::messenger()->addStatus("Record has been updated!\n");

    } else {
      // New Record
      $form_data = $this->_getFormData($form_state);
      $form_data['title'] = 'Sales - ' . time();
  
      //print_r($form_data);exit;
      $node = Node::create($form_data);
  
      $node->save();
      $node_id = preg_replace("/[^0-9]/", "", $node->id());

      // Save files
      $arr_file_list = $this->_uploadFile($file_field, $node_id, $form_state);
      if (is_array($arr_file_list) && count($arr_file_list) > 0) {
        $node->set('field_'.$file_field, serialize($arr_file_list));
      }

      \Drupal::messenger()->addStatus("Record has been created!\n");
          commonUtil::my_goto(Url::fromRoute('sales.manage',
                              array('node_id' => $node_id))->toString());
    }
  }

  private function _getFormData(FormStateInterface $form_state) {
    $form_field = $this->form_field;
    foreach ($form_field as $field_name) {
      $form_data['field_'.$field_name] = $form_state->getValue($field_name);
    }

    $arr_client_name = array();
    $arr_client_designation = array();
    $arr_client_phone = array();
    $arr_client_email = array();

    for ($i=1; $i<=4; $i++) {
      array_push($arr_client_name, $form_state->getValue('client_name_'.$i));
      array_push($arr_client_designation, $form_state->getValue('client_designation_'.$i));
      array_push($arr_client_phone, $form_state->getValue('client_phone_'.$i));
      array_push($arr_client_email, $form_state->getValue('client_email_'.$i));
    }
    $form_data['field_client_name'] = $arr_client_name;
    $form_data['field_client_designation'] = $arr_client_designation;
    $form_data['field_client_phone'] = $arr_client_phone;
    $form_data['field_client_email'] = $arr_client_email;

    $form_data['type'] = 'sales';
    return $form_data;
  }

  private function _uploadFile($file_field, $node_id, FormStateInterface $form_state) {
    $arr_result = array();
    $arr_fid = $form_state->getValue([$file_field]);
    echo "IN".$file_field;
    print_r($arr_fid);

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
        $final_realpath = \Drupal::service('file_system')->realpath($this->path_file_final);
        $final_full_path = $final_realpath ."/".$node_id;

        # Create directory if not exist.
        if (!is_dir($final_full_path)) {
          var_dump(mkdir($final_full_path, 0777, true));
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

  public function submitDeleteFile(array &$form, FormStateInterface $form_state) {
    $form_data_file = $this->form_data_file;

    // Get the clicked form element to set the field name.
    $clickedElement = $form_state->getTriggeringElement()['#name'];
    $field_name = str_replace('btn_delete_', '', $clickedElement);

    if ($form_state->getValue('node_id') > 0) {
      $node_id = $form_state->getValue('node_id');
      $node = Node::load($node_id);
      $node_file_data = array();
      $arr_chk_list = array();
      $str_success_message = '';
      $flag_message_error = false;
      if ($node != NULL) {
        $node_file_data[$field_name] = unserialize($node->get('field_'.$field_name)->getString());
        $flag_file_deleted = false;
        if(is_array($node_file_data[$field_name]) && count($node_file_data[$field_name])) {
          $arr_chk_list = $form_state->getValue('chk_'.$field_name);
          foreach($node_file_data[$field_name] as $val_file_name) {
            if ($arr_chk_list[$val_file_name]) {

              if (($key = array_search($val_file_name, $node_file_data[$field_name])) !== false) {

                  $final_realpath = \Drupal::service('file_system')->realpath($this->path_file_final);
                  $final_full_path = $final_realpath ."/".$node_id;

                  if ($this->_deleteFile($final_full_path."/".$val_file_name)) {
                    unset($node_file_data[$field_name][$key]);
                    $flag_file_deleted = true;
                    $str_success_message.= "Successfully deleted ".$val_file_name ."  ";
                  } else {
                    $str_success_message.= "Could not deleted ".$val_file_name ."  ";
                    $flag_message_error = true;
                  }
              }                
            }
          }
          // Update only when a file is deleted.
          if ($flag_file_deleted == true) {
            $node->set('field_'.$field_name, serialize($node_file_data[$field_name]));
          }
        }

        $node->save();
        if ($flag_message_error == true) {
          \Drupal::messenger()->addWarning($str_success_message);
        } else {
          \Drupal::messenger()->addStatus($str_success_message);
        }
      } else {
        \Drupal::messenger()->addWarning("Sorry, error processing the request!\n");
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
